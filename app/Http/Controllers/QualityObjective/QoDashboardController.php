<?php

namespace App\Http\Controllers\QualityObjective;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\QualityObjective;
use App\Models\QualityObjectivePeriod;
use App\Models\QualityObjectiveMonitoring;
use Illuminate\Http\Request;

class QoDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $periods = QualityObjectivePeriod::orderByDesc('year')->get();
        $selectedPeriodId = $request->input('period_id');

        if (!$selectedPeriodId) {
            $activePeriod = QualityObjectivePeriod::where('status', 'active')->first();
            $selectedPeriodId = $activePeriod ? $activePeriod->id : $periods->first()?->id;
        }

        // Base query for objectives in selected period
        $objectivesQuery = QualityObjective::with(['department', 'period', 'monitorings'])
            ->where('period_id', $selectedPeriodId);

        $objectives = $objectivesQuery->get();

        // 1. Performance KPIs
        $countExcellent = 0;
        $countOnTrack = 0;
        $countAtRisk = 0;
        $countOffTrack = 0;
        $countNotReported = 0;
        $totalCompliancePct = 0;

        foreach ($objectives as $obj) {
            $status = $obj->current_achievement_status;
            if ($status === 'excellent') $countExcellent++;
            elseif ($status === 'on_track') $countOnTrack++;
            elseif ($status === 'at_risk') $countAtRisk++;
            elseif ($status === 'off_track') $countOffTrack++;
            else $countNotReported++;

            $totalCompliancePct += (float) $obj->reporting_compliance_pct;
        }

        $totalCount = $objectives->count();
        $avgCompliance = $totalCount > 0 ? round($totalCompliancePct / $totalCount, 1) : 100.0;

        // 2. Objectives Requiring Attention (off_track & at_risk)
        $attentionList = $objectives->filter(function ($obj) {
            $status = $obj->current_achievement_status;
            return in_array($status, ['off_track', 'at_risk']);
        })->sort(function ($a, $b) {
            $statusA = $a->current_achievement_status;
            $statusB = $b->current_achievement_status;

            if ($statusA === 'off_track' && $statusB !== 'off_track') {
                return -1;
            }
            if ($statusA !== 'off_track' && $statusB === 'off_track') {
                return 1;
            }

            // Sort by achievement percentage ascending
            $pctA = (float) $a->current_achievement_pct;
            $pctB = (float) $b->current_achievement_pct;
            return $pctA <=> $pctB;
        })->take(10);

        // 3. Action Plan Due Soon (Decision #3: 10-day warning threshold)
        $today = now()->startOfDay();
        $actionPlansQuery = \App\Models\QualityObjectiveActionPlan::with(['objective.department', 'pic'])
            ->whereHas('objective', function ($query) use ($selectedPeriodId) {
                $query->where('period_id', $selectedPeriodId);
            })
            ->whereNotIn('status', ['completed', 'cancelled']);

        $allActionPlans = $actionPlansQuery->get();

        $dueSoonActionPlans = $allActionPlans->filter(function ($plan) use ($today) {
            $targetDate = \Carbon\Carbon::parse($plan->target_date)->startOfDay();
            $diffDays = $today->diffInDays($targetDate, false);
            $plan->remaining_days = $diffDays; // Inject remaining days helper

            // Classify: Overdue (diffDays < 0) or Due Soon (0 <= diffDays <= 10)
            return $diffDays <= 10;
        })->sort(function ($a, $b) {
            return $a->remaining_days <=> $b->remaining_days;
        })->take(10);

        // 4. Reporting Discipline (Decision #1 & #2: "Belum Melapor" with Grace Period)
        $currentDate = now();
        $currentYear = (int) $currentDate->format('Y');
        $currentMonth = (int) $currentDate->format('n');
        $currentDay = (int) $currentDate->format('j');
        $missingReports = collect();

        // Alert activated only from day 6 onwards (Grace Period)
        if ($currentDay >= 6) {
            $activeObjectives = QualityObjective::with(['department', 'monitorings'])
                ->where('period_id', $selectedPeriodId)
                ->whereIn('status', ['active', 'approved'])
                ->get();

            foreach ($activeObjectives as $obj) {
                $freq = $obj->monitoring_frequency;
                $shouldReport = false;

                if ($freq === 'monthly') {
                    $shouldReport = true;
                } elseif ($freq === 'quarterly' && in_array($currentMonth, [3, 6, 9, 12])) {
                    $shouldReport = true;
                } elseif ($freq === 'biannual' && in_array($currentMonth, [6, 12])) {
                    $shouldReport = true;
                } elseif ($freq === 'annual' && $currentMonth === 12) {
                    $shouldReport = true;
                }

                if ($shouldReport) {
                    $hasReport = $obj->monitorings->first(function ($mon) use ($currentYear, $currentMonth) {
                        return $mon->period_year === $currentYear && $mon->period_month === $currentMonth;
                    });

                    if (!$hasReport) {
                        $expectedPeriod = $currentDate->format('F Y');
                        $missingSince = $currentDate->startOfMonth()->addDays(5)->format('d/m/Y');

                        $missingReports->push([
                            'objective' => $obj,
                            'expected_period' => $expectedPeriod,
                            'missing_since' => $missingSince,
                            'delay_days' => $currentDay - 5
                        ]);
                    }
                }
            }
        }

        // 5. Department Ranking
        $deptRankings = [];
        if ($totalCount > 0) {
            $grouped = $objectives->groupBy('department_id');
            foreach ($grouped as $deptId => $deptObjs) {
                $dept = $deptObjs->first()->department;
                $validObjs = $deptObjs->filter(fn($o) => $o->overall_achievement_pct !== null);
                
                $avgAchievement = $validObjs->count() > 0 
                    ? $validObjs->avg('overall_achievement_pct') 
                    : null;

                $deptRankings[] = [
                    'code' => $dept->code,
                    'name' => $dept->name,
                    'avg_achievement' => $avgAchievement,
                    'total_objectives' => $deptObjs->count()
                ];
            }

            usort($deptRankings, function ($a, $b) {
                if ($a['avg_achievement'] === null) return 1;
                if ($b['avg_achievement'] === null) return -1;
                return $b['avg_achievement'] <=> $a['avg_achievement'];
            });
        }

        return view('quality-objectives.dashboard.index', compact(
            'periods',
            'selectedPeriodId',
            'totalCount',
            'countExcellent',
            'countOnTrack',
            'countAtRisk',
            'countOffTrack',
            'countNotReported',
            'avgCompliance',
            'attentionList',
            'dueSoonActionPlans',
            'missingReports',
            'deptRankings'
        ));
    }
}
