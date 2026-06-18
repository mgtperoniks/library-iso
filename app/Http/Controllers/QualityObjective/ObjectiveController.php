<?php

namespace App\Http\Controllers\QualityObjective;

use App\Http\Controllers\Controller;
use App\Http\Requests\QualityObjective\StoreQualityObjectiveRequest;
use App\Http\Requests\QualityObjective\UpdateQualityObjectiveRequest;
use App\Models\Department;
use App\Models\QualityObjective;
use App\Models\QualityObjectivePeriod;
use App\Models\QualityObjectiveApproval;
use App\Services\QualityObjective\ObjectiveCodeService;
use App\Services\QualityObjective\ObjectiveAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ObjectiveController extends Controller
{
    protected ObjectiveCodeService $codeService;
    protected ObjectiveAuditService $auditService;

    public function __construct(ObjectiveCodeService $codeService, ObjectiveAuditService $auditService)
    {
        $this->codeService = $codeService;
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of the objectives.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $periods = QualityObjectivePeriod::orderByDesc('year')->get();
        $departments = Department::orderBy('code')->get();

        $query = QualityObjective::with(['period', 'department', 'pic']);

        // Filter: Department
        if ($user->hasAnyRole(['kabag'])) {
            $query->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Filter: Period
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->input('period_id'));
        } else {
            // Default to latest active period if exists
            $activePeriod = QualityObjectivePeriod::where('status', 'active')->first();
            if ($activePeriod) {
                $query->where('period_id', $activePeriod->id);
            }
        }

        // Calculate KPIs before pagination
        $allFilteredObjectives = $query->get();

        $totalObjectives = $allFilteredObjectives->count();
        $countExcellent = 0;
        $countOnTrack = 0;
        $countAtRisk = 0;
        $countOffTrack = 0;
        $countNotReported = 0;
        $totalCompliancePct = 0;

        foreach ($allFilteredObjectives as $obj) {
            $status = $obj->current_achievement_status;
            if ($status === 'excellent') $countExcellent++;
            elseif ($status === 'on_track') $countOnTrack++;
            elseif ($status === 'at_risk') $countAtRisk++;
            elseif ($status === 'off_track') $countOffTrack++;
            else $countNotReported++;

            $totalCompliancePct += (float) $obj->reporting_compliance_pct;
        }

        $avgCompliance = $totalObjectives > 0 ? round($totalCompliancePct / $totalObjectives, 1) : 100.0;

        $objectives = $query->orderBy('sort_order')->paginate(20)->appends($request->query());

        return view('quality-objectives.objectives.index', compact(
            'objectives', 'periods', 'departments',
            'totalObjectives', 'countExcellent', 'countOnTrack', 'countAtRisk', 'countOffTrack', 'countNotReported', 'avgCompliance'
        ));
    }

    /**
     * Show the form for creating a new objective.
     */
    public function create()
    {
        $user = auth()->user();
        if (Gate::denies('create', QualityObjective::class)) {
            abort(403, 'Unauthorized action.');
        }

        $periods = QualityObjectivePeriod::whereIn('status', ['draft', 'active'])->orderByDesc('year')->get();
        
        if ($user->hasAnyRole(['kabag'])) {
            $departments = Department::where('id', $user->department_id)->get();
        } else {
            $departments = Department::orderBy('code')->get();
        }

        // Fetch all users for PIC selection
        $users = \App\Models\User::orderBy('name')->get();

        return view('quality-objectives.objectives.create', compact('periods', 'departments', 'users'));
    }

    /**
     * Store a newly created objective in storage.
     */
    public function store(StoreQualityObjectiveRequest $request)
    {
        if (Gate::denies('create', QualityObjective::class)) {
            abort(403);
        }

        $data = $request->validated();
        $user = auth()->user();

        // Enforce department for KABAG
        if ($user->hasAnyRole(['kabag'])) {
            $data['department_id'] = $user->department_id;
        }

        // Get Period Year to generate code
        $period = QualityObjectivePeriod::findOrFail($data['period_id']);
        
        // Generate Unique Code
        $data['code'] = $this->codeService->generateCode($data['department_id'], $period->year);
        $data['created_by'] = $user->id;
        $data['status'] = 'draft';

        $objective = QualityObjective::create($data);

        // Audit Log
        $this->auditService->log('qo_objective_created', $user->id, $objective->id, $request->ip(), [
            'code' => $objective->code,
            'process_name' => $objective->process_name,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', "Sasaran Mutu {$objective->code} berhasil dibuat.");
    }

    /**
     * Display the specified objective.
     */
    public function show($id)
    {
        $objective = QualityObjective::with([
            'period', 'department', 'pic', 'creator', 
            'renewals', 'renewalOf', 'approvals.user'
        ])->findOrFail($id);

        if (Gate::denies('view', $objective)) {
            abort(403, 'Anda tidak diizinkan melihat data ini.');
        }

        // Sprint 2 Stub
        $actionPlans = $objective->actionPlans()->orderBy('sequence')->get();
        
        // Sprint 3 Stub
        $monitorings = $objective->monitorings()
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->orderBy('period_quarter')
            ->get();
            
        // Sprint 4 Stub
        $evaluation = $objective->evaluation;

        // Fetch periods for renewal options
        $periods = QualityObjectivePeriod::orderByDesc('year')->get();

        return view('quality-objectives.objectives.show', compact('objective', 'actionPlans', 'monitorings', 'evaluation', 'periods'));
    }

    /**
     * Show the form for editing the specified objective.
     */
    public function edit($id)
    {
        $objective = QualityObjective::findOrFail($id);
        
        if (Gate::denies('update', $objective)) {
            abort(403, 'Anda tidak diizinkan mengubah data ini.');
        }

        $periods = QualityObjectivePeriod::orderByDesc('year')->get();
        $departments = Department::orderBy('code')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('quality-objectives.objectives.edit', compact('objective', 'periods', 'departments', 'users'));
    }

    /**
     * Update the specified objective in storage.
     */
    public function update(UpdateQualityObjectiveRequest $request, $id)
    {
        $objective = QualityObjective::findOrFail($id);
        
        if (Gate::denies('update', $objective)) {
            abort(403);
        }

        $data = $request->validated();
        $user = auth()->user();

        // Enforce department for KABAG
        if ($user->hasAnyRole(['kabag'])) {
            $data['department_id'] = $user->department_id;
        }

        // Regenerate code if period or department changed
        if ($objective->period_id != $data['period_id'] || $objective->department_id != $data['department_id']) {
            $period = QualityObjectivePeriod::findOrFail($data['period_id']);
            $data['code'] = $this->codeService->generateCode($data['department_id'], $period->year);
        }

        $objective->update($data);

        // Audit Log
        $this->auditService->log('qo_objective_updated', $user->id, $objective->id, $request->ip(), [
            'code' => $objective->code,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', 'Sasaran Mutu berhasil diperbarui.');
    }

    /**
     * Remove the specified objective from storage.
     */
    public function destroy(Request $request, $id)
    {
        $objective = QualityObjective::findOrFail($id);

        if (Gate::denies('delete', $objective)) {
            abort(403, 'Anda tidak diizinkan menghapus data ini.');
        }

        $code = $objective->code;
        $user = auth()->user();

        $objective->delete();

        // Audit Log
        $this->auditService->log('qo_objective_deleted', $user->id, null, $request->ip(), [
            'id' => $id,
            'code' => $code,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.index')
            ->with('success', "Sasaran Mutu {$code} berhasil dihapus.");
    }

    /**
     * Submit the objective for approval.
     */
    public function submit(Request $request, $id)
    {
        $objective = QualityObjective::findOrFail($id);

        if (Gate::denies('submit', $objective)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengajukan target ini.');
        }

        $user = auth()->user();

        DB::transaction(function () use ($objective, $user, $request) {
            $objective->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Create Approval Log
            QualityObjectiveApproval::create([
                'objective_id' => $objective->id,
                'user_id' => $user->id,
                'role' => $user->roles->first()?->name ?? 'kabag',
                'action' => 'submit',
                'stage' => 'submitted',
                'note' => $request->input('note', 'Diajukan oleh KABAG'),
                'ip_address' => $request->ip(),
            ]);
        });

        // Audit Log
        $this->auditService->log('qo_objective_submitted', $user->id, $objective->id, $request->ip());

        return redirect()
            ->route('quality-objectives.objectives.show', $objective->id)
            ->with('success', 'Sasaran Mutu berhasil diajukan untuk persetujuan.');
    }

    /**
     * Show preview form for renewal.
     */
    public function renewForm($id)
    {
        $objective = QualityObjective::with(['period', 'department', 'pic'])->findOrFail($id);
        $user = auth()->user();

        // Enforce KABAG restrictions
        if ($user->hasAnyRole(['kabag']) && $user->department_id !== $objective->department_id) {
            abort(403, 'Anda tidak dapat memperpanjang sasaran mutu dari departemen lain.');
        }

        // Fetch other periods as targets
        $periods = QualityObjectivePeriod::where('id', '!=', $objective->period_id)
            ->orderByDesc('year')
            ->get();

        return view('quality-objectives.objectives.renew_preview', compact('objective', 'periods'));
    }

    /**
     * Renew an objective for a new period (Annual cloning).
     */
    public function renew(Request $request, $id)
    {
        $oldObjective = QualityObjective::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'target_period_id' => ['required', 'integer', 'exists:quality_objective_periods,id']
        ]);

        $targetPeriod = QualityObjectivePeriod::findOrFail($request->input('target_period_id'));

        // Enforce KABAG restrictions
        if ($user->hasAnyRole(['kabag']) && $user->department_id !== $oldObjective->department_id) {
            abort(403, 'Anda tidak dapat memperpanjang sasaran mutu dari departemen lain.');
        }

        // Check if already renewed for this target period
        $exists = QualityObjective::where('renewal_of_id', $oldObjective->id)
            ->where('period_id', $targetPeriod->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Sasaran mutu ini sudah diperpanjang untuk periode target tersebut.');
        }

        $newObjective = DB::transaction(function () use ($oldObjective, $targetPeriod, $user) {
            // Generate Code
            $newCode = $this->codeService->generateCode($oldObjective->department_id, $targetPeriod->year);

            // Clone attributes
            return QualityObjective::create([
                'period_id' => $targetPeriod->id,
                'department_id' => $oldObjective->department_id,
                'code' => $newCode,
                'process_name' => $oldObjective->process_name,
                'objective_statement' => $oldObjective->objective_statement,
                'kpi_indicator' => $oldObjective->kpi_indicator,
                'unit' => $oldObjective->unit,
                'target_value' => $oldObjective->target_value,
                'target_polarity' => $oldObjective->target_polarity,
                'monitoring_frequency' => $oldObjective->monitoring_frequency,
                'measurement_method' => $oldObjective->measurement_method,
                'pic_user_id' => $oldObjective->pic_user_id,
                'status' => 'draft',
                'renewal_of_id' => $oldObjective->id,
                'is_mandatory' => $oldObjective->is_mandatory,
                'sort_order' => $oldObjective->sort_order,
                'created_by' => $user->id,
            ]);
        });

        // Audit Log
        $this->auditService->log('qo_objective_renewed', $user->id, $newObjective->id, $request->ip(), [
            'renewal_of_id' => $oldObjective->id,
            'old_code' => $oldObjective->code,
            'new_code' => $newObjective->code,
        ]);

        return redirect()
            ->route('quality-objectives.objectives.show', $newObjective->id)
            ->with('success', "Sasaran Mutu berhasil diperpanjang ke tahun {$targetPeriod->year} sebagai draft.");
    }
}
