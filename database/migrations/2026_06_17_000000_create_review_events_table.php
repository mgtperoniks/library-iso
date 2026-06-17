<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('outcome'); // still_relevant, needs_revision
            $table->text('note')->nullable();
            $table->timestamp('review_date')->useCurrent();
            $table->timestamps();

            $table->index(['document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_events');
    }
};
