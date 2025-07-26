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
        Schema::create('complaint_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->onDelete('cascade');
            $table->foreignId('resolved_by')->constrained('users')->onDelete('cascade');
            $table->text('resolution_notes');
            $table->text('internal_notes')->nullable();
            $table->enum('resolution_type', ['resolved', 'closed', 'escalated', 'duplicate'])->default('resolved');
            $table->timestamp('resolved_at');
            $table->timestamps();
            
            $table->index(['complaint_id', 'resolved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_resolutions');
    }
};
