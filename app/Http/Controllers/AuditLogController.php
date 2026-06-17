<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\AuditLog::with(['user', 'document', 'version'])
            ->orderBy('created_at', 'desc');

        // Filter: Event
        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        // Filter: User (Email or Name)
        if ($request->filled('user')) {
            $searchUser = $request->input('user');
            $query->whereHas('user', function ($q) use ($searchUser) {
                $q->where('email', 'like', "%{$searchUser}%")
                  ->orWhere('name', 'like', "%{$searchUser}%");
            });
        }

        // Filter: Document (Doc Code)
        if ($request->filled('document')) {
            $searchDoc = $request->input('document');
            $query->whereHas('document', function ($q) use ($searchDoc) {
                $q->where('doc_code', 'like', "%{$searchDoc}%");
            });
        }

        // Filter: Date Range (Start Date)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        // Filter: Date Range (End Date)
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Export CSV via query param ?export=csv
        if ($request->filled('export') && $request->input('export') === 'csv') {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="audit_log.csv"',
            ];

            $callback = function () use ($query) {
                $handle = fopen('php://output', 'w');

                // Header kolom
                fputcsv($handle, ['id', 'event', 'user', 'document', 'version', 'detail', 'ip', 'created_at']);

                $query->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $r) {
                        fputcsv($handle, [
                            $r->id,
                            $r->event,
                            $r->user->email ?? $r->user->name ?? '',
                            $r->document->doc_code ?? '',
                            $r->version->version_label ?? '',
                            is_string($r->detail) ? $r->detail : json_encode($r->detail),
                            $r->ip,
                            $r->created_at,
                        ]);
                    }
                });

                fclose($handle);
            };

            return new StreamedResponse($callback, 200, $headers);
        }

        // List view (paginate)
        $events = $query->paginate(50)->withQueryString();

        return view('audit.index', compact('events'));
    }
}
