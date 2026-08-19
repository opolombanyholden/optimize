<?php

use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Intranet\ContactOrganisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migration de données : absorbe `clients` et `fournisseurs` dans
 * `intranet_contact_organisations` typé.
 *
 * Idempotent : détecte via (type, code) si l'organisation existe déjà.
 * Les tables sources ne sont PAS supprimées ici (rollback possible).
 */
return new class extends Migration {
    public function up(): void
    {
        $userId = DB::table('users')->where('email', 'admin@optimize.local')->value('id')
               ?? DB::table('users')->min('id')
               ?? 1;

        // ─── 1. Clients → Organisation type='client' ───
        $migCli = 0;
        foreach (Client::all() as $c) {
            if (ContactOrganisation::where('type', 'client')->where('code', $c->code)->exists()) continue;

            ContactOrganisation::create([
                'type'            => 'client',
                'code'            => $c->code,
                'raison_sociale'  => $c->raison_sociale,
                'nom'             => $c->raison_sociale ?: $c->code ?: 'Client sans nom',
                'forme_juridique' => $c->forme_juridique,
                'nif'             => $c->nif,
                'rccm'            => $c->rccm,
                'email'           => $c->email,
                'telephone'       => $c->telephone,
                'site_web'        => $c->site_web,
                'adresse'         => $c->adresse,
                'ville'           => $c->ville,
                'contact_principal_nom'       => $c->contact_nom,
                'contact_principal_telephone' => $c->contact_telephone,
                'contact_principal_email'     => $c->contact_email,
                'rib'             => $c->rib,
                'banque'          => $c->banque,
                'notes'           => $c->notes,
                'statut'          => $c->statut ?? 1,
                'extra_attributes'=> $c->extra_attributes,
                'created_by'      => $c->created_by ?? $userId,
                'slug'            => static::slugUnique(Str::slug(($c->raison_sociale ?: 'cli') . '-' . $c->code)),
            ]);
            $migCli++;
        }

        // ─── 2. Fournisseurs → Organisation type='fournisseur' ───
        $migFrn = 0;
        foreach (Fournisseur::all() as $f) {
            $code = 'FRN-' . str_pad((string) $f->id, 4, '0', STR_PAD_LEFT);
            if (ContactOrganisation::where('type', 'fournisseur')->where('code', $code)->exists()) continue;

            $rs = $f->raison_sociale;
            if (empty($rs)) continue;

            ContactOrganisation::create([
                'type'            => 'fournisseur',
                'code'            => $code,
                'raison_sociale'  => $rs,
                'nom'             => $rs,
                'nif'             => $f->nif,
                'rccm'            => $f->rccm,
                'email'           => $f->email,
                'telephone'       => $f->telephone,
                'site_web'        => $f->site_web,
                'adresse'         => $f->adresse,
                'ville'           => $f->ville,
                'contact_principal_nom'       => $f->contact_nom,
                'contact_principal_telephone' => $f->contact_telephone,
                'contact_principal_email'     => $f->contact_email,
                'rib'             => $f->rib,
                'banque'          => $f->banque,
                'notes'           => $f->commentaire,
                'statut'          => $f->statut ?? 1,
                'extra_attributes'=> $f->extra_attributes,
                'created_by'      => $userId,
                'slug'            => static::slugUnique(Str::slug($rs . '-' . $code)),
            ]);
            $migFrn++;
        }

        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            echo "  Fusion : $migCli client(s) + $migFrn fournisseur(s) migré(s) vers intranet_contact_organisations.\n";
        }
    }

    public function down(): void
    {
        ContactOrganisation::whereIn('type', ['client', 'fournisseur'])
            ->where(function ($q) {
                $q->where('code', 'like', 'CLI-%')->orWhere('code', 'like', 'FRN-%');
            })
            ->forceDelete();
    }

    private static function slugUnique(string $base): string
    {
        $slug = $base ?: 'organisation-' . uniqid();
        $original = $slug;
        $i = 2;
        while (ContactOrganisation::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
};
