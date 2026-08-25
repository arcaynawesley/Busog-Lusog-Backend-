<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->uuid('risk_prediction_id')->nullable();
            $table->enum('intervention_type', [
                'home_visit', 'feeding_program', 'vitamin_a', 'nutrition_counseling',
                'water_sanitation_education', 'medical_referral', 'other',
            ]);
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->boolean('is_recommended')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->timestamps();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->foreign('risk_prediction_id')->references('id')->on('risk_predictions')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};