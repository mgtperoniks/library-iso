<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityObjective;
use App\Models\QualityObjectivePeriod;
use App\Models\Department;
use App\Models\User;

class QualityObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        $period = QualityObjectivePeriod::where('year', 2026)->first();
        if (!$period) {
            return;
        }

        // Helper function to find department ID
        $getDeptId = function ($code) {
            $dept = Department::where('code', $code)->first();
            return $dept ? $dept->id : null;
        };

        // Helper function for dynamic fallback user ID
        $getFallbackUserId = function () {
            // A. User dengan role director (atau memuat kata director/direktur di email/role)
            $director = User::where('email', 'like', '%direktur%')->first();
            if ($director) return $director->id;

            // B. User dengan role admin
            $admin = User::role('admin')->first();
            if ($admin) return $admin->id;

            // C. User pertama yang aktif
            $firstUser = User::first();
            return $firstUser ? $firstUser->id : null;
        };

        $fallbackUserId = $getFallbackUserId();

        // Helper function to find user ID by email with dynamic fallback
        $getUserId = function ($email) use ($fallbackUserId) {
            $user = User::where('email', $email)->first();
            return $user ? $user->id : $fallbackUserId;
        };

        $objectivesData = [
            // PRODUKSI
            [
                'department_code' => 'COR-PF',
                'code' => 'QO-COR-01',
                'process_name' => 'Produksi Cor Fitting',
                'objective_statement' => 'Mencapai target output volume peleburan logam cor fitting logam.',
                'kpi_indicator' => 'Output Volume Cor',
                'unit' => 'ton',
                'target_value' => 200.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagcorfitting@peronik.com',
            ],
            [
                'department_code' => 'COR-PF',
                'code' => 'QO-COR-02',
                'process_name' => 'Kualitas Produk Cor',
                'objective_statement' => 'Menekan tingkat produk reject (BS) hasil peleburan logam cor.',
                'kpi_indicator' => 'Reject Cor Rate',
                'unit' => '%',
                'target_value' => 3.0,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagcorfitting@peronik.com',
            ],
            [
                'department_code' => 'BBT-FL',
                'code' => 'QO-BBT-01',
                'process_name' => 'Produksi Bubut Flange',
                'objective_statement' => 'Mencapai target volume pembubutan produk Flange.',
                'kpi_indicator' => 'Output Volume Bubut',
                'unit' => 'ton',
                'target_value' => 150.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagbubutflange@peroniks.com',
            ],
            [
                'department_code' => 'BBT-FL',
                'code' => 'QO-BBT-02',
                'process_name' => 'Kualitas Hasil Bubut',
                'objective_statement' => 'Menekan produk reject hasil pembubutan Flange.',
                'kpi_indicator' => 'Reject Bubut Rate',
                'unit' => '%',
                'target_value' => 2.0,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagbubutflange@peroniks.com',
            ],
            [
                'department_code' => 'BOR-FL',
                'code' => 'QO-BOR-01',
                'process_name' => 'Produksi Bor Flange',
                'objective_statement' => 'Mencapai target volume pengeboran lubang Flange.',
                'kpi_indicator' => 'Output Volume Bor',
                'unit' => 'ton',
                'target_value' => 120.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagbubutflange@peroniks.com',
            ],
            [
                'department_code' => 'BOR-FL',
                'code' => 'QO-BOR-02',
                'process_name' => 'Kualitas Hasil Bor',
                'objective_statement' => 'Menekan produk reject hasil pengeboran Flange.',
                'kpi_indicator' => 'Reject Bor Rate',
                'unit' => '%',
                'target_value' => 1.5,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagbubutflange@peroniks.com',
            ],
            [
                'department_code' => 'QA',
                'code' => 'QO-QA-01',
                'process_name' => 'Kebocoran Kualitas QC',
                'objective_statement' => 'Mencegah produk defect lolos ke pelanggan (QC Escape).',
                'kpi_indicator' => 'QC Escape Rate',
                'unit' => '%',
                'target_value' => 0.1,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagqc@peroniks.com',
            ],
            [
                'department_code' => 'MTC',
                'code' => 'QO-MTC-01',
                'process_name' => 'Perawatan Mesin Stamping',
                'objective_statement' => 'Meminimalkan total downtime tidak terencana mesin stamping utama.',
                'kpi_indicator' => 'Downtime Mesin',
                'unit' => 'jam',
                'target_value' => 24.0,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagmaintenance@peroniks.com',
            ],

            // PPIC
            [
                'department_code' => 'PPIC',
                'code' => 'QO-PP-01',
                'process_name' => 'Akurasi Persediaan Bahan Baku',
                'objective_statement' => 'Mencapai akurasi kecocokan data fisik dengan sistem (Stock Card) bahan baku.',
                'kpi_indicator' => 'Akurasi Stock Bahan Baku',
                'unit' => '%',
                'target_value' => 98.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerppic@peroniks.com',
            ],
            [
                'department_code' => 'PPIC',
                'code' => 'QO-PP-02',
                'process_name' => 'Akurasi Persediaan Barang Jadi',
                'objective_statement' => 'Mencapai akurasi kecocokan stock barang jadi di gudang utama.',
                'kpi_indicator' => 'Akurasi Stock Barang Jadi',
                'unit' => '%',
                'target_value' => 99.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerppic@peroniks.com',
            ],
            [
                'department_code' => 'PPIC',
                'code' => 'QO-PP-03',
                'process_name' => 'Akurasi Persediaan Sparepart',
                'objective_statement' => 'Memastikan stock sparepart kritis mesin selalu akurat.',
                'kpi_indicator' => 'Akurasi Stock Sparepart',
                'unit' => '%',
                'target_value' => 95.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerppic@peroniks.com',
            ],
            [
                'department_code' => 'PPIC',
                'code' => 'QO-PP-04',
                'process_name' => 'Kesesuaian Rencana Produksi',
                'objective_statement' => 'Mencapai ketepatan realisasi produksi terhadap jadwal rencana (MPS).',
                'kpi_indicator' => 'Schedule Adherence',
                'unit' => '%',
                'target_value' => 95.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerppic@peroniks.com',
            ],
            [
                'department_code' => 'PPIC',
                'code' => 'QO-PP-05',
                'process_name' => 'Kebutuhan Bahan Baku',
                'objective_statement' => 'Mencapai akurasi estimasi perencanaan kebutuhan bahan baku pabrik (MRP).',
                'kpi_indicator' => 'Material Plan Accuracy',
                'unit' => '%',
                'target_value' => 95.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerppic@peroniks.com',
            ],

            // QA
            [
                'department_code' => 'QA',
                'code' => 'QO-QA-02',
                'process_name' => 'Kalibrasi Tepat Waktu',
                'objective_statement' => 'Memastikan seluruh alat ukur dan timbangan terkalibrasi sebelum jatuh tempo.',
                'kpi_indicator' => 'On-Time Calibration',
                'unit' => '%',
                'target_value' => 100.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagqc@peroniks.com',
            ],
            [
                'department_code' => 'QA',
                'code' => 'QO-QA-03',
                'process_name' => 'Penanganan Keluhan Pelanggan',
                'objective_statement' => 'Meminimalkan komplain resmi pelanggan terkait kualitas produk.',
                'kpi_indicator' => 'Customer Complaints',
                'unit' => 'keluhan',
                'target_value' => 0.0,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagqc@peroniks.com',
            ],
            [
                'department_code' => 'QA',
                'code' => 'QO-QA-04',
                'process_name' => 'Akurasi Pengujian Material',
                'objective_statement' => 'Memastikan hasil uji spectrometer material cor akurat 100%.',
                'kpi_indicator' => 'Lab Testing Accuracy',
                'unit' => '%',
                'target_value' => 99.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'kabagqc@peroniks.com',
            ],

            // PURCHASING
            [
                'department_code' => 'PBL',
                'code' => 'QO-PBL-01',
                'process_name' => 'Evaluasi Supplier',
                'objective_statement' => 'Memastikan performa supplier material berada pada rating aman.',
                'kpi_indicator' => 'Supplier Rating Score',
                'unit' => '%',
                'target_value' => 95.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerpurchasing@peroniks.com',
            ],
            [
                'department_code' => 'PBL',
                'code' => 'QO-PBL-02',
                'process_name' => 'Ketepatan Pengiriman Supplier',
                'objective_statement' => 'Mencapai ketepatan jadwal kedatangan material dari supplier.',
                'kpi_indicator' => 'On-Time Delivery Supplier',
                'unit' => '%',
                'target_value' => 95.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerpurchasing@peroniks.com',
            ],
            [
                'department_code' => 'PBL',
                'code' => 'QO-PBL-03',
                'process_name' => 'Ketepatan Volume Material',
                'objective_statement' => 'Memastikan kuantitas material yang dikirim supplier sesuai PO.',
                'kpi_indicator' => 'Delivery Volume Accuracy',
                'unit' => '%',
                'target_value' => 98.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerpurchasing@peroniks.com',
            ],

            // HRD
            [
                'department_code' => 'PRS',
                'code' => 'QO-HR-01',
                'process_name' => 'Kehadiran Karyawan',
                'objective_statement' => 'Mempertahankan tingkat kehadiran karyawan pabrik.',
                'kpi_indicator' => 'Employee Attendance Rate',
                'unit' => '%',
                'target_value' => 97.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managerhr@peroniks.com',
            ],

            // MR
            [
                'department_code' => 'MR',
                'code' => 'QO-MR-01',
                'process_name' => 'Audit Internal & Tinjauan Manajemen',
                'objective_statement' => 'Memastikan audit mutu internal terlaksana 100% tepat waktu.',
                'kpi_indicator' => 'Audit Completion Rate',
                'unit' => '%',
                'target_value' => 100.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'MR@peroniks.com',
            ],

            // MARKETING
            [
                'department_code' => 'MKT',
                'code' => 'QO-MKT-01',
                'process_name' => 'Volume Penjualan Lokal',
                'objective_statement' => 'Mencapai target volume penjualan logam lokal.',
                'kpi_indicator' => 'Sales Volume Local',
                'unit' => 'ton',
                'target_value' => 1500.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managermarketing@peroniks.com',
            ],
            [
                'department_code' => 'MKT',
                'code' => 'QO-MKT-02',
                'process_name' => 'Volume Penjualan Ekspor',
                'objective_statement' => 'Mencapai target volume penjualan logam ekspor.',
                'kpi_indicator' => 'Sales Volume Export',
                'unit' => 'ton',
                'target_value' => 800.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managermarketing@peroniks.com',
            ],
            [
                'department_code' => 'MKT',
                'code' => 'QO-MKT-03',
                'process_name' => 'Index Kepuasan Pelanggan',
                'objective_statement' => 'Mempertahankan indeks kepuasan pelanggan tahunan.',
                'kpi_indicator' => 'Customer Satisfaction Index',
                'unit' => '%',
                'target_value' => 85.0,
                'target_polarity' => 'gte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managermarketing@peroniks.com',
            ],

            // EXIM (fallback to Marketing/MKT)
            [
                'department_code' => 'MKT',
                'code' => 'QO-EXM-01',
                'process_name' => 'Denda Keterlambatan Ekspor-Impor',
                'objective_statement' => 'Menghindari biaya denda (demurrage/storage) eksim.',
                'kpi_indicator' => 'EXIM Penalty Cases',
                'unit' => 'kasus',
                'target_value' => 0.0,
                'target_polarity' => 'lte',
                'monitoring_frequency' => 'monthly',
                'pic_email' => 'managermarketing@peroniks.com',
            ],
        ];

        foreach ($objectivesData as $data) {
            $deptId = $getDeptId($data['department_code']);
            if (!$deptId) {
                continue; // Skip if department not found
            }

            QualityObjective::updateOrCreate(
                [
                    'period_id' => $period->id,
                    'code' => $data['code']
                ],
                [
                    'department_id' => $deptId,
                    'process_name' => $data['process_name'],
                    'objective_statement' => $data['objective_statement'],
                    'kpi_indicator' => $data['kpi_indicator'],
                    'unit' => $data['unit'],
                    'target_value' => $data['target_value'],
                    'target_polarity' => $data['target_polarity'],
                    'monitoring_frequency' => $data['monitoring_frequency'],
                    'pic_user_id' => $getUserId($data['pic_email']),
                    'status' => 'active',
                    'created_by' => $fallbackUserId,
                ]
            );
        }
    }
}
