<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityObjective;
use App\Models\QualityObjectiveMonitoring;

class QualityObjectiveMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $objectives = QualityObjective::all();

        // Helper function to calculate achievement percentage
        $calculatePct = function ($polarity, $target, $realization) {
            if ($realization === null) return null;

            if ($polarity === 'gte') {
                if ($target <= 0) return 100.0;
                return round(($realization / $target) * 100, 2);
            } else {
                if ($realization <= 0) {
                    return 100.0;
                }
                return round(($target / $realization) * 100, 2);
            }
        };

        // Realistic monthly realization matrices for January-June 2026
        $realizationMatrix = [
            // PRODUKSI
            'QO-COR-01' => [198.0, 201.0, 199.0, 202.0, 201.0, 205.0], // target >= 200, trend: up, On Track
            'QO-COR-02' => [2.8, 2.5, 2.7, 2.1, 1.8, 1.5],          // target <= 3.0, trend: up (lower defect), Excellent
            'QO-BBT-01' => [148.0, 149.0, 151.0, 150.0, 152.0, 153.0], // target >= 150, trend: up, On Track
            'QO-BBT-02' => [1.9, 1.8, 2.1, 1.9, 2.0, 1.8],          // target <= 2.0, trend: stable, On Track
            'QO-BOR-01' => [118.0, 120.0, 119.0, 121.0, 120.0, 121.0], // target >= 120, trend: stable, On Track
            'QO-BOR-02' => [1.2, 1.3, 1.2, 1.4, 1.5, 1.6],          // target <= 1.5, trend: down (more defect), At Risk
            'QO-QA-01'  => [0.05, 0.07, 0.08, 0.09, 0.12, 0.15],       // target <= 0.1, trend: down, Off Track
            'QO-MTC-01' => [15.0, 18.0, 20.0, 23.0, 28.0, 36.0],       // target <= 24, trend: down (worse downtime), Off Track

            // PPIC (Sengaja tidak melaporkan bulan Juni)
            'QO-PP-01'  => [97.5, 98.1, 98.0, 98.2, 98.5, null],     // target >= 98.0, trend: up, On Track
            'QO-PP-02'  => [98.8, 99.1, 99.0, 99.2, 99.1, null],     // target >= 99.0, trend: stable, On Track
            'QO-PP-03'  => [94.5, 95.1, 94.8, 94.0, 93.5, null],     // target >= 95.0, trend: down, At Risk
            'QO-PP-04'  => [95.2, 94.8, 94.0, 93.5, 92.5, null],     // target >= 95.0, trend: down, At Risk
            'QO-PP-05'  => [94.5, 93.0, 91.5, 88.0, 75.0, null],     // target >= 95.0, trend: down, Off Track

            // QA
            'QO-QA-02'  => [100.0, 100.0, 100.0, 100.0, 100.0, 100.0], // target >= 100, trend: stable, On Track/Perfect
            'QO-QA-03'  => [0, 0, 0, 0, 0, 1],                      // target <= 0 (Complaints), trend: down, Off Track
            'QO-QA-04'  => [99.0, 99.1, 99.0, 99.3, 99.1, 99.2],     // target >= 99, trend: stable, On Track

            // PURCHASING
            'QO-PBL-01' => [95.5, 96.0, 95.8, 96.5, 97.8, 98.5],     // target >= 95, trend: up, Excellent
            'QO-PBL-02' => [95.2, 94.8, 94.0, 93.5, 92.0, 91.2],     // target >= 95, trend: down, At Risk
            'QO-PBL-03' => [98.1, 98.0, 98.2, 98.1, 98.0, 98.1],     // target >= 98, trend: stable, On Track

            // HRD
            'QO-HR-01'  => [97.1, 97.2, 97.0, 97.3, 97.2, 97.1],     // target >= 97, trend: stable, On Track

            // MR
            'QO-MR-01'  => [100.0, 100.0, 100.0, 100.0, 100.0, 100.0], // target >= 100, trend: stable, On Track

            // MARKETING
            'QO-MKT-01' => [1480, 1490, 1475, 1460, 1430, 1420],     // target >= 1500, trend: down, At Risk
            'QO-MKT-02' => [805, 820, 815, 840, 890, 920],           // target >= 800, trend: up, Excellent
            'QO-MKT-03' => [85.0, 85.5, 85.2, 85.8, 86.1, 86.0],     // target >= 85, trend: up, On Track
            'QO-EXM-01' => [0, 0, 0, 0, 0, 0],                      // target <= 0, trend: stable, Excellent/Perfect
        ];

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni'
        ];

        foreach ($objectives as $obj) {
            if (!isset($realizationMatrix[$obj->code])) {
                continue;
            }

            $monthlyValues = $realizationMatrix[$obj->code];

            foreach ($months as $monthNum => $monthName) {
                $realizationValue = $monthlyValues[$monthNum - 1];

                // Skip June for PPIC to satisfy UAT
                if ($realizationValue === null) {
                    QualityObjectiveMonitoring::where([
                        'objective_id' => $obj->id,
                        'period_year' => 2026,
                        'period_month' => $monthNum,
                    ])->delete();
                    continue;
                }

                $pct = $calculatePct($obj->target_polarity, $obj->target_value, $realizationValue);

                QualityObjectiveMonitoring::updateOrCreate(
                    [
                        'objective_id' => $obj->id,
                        'period_year' => 2026,
                        'period_month' => $monthNum,
                    ],
                    [
                        'period_label' => "2026-0" . $monthNum,
                        'target_snapshot' => $obj->target_value,
                        'realization_value' => $realizationValue,
                        'achievement_pct' => $pct,
                        'data_source' => 'Laporan Kinerja Bulanan Internal',
                        'notes' => "Realisasi otomatis disintesis oleh seeder UAT untuk bulan {$monthName} 2026.",
                        'is_locked' => ($monthNum < 6), // Lock previous months, keep current month unlocked
                        'input_by' => $obj->pic_user_id,
                    ]
                );
            }
        }
    }
}
