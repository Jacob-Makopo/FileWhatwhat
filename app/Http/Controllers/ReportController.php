<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Uploads;
use App\Models\Company;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view reports');

        return Inertia::render('Reports/Index', [
            'stats' => $this->getReportStats($request),
            'filters' => $request->only(['date_from', 'date_to', 'municipality_id', 'status']),
        ]);
    }

    public function submissions(Request $request)
    {
        $this->authorize('view reports');

        $query = Uploads::with(['company', 'municipality'])
            ->when($request->date_from, function ($q, $date) {
                $q->whereDate('submitted_at', '>=', $date);
            })
            ->when($request->date_to, function ($q, $date) {
                $q->whereDate('submitted_at', '<=', $date);
            })
            ->when($request->municipality_id, function ($q, $municipalityId) {
                $q->where('municipality_id', $municipalityId);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest('submitted_at');

        return Inertia::render('Reports/Submissions', [
            'submissions' => $query->paginate(20)->withQueryString(),
            'municipalities' => Municipality::all(),
            'filters' => $request->only(['date_from', 'date_to', 'municipality_id', 'status']),
        ]);
    }

    public function audit(Request $request)
    {
        $this->authorize('view reports');

        // This would typically show system reports, not to be confused with audit logs
        return Inertia::render('Reports/Audit', [
            'systemStats' => $this->getSystemStats($request),
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    private function getReportStats(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        return [
            'totalSubmissions' => Uploads::whereBetween('submitted_at', [$dateFrom, $dateTo])->count(),
            'pendingSubmissions' => Uploads::whereBetween('submitted_at', [$dateFrom, $dateTo])->where('status', 'pending')->count(),
            'completedSubmissions' => Uploads::whereBetween('submitted_at', [$dateFrom, $dateTo])->where('status', 'completed')->count(),
            'activeCompanies' => Company::where('status', 'active')->count(),
            'totalMunicipalities' => Municipality::count(),
        ];
    }

    private function getSystemStats(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        return [
            'totalUsers' => \App\Models\User::count(),
            'activeUsers' => \App\Models\User::where('last_login_at', '>=', now()->subMonth())->count(),
            'storageUsage' => $this->calculateStorageUsage(),
            'systemUptime' => $this->getSystemUptime(),
        ];
    }

    private function calculateStorageUsage()
    {
        // Simplified storage calculation
        $size = 0;
        $path = storage_path('app');
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return round($size / 1024 / 1024, 2); // MB
    }

    private function getSystemUptime()
    {
        // Simplified uptime calculation
        return '99.9%';
    }
}
