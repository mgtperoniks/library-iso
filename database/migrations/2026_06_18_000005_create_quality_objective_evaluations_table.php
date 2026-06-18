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
        Schema::create('quality_objective_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quality_objectives')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('quality_objective_periods')->restrictOnDelete();
            $table->decimal('avg_achievement_pct', 5, 2)->nullable();
            $table->tinyInteger('total_monitoring_count')->nullable();
            $table->tinyInteger('completed_action_count')->nullable();
            $table->enum('evaluation_result', ['achieved', 'partially_achieved', 'not_achieved']);
            $table->text('root_cause')->nullable();
            $table->text('contributing_factors')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('follow_up_action')->nullable();
            $table->enum('next_period_decision', ['renew', 'revise_target', 'close', 'escalate']);
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->foreignId('reviewed_by_dir')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at_dir')->nullable();
            $table->string('management_review_ref', 100)->nullable();
            $table->timestamps();

            $table->unique(['objective_id', 'period_id'], 'uq_eval_obj_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_objective_evaluations');
    }
};
