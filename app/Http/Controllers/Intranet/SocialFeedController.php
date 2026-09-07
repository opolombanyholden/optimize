<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use App\Models\Employee;
use App\Models\Intranet\Media;
use App\Models\Intranet\Publication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SocialFeedController extends Controller
{
    /**
     * Rend le contenu du drawer réseau social (partial HTML injecté via fetch).
     */
    public function drawer(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $in7 = Carbon::today()->addDays(7)->endOfDay();

        // ── Anniversaires (7 prochains jours) ────────────────────────────
        $anniversaires = Employee::with('user')
            ->where('statut', 1)
            ->whereNotNull('date_naissance')
            ->get()
            ->filter(function ($e) use ($today, $in7) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->between($today, $in7);
            })
            ->sortBy(function ($e) use ($today) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->timestamp;
            })
            ->take(10)
            ->values();

        $wishesData = [];
        $wishesDejaEnvoyes = collect();
        if ($anniversaires->isNotEmpty()) {
            $dates = $anniversaires->map(function ($e) use ($today) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->toDateString();
            })->unique();
            $wishesData = BirthdayWish::whereIn('employee_id', $anniversaires->pluck('id'))
                ->whereIn('date_anniversaire', $dates)
                ->selectRaw('employee_id, count(*) as total')
                ->groupBy('employee_id')
                ->pluck('total', 'employee_id')
                ->toArray();
            if ($user) {
                $wishesDejaEnvoyes = BirthdayWish::where('expediteur_id', $user->id)
                    ->whereIn('employee_id', $anniversaires->pluck('id'))
                    ->whereIn('date_anniversaire', $dates)
                    ->pluck('employee_id');
            }
        }

        // ── Publications publiques récentes (10) ─────────────────────────
        $publications = Publication::with(['publishable', 'auteur'])
            ->where('visibilite', 'public')
            ->whereNotNull('publie_le')
            ->orderByDesc('publie_le')
            ->take(10)
            ->get()
            ->filter(fn ($p) => $p->publishable !== null)
            ->values();

        // ── Médias récents (12) ──────────────────────────────────────────
        $medias = Media::whereIn('type', ['image', 'video'])->latest()->take(12)->get();

        return view('intranet._partials.social-drawer-content', compact(
            'anniversaires', 'wishesData', 'wishesDejaEnvoyes',
            'publications', 'medias',
        ));
    }
}
