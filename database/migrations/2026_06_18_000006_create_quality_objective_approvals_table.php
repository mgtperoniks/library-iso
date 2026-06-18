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
        Schema::create('quality_objective_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quality_objectives')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 50);
            $table->enum('action', ['submit', 'approve', 'reject', 'request_revision', 'activate', 'close']);
            $table->string('stage', 30);
            $table->text('note')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('objective_id', 'idx_qoa_obj');
            $table->index('user_id', 'idx_qoa_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_objective_approvals');
    }
};
