<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\{Upload, Company, Municipality};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('view dashboard');

        $stats = [
            'activeCompanies'  => Company::where('status', 'active')->count(),
            'municipalities'   => Municipality::count(),
            //'totalSubmissions' => Uploads::count(),
            //'totalValue'       => (float) Upload::sum('amount'),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
