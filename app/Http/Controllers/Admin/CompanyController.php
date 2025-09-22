<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Company;
use App\Models\Municipality;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view companies');

        $query = Company::with('municipality')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%")
                    ->orWhereHas('municipality', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->municipality_id, function ($q, $municipalityId) {
                $q->where('municipality_id', $municipalityId);
            })
            ->latest();

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $query->paginate(20)->withQueryString(),
            'municipalities' => Municipality::all(),
            'filters' => $request->only(['search', 'status', 'municipality_id']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage companies');

        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'registration_number' => 'nullable|string|max:100|unique:companies,registration_number',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
            'municipality_id' => 'required|exists:municipalities,id',
        ]);

        Company::create($request->all());

        return redirect()->back()->with('success', 'Company created successfully.');
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('manage companies');

        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'registration_number' => 'nullable|string|max:100|unique:companies,registration_number,' . $company->id,
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
            'municipality_id' => 'required|exists:municipalities,id',
        ]);

        $company->update($request->all());

        return redirect()->back()->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('manage companies');

        if ($company->submissions()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete company with existing submissions.');
        }

        $company->delete();

        return redirect()->back()->with('success', 'Company deleted successfully.');
    }
}
