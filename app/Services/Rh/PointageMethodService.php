<?php

namespace App\Services\Rh;

use App\Models\Employee;
use App\Models\Pointage;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Service gérant les 3 modes de pointage électronique :
 *   1. QR Code   : l'employé scanne un QR personnel et unique
 *   2. Intranet  : l'employé connecté à /pointer-now depuis le réseau entreprise (IP whitelistée)
 *   3. Télétravail : l'employé hors réseau entreprise → pointage à valider par son N+1
 *
 * Gère également la génération sécurisée du QR (token aléatoire 64 chars).
 */
class PointageMethodService
{
    /**
     * Détermine le mode (intranet vs teletravail) à partir de l'IP du visiteur.
     */
    public function detecterMode(string $ip): string
    {
        if ($this->isIntranetIp($ip)) {
            return 'intranet';
        }
        return 'teletravail';
    }

    /**
     * Vérifie si l'IP correspond à un réseau officiel de l'entreprise.
     */
    public function isIntranetIp(string $ip): bool
    {
        // Tolérance locale en dev
        if (config('pointage.tolerate_local', true)) {
            if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
                return true;
            }
            if ($this->ipInCidr($ip, '10.0.0.0/8'))    return true;
            if ($this->ipInCidr($ip, '172.16.0.0/12')) return true;
            if ($this->ipInCidr($ip, '192.168.0.0/16')) return true;
        }

