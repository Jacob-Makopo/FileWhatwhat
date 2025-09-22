<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view municipalities');

        $query = Municipality::withCount(['companies', 'deadlines'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%");
            })
            ->orderBy('name');

        return Inertia::render('Municipalities/Index', [
            'municipalities' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }
    
    public function store(Request $request)
    {
        $this->authorize('create municipality');

        $request->validate([
            'name' => 'required|string|max:255|unique:municipalities,name',
            'province' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:municipalities,code'
        ]);

        Municipality::create($request->all());

        return redirect()->back()->with('success', 'Municipality created successfully.');
    }

    public function update(Request $request, Municipality $municipality)
    {
        $this->authorize('edit municipality');

        $request->validate([
            'name' => 'required|string|max:255|unique:municipalities,name,' . $municipality->id,
            'province' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:municipalities,code,' . $municipality->id,
        ]);

        $municipality->update($request->all());

        return redirect()->back()->with('success', 'Municipality updated successfully.');
    }

    public function destroy(Municipality $municipality)
    {
        $this->authorize('delete municipality');

        // Check if municipality has any companies or deadlines
        if ($municipality->companies()->exists() || $municipality->deadlines()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete municipality with associated companies or deadlines.');
        }

        $municipality->delete();

        return redirect()->back()->with('success', 'Municipality deleted successfully.');
    }
}