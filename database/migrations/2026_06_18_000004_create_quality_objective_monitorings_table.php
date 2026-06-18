<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quality_objective_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quality_objectives')->cascadeOnDelete();
            $table->string('period_label', 20);
            $table->year('period_year');
            $table->tinyInteger('period_month')->nullable();
            $table->tinyInteger('period_quarter')->nullable();
            $table->decimal('target_snapshot', 10, 2);
            $table->decimal('realization_value', 10, 2)->nullable();
            $table->decimal('achievement_pct', 5, 2)->nullable();
            $table->text('data_source')->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('input_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('capa_triggered')->default(false);
            $table->unsignedBigInteger('capa_ref_id')->nullable();
            $table->timestamps();

            $table->index('objective_id', 'idx_qom_obj');
            $table->index(['objective_id', 'period_year', 'period_month'], 'idx_qom_period');
            $table->unique(['objective_id', 'period_label'], 'uq_qom_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_objective_monitorings');
    }
};
