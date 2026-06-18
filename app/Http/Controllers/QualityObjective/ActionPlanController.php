<?php

namespace App\Http\Controllers\QualityObjective;

use App\Http\Controllers\Controller;
use App\Http\Requests\QualityObjective\StoreActionPlanRequest;
use App\Http\Requests\QualityObjective\UpdateActionPlanRequest;
use App\Models\QualityObjective;
use App\Models\QualityObjectiveActionPlan;
use App\Models\User;
use App\Services\QualityObjective\ObjectiveAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActionPlanController extends Controller
{
    protected ObjectiveAuditService $auditService;

    public function __construct(ObjectiveAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Show form to create Action Plan.
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

        $users = User::orderBy('name')->get();

        return view('quality-objectives.action-plans.create', compact('objective', 'users'));
    }

    /**
     * Store Action Plan.
     */
    public function store(StoreActionPlanRequest $request)
    {
        $data = $request->validated();
        $objective = QualityObjective::with('period')->findOrFail($data['objective_id']);

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $user = auth()->user();
        $data['created_by'] = $user->id;

        // Auto-assign sequence if empty
        if (empty($data['sequence'])) {
            $lastSeq = QualityObjectiveActionPlan::where('objective_id', $objective->id)->max('sequence');
            $data['sequence'] = ($lastSeq ?? 0) + 1;
        }

        $actionPlan = QualityObjectiveActionPlan::create($data);

        // Audit Log
        $this->auditService->log('qo_action_plan_created', $user->id, $objective->id, $request->ip(), [
            'action_plan_id' => $actionPlan->id,
            'program_name' => $actionPlan->program_name,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Program Kerja '{$actionPlan->program_name}' berhasil ditambahkan.");
    }

    /**
     * Show form to edit Action Plan.
     */
    public function edit($id)
    {
        $actionPlan = QualityObjectiveActionPlan::with('objective.period')->findOrFail($id);
        $objective = $actionPlan->objective;

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            return redirect()
                ->route('quality-objectives.objectives.show', $objective->id)
                ->with('error', 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $users = User::orderBy('name')->get();

        return view('quality-objectives.action-plans.edit', compact('actionPlan', 'objective', 'users'));
    }

    /**
     * Update Action Plan.
     */
    public function update(UpdateActionPlanRequest $request, $id)
    {
        $actionPlan = QualityObjectiveActionPlan::with('objective.period')->findOrFail($id);
        $objective = $actionPlan->objective;

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $data = $request->validated();
        $user = auth()->user();
        $data['updated_by'] = $user->id;

        if ($data['status'] === 'completed' && $actionPlan->status !== 'completed') {
            $data['completed_at'] = now();
            if (empty($data['actual_date'])) {
                $data['actual_date'] = now()->toDateString();
            }
        }

        $actionPlan->update($data);

        // Audit Log
        $this->auditService->log('qo_action_plan_updated', $user->id, $objective->id, $request->ip(), [
            'action_plan_id' => $actionPlan->id,
            'program_name' => $actionPlan->program_name,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', 'Program kerja berhasil diperbarui.');
    }

    /**
     * Delete Action Plan.
     */
    public function destroy(Request $request, $id)
    {
        $actionPlan = QualityObjectiveActionPlan::with('objective.period')->findOrFail($id);
        $objective = $actionPlan->objective;

        // Period locking check
        if ($objective->period->status === 'closed' || $objective->period->status === 'archived') {
            abort(403, 'Aksi ditolak. Periode tahun sasaran mutu ini telah ditutup.');
        }

        $user = auth()->user();
        $programName = $actionPlan->program_name;

        $actionPlan->delete();

        // Audit Log
        $this->auditService->log('qo_action_plan_deleted', $user->id, $objective->id, $request->ip(), [
            'program_name' => $programName,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Program kerja '{$programName}' berhasil dihapus.");
    }
}
