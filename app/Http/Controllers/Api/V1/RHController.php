<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\Formation;
use App\Models\Paie;
use App\Models\Recrutement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RHController extends Controller
{
    public function employees(Request $request): JsonResponse
    {
        $query = Employee::with('user', 'organisation');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenoms', 'like', "%{$request->search}%")
                  ->orWhere('matricule', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $employees = $query->orderBy('nom')->paginate($request->input('per_page', 15));

        return response()->json($employees);
    }

    public function showEmployee(Employee $employee): JsonResponse
    {
        $employee->load('user', 'organisation', 'absences', 'competences', 'qualifications');

        return response()->json($employee);
    }

    public function absences(Request $request): JsonResponse
    {
        $query = Absence::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('type')) {
            $query->where('type_absence', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $absences = $query->orderByDesc('date_debut')->paginate($request->input('per_page', 15));

        return response()->json($absences);
    }

    public function paie(Request $request): JsonResponse
    {
        $query = Paie::with('employee', 'exercice');

        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }

        if ($request->filled('mois')) {
            $query->where('mois', $request->mois);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $paies = $query->orderByDesc('created_at')->paginate($request->input('per_page', 15));

        return response()->json($paies);
    }

    public function showPaie(Paie $paie): JsonResponse
    {
        $paie->load('employee', 'exercice');

        return response()->json($paie);
    }

    public function formations(Request $request): JsonResponse
    {
        $query = Formation::query();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $query->where('intitule', 'like', "%{$request->search}%");
        }

        $formations = $query->orderByDesc('date_debut')->paginate($request->input('per_page', 15));

        return response()->json($formations);
    }

    public function recrutements(Request $request): JsonResponse
    {
        $query = Recrutement::query();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $recrutements = $query->orderByDesc('date_publication')->paginate($request->input('per_page', 15));

        return response()->json($recrutements);
    }
}
