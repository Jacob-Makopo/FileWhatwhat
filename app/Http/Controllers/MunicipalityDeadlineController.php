<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\MunicipalityDeadline;
use App\Models\UserAssignment;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MunicipalityDeadlineController extends Controller
{
    /**
     * Display deadlines and assignments for municipalities.
     */
    public function index(Request $request)
    {
        $municipalityId = $request->get('municipality_id');
        $date = $request->get('date', now()->format('Y-m-d'));

        // Fetch all municipalities with their companies
        $municipalities = Municipality::with('companies')->orderBy('name')->get();

        $selectedMunicipality = $municipalityId
            ? Municipality::with('companies')->find($municipalityId)
            : null;

        $startOfMonth = Carbon::parse($date)->startOfMonth();
        $endOfMonth = Carbon::parse($date)->endOfMonth();

        // Deadlines for current month
        $deadlines = MunicipalityDeadline::with('municipality')
            ->whereBetween('deadline_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('deadline_date')
            ->map(fn($group) => $group->first());

        // Assignments for current month
        $assignments = UserAssignment::with(['user', 'municipality', 'company'])
            ->whereBetween('deadline_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(fn($assignment) => [
                'id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user->name,
                'municipality_id' => $assignment->municipality_id,
                'municipality_name' => $assignment->municipality->name,
                'company_id' => $assignment->company_id,
                'company_name' => $assignment->company->name,
                'deadline_date' => $assignment->deadline_date,
                'notes' => $assignment->notes,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
            ]);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Updated path for Vue component in Deadlines subfolder
        return Inertia::render('Deadlines/Municipalities', [
            'municipalities' => $municipalities,
            'selectedMunicipality' => $selectedMunicipality,
            'selectedDate' => $date,
            'deadlines' => $deadlines,
            'assignments' => $assignments,
            'users' => $users,
        ]);
    }

    /**
     * Store a new deadline.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'municipality_id' => 'required|exists:municipalities,id',
            'deadline_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        // Prevent duplicates
        $existingDeadline = MunicipalityDeadline::where('municipality_id', $validated['municipality_id'])
            ->where('deadline_date', $validated['deadline_date'])
            ->first();

        if ($existingDeadline) {
            return redirect()->back()->withErrors([
                'deadline_date' => 'A deadline already exists for this municipality on the selected date.'
            ]);
        }

        MunicipalityDeadline::create($validated);

        return redirect()->back()->with('success', 'Deadline created successfully!');
    }

    /**
     * Update an existing deadline.
     */
    public function update(Request $request, MunicipalityDeadline $deadline)
    {
        $validated = $request->validate([
            'deadline_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        // Prevent duplicates for other records
        $existingDeadline = MunicipalityDeadline::where('municipality_id', $deadline->municipality_id)
            ->where('deadline_date', $validated['deadline_date'])
            ->where('id', '!=', $deadline->id)
            ->first();

        if ($existingDeadline) {
            return redirect()->back()->withErrors([
                'deadline_date' => 'A deadline already exists for this municipality on the selected date.'
            ]);
        }

        $deadline->update($validated);

        return redirect()->back()->with('success', 'Deadline updated successfully!');
    }

    /**
     * Delete a deadline and related assignments.
     */
    public function destroy(MunicipalityDeadline $deadline)
    {
        UserAssignment::where('municipality_id', $deadline->municipality_id)
            ->where('deadline_date', $deadline->deadline_date)
            ->delete();

        $deadline->delete();

        return redirect()->back()->with('success', 'Deadline deleted successfully!');
    }

    /**
     * Store multiple assignments at once.
     */
    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.municipality_id' => 'required|exists:municipalities,id',
            'assignments.*.company_id' => 'required|exists:companies,id',
            'assignments.*.deadline_date' => 'required|date|after_or_equal:today',
            'assignments.*.notes' => 'nullable|string|max:500',
        ]);

        $createdAssignments = [];

        DB::transaction(function () use ($validated, &$createdAssignments) {
            foreach ($validated['assignments'] as $data) {
                $existing = UserAssignment::where('municipality_id', $data['municipality_id'])
                    ->where('company_id', $data['company_id'])
                    ->where('deadline_date', $data['deadline_date'])
                    ->first();

                if ($existing) continue;

                $assignment = UserAssignment::create($data);
                $createdAssignments[] = $assignment;
            }
        });

        if (count($createdAssignments)) {
            return redirect()->back()->with('success', 'Assignments created successfully!');
        }

        return redirect()->back()->with('error', 'No new assignments were created. Some assignments may already exist.');
    }

    /**
     * Delete a single assignment.
     */
    public function destroyAssignment(UserAssignment $assignment)
    {
        $assignment->delete();

        return redirect()->back()->with('success', 'Assignment deleted successfully!');
    }

    /**
     * Create deadline with assignments in a single transaction.
     */
    public function createWithAssignments(Request $request)
    {
        $validated = $request->validate([
            'municipality_id' => 'required|exists:municipalities,id',
            'deadline_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'assigned_user_id' => 'required|exists:users,id',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Create or update deadline
                $deadline = MunicipalityDeadline::updateOrCreate(
                    [
                        'municipality_id' => $validated['municipality_id'],
                        'deadline_date' => $validated['deadline_date'],
                    ],
                    ['notes' => $validated['notes']]
                );

                // Create or update assignments
                foreach ($validated['company_ids'] as $companyId) {
                    UserAssignment::updateOrCreate(
                        [
                            'municipality_id' => $validated['municipality_id'],
                            'company_id' => $companyId,
                            'deadline_date' => $validated['deadline_date'],
                        ],
                        [
                            'user_id' => $validated['assigned_user_id'],
                            'notes' => $validated['notes'],
                        ]
                    );
                }
            });

            return redirect()->back()->with('success', 'Deadline and assignments created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create deadline and assignments: ' . $e->getMessage());
        }
    }
}
