<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_distribution_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('document_version_id')->nullable();
            
            $table->string('doc_code')->nullable();
            $table->string('document_title')->nullable();
            $table->string('version_label')->nullable();
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('user_department')->nullable();
            
            $table->enum('action', ['preview_pdf', 'download_pdf', 'download_master']);
            $table->string('trace_id')->unique();
            $table->string('ip_address')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_distribution_logs');
    }
};
