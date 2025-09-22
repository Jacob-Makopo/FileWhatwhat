<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view audit logs');

        $query = AuditLog::with(['user'])
            ->when($request->search, function ($q, $search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->when($request->event, function ($q, $event) {
                $q->where('event', $event);
            })
            ->when($request->date_from, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->latest();

        return Inertia::render('Admin/AuditLogs/Index', [
            'auditLogs' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'event', 'date_from', 'date_to']),
            'eventTypes' => AuditLog::distinct()->pluck('event')->values(),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view audit logs');

        $auditLog->load(['user', 'auditable']);

        return Inertia::render('Admin/AuditLogs/Show', [
            'auditLog' => $auditLog,
        ]);
    }
}
