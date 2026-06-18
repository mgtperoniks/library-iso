<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityObjective;
use App\Models\QualityObjectiveActionPlan;

class QualityObjectiveActionPlanSeeder extends Seeder
{
    public function run(): void
    {
        // helper to get objective ID by code
        $getObjId = function ($code) {
            $obj = QualityObjective::where('code', $code)->first();
            return $obj ? $obj->id : null;
        };

        // helper to get PIC ID of objective
        $getPicId = function ($code) {
            $obj = QualityObjective::where('code', $code)->first();
            return $obj ? $obj->pic_user_id : 1;
        };

        // 15 Action Plans Data according to required distribution
        $actionPlansData = [
            // COMPLETED (6 Items)
            [
                'code' => 'QO-COR-01',
                'program_name' => 'Kalibrasi burner tungku peleburan logam Cor',
                'target_date' => '2026-02-15',
                'description' => 'Kalibrasi selesai, performa pemanasan naik.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],
            [
                'code' => 'QO-BBT-02',
                'program_name' => 'Penggantian mata pahat insert bubut CNC merk baru',
                'target_date' => '2026-03-10',
                'description' => 'Mata pahat diganti, kehalusan permukaan terjamin.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],
            [
                'code' => 'QO-PP-01',
                'program_name' => 'Stock opname cycle count mingguan area gudang bahan baku',
                'target_date' => '2026-04-05',
                'description' => 'Akurasi stock naik dari 96% ke 98.2%.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],
            [
                'code' => 'QO-QA-02',
                'program_name' => 'Sertifikasi kalibrasi tahunan timbangan jembatan utama',
                'target_date' => '2026-04-20',
                'description' => 'Sertifikat kalibrasi dari KAN terbit.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],
            [
                'code' => 'QO-PBL-01',
                'program_name' => 'Audit lapangan ke supplier scrap besi cor lokal',
                'target_date' => '2026-05-12',
                'description' => 'Kualitas supplier scrap dievaluasi langsung.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],
            [
                'code' => 'QO-MKT-03',
                'program_name' => 'Penyebaran kuesioner kepuasan pelanggan semester I',
                'target_date' => '2026-05-25',
                'description' => 'Kuesioner terisi lengkap oleh 30 pelanggan utama.',
                'status' => 'completed',
                'progress_pct' => 100,
            ],

            // IN PROGRESS (4 Items)
            [
                'code' => 'QO-MKT-02',
                'program_name' => 'Penjajakan pasar ekspor baru ke wilayah Asia Timur',
                'target_date' => '2026-08-15',
                'description' => 'Dalam tahap korespondensi dengan agen ekspor.',
                'status' => 'in_progress',
                'progress_pct' => 40,
            ],
            [
                'code' => 'QO-HR-01',
                'program_name' => 'Implementasi sistem scan kartu absensi barcode baru',
                'target_date' => '2026-09-01',
                'description' => 'Pemasangan hardware absensi selesai 50%.',
                'status' => 'in_progress',
                'progress_pct' => 50,
            ],
            [
                'code' => 'QO-MR-01',
                'program_name' => 'Pelatihan internal auditor ISO 9001:2015 Clause 9.2',
                'target_date' => '2026-07-20',
                'description' => 'Materi pelatihan disiapkan oleh MR.',
                'status' => 'open',
                'progress_pct' => 10,
            ],
            [
                'code' => 'QO-COR-01',
                'program_name' => 'Preventive maintenance berkala conveyor scrap cor',
                'target_date' => '2026-07-10',
                'description' => 'Menunggu jadwal downtime mingguan.',
                'status' => 'open',
                'progress_pct' => 0,
            ],

            // DUE SOON (3 Items)
            [
                'code' => 'QO-QA-01',
                'program_name' => 'Penyusunan Peta Kendali (SPC) untuk area finishing fitting',
                'target_date' => '2026-06-22',
                'description' => 'Draft SPC sedang direview oleh Kabag QC.',
                'status' => 'in_progress',
                'progress_pct' => 80,
            ],
            [
                'code' => 'QO-PBL-02',
                'program_name' => 'Rapat koordinasi perbaikan keterlambatan pengiriman kokas',
                'target_date' => '2026-06-25',
                'description' => 'Undangan rapat koordinasi dikirim ke supplier.',
                'status' => 'open',
                'progress_pct' => 20,
            ],
            [
                'code' => 'QO-BBT-01',
                'program_name' => 'Uji coba coolant cair merk Shell untuk menaikkan kecepatan bubut',
                'target_date' => '2026-06-28',
                'description' => 'Sampel coolant sudah datang di gudang.',
                'status' => 'in_progress',
                'progress_pct' => 60,
            ],

            // OVERDUE (2 Items)
            [
                'code' => 'QO-MTC-01',
                'program_name' => 'Overhaul minor hidrolik molding press stamping #3',
                'target_date' => '2026-06-12',
                'description' => 'Tertunda karena sparepart seal hidrolik belum datang.',
                'status' => 'in_progress',
                'progress_pct' => 75,
            ],
            [
                'code' => 'QO-PP-05',
                'program_name' => 'Penyelarasan parameter lead time scrap besi pada sistem ERP',
                'target_date' => '2026-06-15',
                'description' => 'Kabag gudang sedang mencocokkan data aktual.',
                'status' => 'open',
                'progress_pct' => 30,
            ],
        ];

        foreach ($actionPlansData as $data) {
            $objId = $getObjId($data['code']);
            if (!$objId) {
                continue;
            }

            QualityObjectiveActionPlan::updateOrCreate(
                [
                    'objective_id' => $objId,
                    'program_name' => $data['program_name'],
                ],
                [
                    'pic_user_id' => $getPicId($data['code']),
                    'target_date' => $data['target_date'],
                    'budget_estimated' => 0.0,
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'progress_pct' => $data['progress_pct'],
                ]
            );
        }
    }
}
