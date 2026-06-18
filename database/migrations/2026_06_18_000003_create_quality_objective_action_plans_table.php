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
        Schema::create('quality_objective_action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quality_objectives')->cascadeOnDelete();
            $table->tinyInteger('sequence')->default(1);
            $table->string('program_name', 200);
            $table->text('description')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date');
            $table->date('actual_date')->nullable();
            $table->decimal('budget_estimated', 15, 2)->nullable();
            $table->tinyInteger('progress_pct')->default(0);
            $table->enum('status', ['open', 'in_progress', 'completed', 'overdue', 'cancelled'])->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['objective_id', 'status'], 'idx_qoap_obj_status');
            $table->index(['target_date', 'status'], 'idx_qoap_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_objective_action_plans');
    }
};