        foreach ((array) config('pointage.intranet_ips', []) as $allowed) {
            if (empty($allowed)) continue;
            if (str_contains($allowed, '/')) {
                if ($this->ipInCidr($ip, $allowed)) return true;
            } elseif ($ip === $allowed) {
                return true;
            }
        }
        return false;
    }

    /**
     * Test d'appartenance IP à un CIDR (IPv4 only — suffisant pour LAN entreprise).
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/') || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        [$subnet, $bits] = explode('/', $cidr, 2);
        if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask       = -1 << (32 - (int) $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    /**
     * Vérifie qu'on est dans la plage horaire autorisée (si configurée).
     */
    public function horaireAutorise(?Carbon $when = null): bool
    {
        $min = config('pointage.horaire_min');
        $max = config('pointage.horaire_max');
        if (!$min && !$max) return true;
        $now = ($when ?? now())->format('H:i');
        if ($min && $now < $min) return false;
        if ($max && $now > $max) return false;
        return true;
    }

    /**
     * Enregistre ou met à jour le pointage du jour selon le mode détecté.
     * Renvoie le Pointage créé/modifié.
     */
    public function pointer(Employee $employee, string $ip, ?string $userAgent, string $mode, ?\App\Models\User $causedBy = null): Pointage
    {
        $aujourd_hui = now()->toDateString();
        $maintenant  = now()->format('H:i:s');

        $existant = Pointage::where('employee_id', $employee->id)
            ->where('date', $aujourd_hui)
            ->first();

        $estTeletravail = $mode === 'teletravail';
        // Statut : intranet / QR personnel / QR générique validés immédiatement,
        // teletravail en brouillon avec validation N+1 requise.
        $statut = in_array($mode, ['intranet', 'qr_code', 'qr_generique'], true) ? 1 : 0;

        if ($existant) {
            // Si déjà entré, on remplit la sortie et on calcule h_normales
            if (empty($existant->heure_sortie)) {
                $heureSortie = $maintenant;
                $duree = $this->dureeEntreSorte($existant->heure_entree, $heureSortie);
                $existant->fill([
                    'heure_sortie' => $heureSortie,
                    'h_normales'   => min($duree, 8),
                    'h_sup'        => max(0, $duree - 8),
                    'mode_pointage' => $existant->mode_pointage ?: $mode,
                    'ip_address'    => $ip,
                    'user_agent'    => $userAgent ? substr($userAgent, 0, 255) : null,
                ]);
                if ($estTeletravail) {
                    $existant->requires_validation_n1 = true;
                    $existant->statut = 0;
                }
                $existant->save();
            }
            return $existant;
        }

        // Création
        $pointage = Pointage::create([
            'employee_id'  => $employee->id,
            'date'         => $aujourd_hui,
            'heure_entree' => $maintenant,
            'h_normales'   => 0, // calculé à la sortie
            'h_sup'        => 0,
            'mode_pointage' => $mode,
            'ip_address'   => $ip,
            'user_agent'   => $userAgent ? substr($userAgent, 0, 255) : null,
            'statut'       => $statut,
            'requires_validation_n1' => $estTeletravail,
            'saisi_par'    => $causedBy?->id ?: $employee->user_id,
        ]);

        return $pointage;
    }

    /**
     * Renvoie la durée en heures décimales entre 2 timestamps H:i:s.
     */
    private function dureeEntreSorte(?string $entree, ?string $sortie): float
    {
        if (!$entree || !$sortie) return 0;
        $start = Carbon::parse($entree);
        $end   = Carbon::parse($sortie);
        if ($end->lessThan($start)) return 0;
        return round($end->diffInMinutes($start) / 60, 2);
    }

    /**
     * Génère un token unique sécurisé pour le QR code de l'employé.
     */
    public function genererToken(Employee $employee, bool $regenerer = false): string
    {
        if (!$regenerer && $employee->pointage_token) {
            return $employee->pointage_token;
        }
        do {
            $token = bin2hex(random_bytes(32)); // 64 chars hex
        } while (Employee::where('pointage_token', $token)->where('id', '!=', $employee->id)->exists());
        $employee->forceFill(['pointage_token' => $token])->save();
        return $token;
    }

    /**
     * Construit l'URL absolue de scan du QR.
     */
    public function urlQr(Employee $employee): string
    {
        $token = $this->genererToken($employee);
        return route('pointage.qr.scan', ['token' => $token]);
    }

    /**
     * Authentifie un employé par matricule + PIN (utilisé par le QR générique).
     * Lève ValidationException en cas d'échec.
     * Anti-bruteforce : limite N tentatives par (matricule, ip) sur une fenêtre glissante.
     */
    public function authenticatePin(string $matricule, string $pin, string $ip): Employee
    {
        $key = 'pointage-pin:' . sha1(strtolower($matricule) . ':' . $ip);
        $max = (int) config('pointage.pin_max_tentatives', 5);
        $fenetre = (int) config('pointage.pin_fenetre_secondes', 600);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'pin' => "Trop de tentatives. Réessayez dans $seconds secondes.",
            ]);
        }

        $employee = Employee::where('matricule', $matricule)->where('statut', 1)->first();

        if (!$employee || !$employee->pointage_pin || !Hash::check($pin, $employee->pointage_pin)) {
            RateLimiter::hit($key, $fenetre);
            throw ValidationException::withMessages([
                'pin' => 'Matricule ou PIN incorrect.',
            ]);
        }

        RateLimiter::clear($key);
        return $employee;
    }

    /**
     * Le token du QR générique partagé est stocké en env (config/pointage).
     */
    public function tokenQrGenerique(): ?string
    {
        return config('pointage.qr_generique_token');
    }

    public function urlQrGenerique(): string
    {
        $token = $this->tokenQrGenerique();
        if (!$token) {
            // Pas de token configuré → URL d'admin pour le générer
            return route('rh.pointages.qr-generique.admin');
        }
        return route('pointage.qr-generique.show', ['token' => $token]);
    }

    public function genererQrGeneriquePng(): string
    {
        $url = $this->urlQrGenerique();
        $builder = new Builder(
            writer: new PngWriter(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 12,
        );
        return $builder->build(data: $url)->getString();
    }

    /**
     * Génère le PNG du QR code (binaire).
     */
    public function genererQrPng(Employee $employee): string
    {
        $url = $this->urlQr($employee);
        $builder = new Builder(
            writer: new PngWriter(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 360,
            margin: 10,
        );
        $result = $builder->build(data: $url);
        return $result->getString();
    }
}
