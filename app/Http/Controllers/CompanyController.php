<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view companies');

        $query = Company::with(['municipality:id,name'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->municipality_id, function ($q, $municipalityId) {
                $q->where('municipality_id', $municipalityId);
            })
            ->orderBy('name');

        return Inertia::render('Companies/Index', [
            'companies' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'municipality_id']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create company');

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
        $this->authorize('edit company');

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
        $this->authorize('delete company');

        // Check if company has any uploads
        if ($company->uploads()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete company with existing uploads.');
        }

        $company->delete();

        return redirect()->back()->with('success', 'Company deleted successfully.');
    }
}