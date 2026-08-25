<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->uuid('risk_prediction_id')->nullable();
            $table->string('risk_level_at_scheduling')->nullable();
            $table->date('scheduled_date');
            $table->enum('status', ['scheduled', 'completed', 'missed', 'rescheduled'])->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->foreign('risk_prediction_id')->references('id')->on('risk_predictions')->nullOnDelete();
            $table->index(['child_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};