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
        Schema::create('quality_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('quality_objective_periods')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('code', 40)->unique();
            $table->string('process_name', 150);
            $table->text('objective_statement');
            $table->string('kpi_indicator', 200);
            $table->string('unit', 30)->nullable();
            $table->decimal('target_value', 10, 2);
            $table->enum('target_polarity', ['gte', 'lte'])->default('gte');
            $table->enum('monitoring_frequency', ['monthly', 'quarterly', 'biannual', 'annual'])->default('monthly');
            $table->text('measurement_method')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'revision', 'pending_director', 'active', 'evaluated', 'closed', 'rejected', 'renewed'])->default('draft');
            $table->foreignId('renewal_of_id')->nullable()->constrained('quality_objectives')->nullOnDelete();
            $table->boolean('is_mandatory')->default(false);
            $table->tinyInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['period_id', 'department_id'], 'idx_qo_period_dept');
            $table->index('status', 'idx_qo_status');
            $table->index('pic_user_id', 'idx_qo_pic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_objectives');
    }
};
