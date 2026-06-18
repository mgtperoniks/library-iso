<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class QualityObjectiveDemoResetSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for safe truncation
        Schema::disableForeignKeyConstraints();

        // Clear operational data tables
        DB::table('quality_objective_monitorings')->truncate();
        DB::table('quality_objective_action_plans')->truncate();
        DB::table('quality_objective_approvals')->truncate();
        DB::table('quality_objective_evidences')->truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }
}
