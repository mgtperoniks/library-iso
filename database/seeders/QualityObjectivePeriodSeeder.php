<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityObjectivePeriod;

class QualityObjectivePeriodSeeder extends Seeder
{
    public function run(): void
    {
        QualityObjectivePeriod::updateOrCreate(
            ['year' => 2026],
            [
                'title' => 'Tahun Anggaran & Sasaran Mutu 2026',
                'status' => 'active',
                'description' => 'Periode pemantauan aktif berjalan untuk UAT dan validasi workflow.',
            ]
        );
    }
}
