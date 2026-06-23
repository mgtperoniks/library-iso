<?php

namespace App\Http\Controllers;

use App\Models\DocumentDistributionLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DistributionRegisterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin', 'mr', 'director'])) {
            abort(403, 'Unauthorized. Only Admin, MR, or Director can access the Distribution Register.');
        }

        $query = DocumentDistributionLog::orderBy('created_at', 'desc');

        // Apply Date range filters
        if ($request->filled('start_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $query->where('created_at', '>=', $startDate);
            } catch (\Throwable) {}
        }
        if ($request->filled('end_date')) {
            try {
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $query->where('created_at', '<=', $endDate);
            } catch (\Throwable) {}
        }

        // Apply Document filter (doc_code or title)
        if ($request->filled('document')) {
            $docTerm = $request->input('document');
            $query->where(function ($q) use ($docTerm) {
                $q->where('doc_code', 'like', "%{$docTerm}%")
                  ->orWhere('document_title', 'like', "%{$docTerm}%");
            });
        }

        // Apply User filter (name or email)
        if ($request->filled('user')) {
            $userTerm = $request->input('user');
            $query->where(function ($q) use ($userTerm) {
                $q->where('user_name', 'like', "%{$userTerm}%")
                  ->orWhere('user_email', 'like', "%{$userTerm}%");
            });
        }

        // Apply Activity/Action filter
        if ($request->filled('action')) {
            $action = $request->input('action');
            $query->where('action', $action);
        }

        // Export to CSV if requested
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('distribution.index', compact('logs'));
    }

    /**
     * Export matching logs to CSV
     */
    protected function exportCsv($query)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="document_distribution_register_' . now()->format('Ymd_His') . '.csv"',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
            'Pragma'              => 'public',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM to prevent excel character encoding issues
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers
            fputcsv($file, [
                'Trace ID',
                'Waktu',
                'Aktivitas',
                'Kode Dokumen',
                'Judul Dokumen',
                'Versi',
                'User Email',
                'Nama User',
                'Role User',
                'Departemen',
                'IP Address'
            ]);

            // Chunk database retrieval to optimize memory
            $query->chunk(500, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    // Translate action
                    $activity = match ($log->action) {
                        'preview_pdf'      => 'Membuka PDF',
                        'download_pdf'     => 'Mengunduh PDF',
                        'download_master'  => 'Mengunduh File Master',
                        default            => $log->action,
                    };

                    fputcsv($file, [
                        $log->trace_id,
                        $log->created_at->format('d-M-Y H:i:s'),
                        $activity,
                        $log->doc_code ?? '-',
                        $log->document_title ?? '-',
                        $log->version_label ?? '-',
                        $log->user_email,
                        $log->user_name,
                        $log->user_role ?? '-',
                        $log->user_department ?? '-',
                        $log->ip_address ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
