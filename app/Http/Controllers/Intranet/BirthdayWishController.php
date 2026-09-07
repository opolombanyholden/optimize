<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BirthdayWishController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        // Anniversaire cible = date du prochain anniversaire (année courante ou suivante)
        if (!$employee->date_naissance) {
            return back()->with('error', "Cet employé n'a pas de date d'anniversaire renseignée.");
        }
        $today = Carbon::today();
        $bday = $employee->date_naissance->copy()->year($today->year);
        if ($bday->lt($today)) $bday->addYear();

        BirthdayWish::updateOrCreate(
            [
                'employee_id'       => $employee->id,
                'expediteur_id'     => $request->user()->id,
                'date_anniversaire' => $bday,
            ],
            ['message' => $data['message'] ?: 'Joyeux anniversaire ! 🎂'],
        );

        return back()->with('success', 'Vœux envoyés à ' . trim($employee->prenoms.' '.$employee->noms) . '.');
    }
}
