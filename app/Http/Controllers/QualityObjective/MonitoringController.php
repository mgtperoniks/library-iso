<?php

namespace App\Http\Controllers\QualityObjective;

use App\Http\Controllers\Controller;
use App\Http\Requests\QualityObjective\StoreMonitoringRequest;
use App\Http\Requests\QualityObjective\UpdateMonitoringRequest;
use App\Models\QualityObjective;
use App\Models\QualityObjectiveMonitoring;
use App\Models\QualityObjectiveEvidence;
use App\Services\QualityObjective\ObjectiveAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    protected ObjectiveAuditService $auditService;

    public function __construct(ObjectiveAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Show form to add monitoring.
     */
    public function create(Request $request)
    {
        $objectiveId = $request->query('objective_id');
        $objective = QualityObjective::with('period')->findOrFail($objectiveId);

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            return redirect()
                ->route('quality-objectives.objectives.show', $objective->id)
                ->with('error', 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        return view('quality-objectives.monitorings.create', compact('objective'));
    }

    /**
     * Store monitoring record.
     */
    public function store(StoreMonitoringRequest $request)
    {
        $data = $request->validated();
        $objective = QualityObjective::with('period')->findOrFail($data['objective_id']);

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $user = auth()->user();
        $data['input_by'] = $user->id;
        $data['input_at'] = now();
        $data['is_locked'] = false;

        // Auto calculate achievement_pct
        $data['achievement_pct'] = $this->calculateAchievementPct(
            $objective->target_polarity,
            $data['target_snapshot'],
            $data['realization_value']
        );

        $monitoring = DB::transaction(function () use ($data, $request, $user) {
            $mon = QualityObjectiveMonitoring::create($data);

            // Handle Evidence Upload
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/evidences'), $filename);
                $filePath = 'uploads/evidences/' . $filename;

                QualityObjectiveEvidence::create([
                    'reference_type' => QualityObjectiveMonitoring::class,
                    'reference_id' => $mon->id,
                    'file_path' => $filePath,
                    'notes' => $request->input('evidence_notes', 'Bukti Unggahan Monitoring'),
                    'created_by' => $user->id,
                ]);
            }

            return $mon;
        });

        // Audit Log
        $this->auditService->log('qo_monitoring_created', $user->id, $objective->id, $request->ip(), [
            'monitoring_id' => $monitoring->id,
            'period_label' => $monitoring->period_label,
            'achievement_pct' => $monitoring->achievement_pct,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Data pemantauan periode '{$monitoring->period_label}' berhasil disimpan.");
    }

    /**
     * Show form to edit monitoring.
     */
    public function edit($id)
    {
        $monitoring = QualityObjectiveMonitoring::with(['objective.period', 'evidences'])->findOrFail($id);
        $objective = $monitoring->objective;

        // Lock checks
        if ($monitoring->is_locked) {
            return redirect()
                ->route('quality-objectives.objectives.show', $objective->id)
                ->with('error', 'Aksi ditolak. Data pemantauan periode ini telah dikunci.');
        }

        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            return redirect()
                ->route('quality-objectives.objectives.show', $objective->id)
                ->with('error', 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        return view('quality-objectives.monitorings.edit', compact('monitoring', 'objective'));
    }

    /**
     * Update monitoring record.
     */
    public function update(UpdateMonitoringRequest $request, $id)
    {
        $monitoring = QualityObjectiveMonitoring::with('objective.period')->findOrFail($id);
        $objective = $monitoring->objective;

        // Lock checks
        if ($monitoring->is_locked) {
            abort(403, 'Aksi ditolak. Data pemantauan periode ini telah dikunci.');
        }

        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $data = $request->validated();
        $user = auth()->user();

        // Auto calculate achievement_pct
        $data['achievement_pct'] = $this->calculateAchievementPct(
            $objective->target_polarity,
            $data['target_snapshot'],
            $data['realization_value']
        );

        DB::transaction(function () use ($monitoring, $data, $request, $user) {
            $monitoring->update($data);

            // Handle Evidence Upload
            if ($request->hasFile('evidence_file')) {
                // Delete old evidence physical file if exists
                $oldEvidence = QualityObjectiveEvidence::where('reference_type', QualityObjectiveMonitoring::class)
                    ->where('reference_id', $monitoring->id)
                    ->first();

                if ($oldEvidence && $oldEvidence->file_path && file_exists(public_path($oldEvidence->file_path))) {
                    @unlink(public_path($oldEvidence->file_path));
                    $oldEvidence->delete();
                }

                $file = $request->file('evidence_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/evidences'), $filename);
                $filePath = 'uploads/evidences/' . $filename;

                QualityObjectiveEvidence::create([
                    'reference_type' => QualityObjectiveMonitoring::class,
                    'reference_id' => $monitoring->id,
                    'file_path' => $filePath,
                    'notes' => $request->input('evidence_notes', 'Bukti Unggahan Monitoring'),
                    'created_by' => $user->id,
                ]);
            }
        });

        // Audit Log
        $this->auditService->log('qo_monitoring_updated', $user->id, $objective->id, $request->ip(), [
            'monitoring_id' => $monitoring->id,
            'period_label' => $monitoring->period_label,
            'achievement_pct' => $monitoring->achievement_pct,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', 'Data pemantauan berhasil diperbarui.');
    }

    /**
     * Lock the monitoring record (No more edits allowed).
     */
    public function lock(Request $request, $id)
    {
        $monitoring = QualityObjectiveMonitoring::with('objective.period')->findOrFail($id);
        $objective = $monitoring->objective;
        $user = auth()->user();

        // Check authority: Only Admin or MR can lock
        if (!$user->hasAnyRole(['admin', 'mr'])) {
            abort(403, 'Hanya Admin atau Management Representative (MR) yang dapat mengunci data pemantauan.');
        }

        $monitoring->update([
            'is_locked' => true,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // Audit Log
        $this->auditService->log('qo_monitoring_locked', $user->id, $objective->id, $request->ip(), [
            'monitoring_id' => $monitoring->id,
            'period_label' => $monitoring->period_label,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Data pemantauan periode '{$monitoring->period_label}' berhasil dikunci.");
    }

    /**
     * Delete monitoring record.
     */
    public function destroy(Request $request, $id)
    {
        $monitoring = QualityObjectiveMonitoring::with(['objective.period', 'evidences'])->findOrFail($id);
        $objective = $monitoring->objective;

        if ($monitoring->is_locked) {
            abort(403, 'Aksi ditolak. Data pemantauan periode ini telah dikunci.');
        }

        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $user = auth()->user();
        $periodLabel = $monitoring->period_label;

        DB::transaction(function () use ($monitoring) {
            foreach ($monitoring->evidences as $evidence) {
                if ($evidence->file_path && file_exists(public_path($evidence->file_path))) {
                    @unlink(public_path($evidence->file_path));
                }
                $evidence->delete();
            }
            $monitoring->delete();
        });

        // Audit Log
        $this->auditService->log('qo_monitoring_deleted', $user->id, $objective->id, $request->ip(), [
            'period_label' => $periodLabel,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Data pemantauan periode '{$periodLabel}' berhasil dihapus.");
    }

    /**
     * Calculate achievement percentage automatically.
     */
    protected function calculateAchievementPct(string $polarity, float $target, ?float $realization): float
    {
        if ($realization === null) {
            return 0.0;
        }

        if ($polarity === 'gte') {
            if ($target <= 0) return 100.0;
            return round(($realization / $target) * 100, 2);
        } else {
            // LTE polarity
            if ($realization <= 0) {
                return 100.0; // Achieved 100% since complaints/errors are 0
            }
            return round(($target / $realization) * 100, 2);
        }
    }
}
