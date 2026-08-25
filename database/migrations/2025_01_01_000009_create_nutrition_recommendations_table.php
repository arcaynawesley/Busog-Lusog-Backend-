<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->uuid('risk_prediction_id')->nullable();
            $table->enum('category', ['meal_plan', 'budgeting', 'supplementation', 'counseling']);
            $table->text('recommendation_text');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->foreign('risk_prediction_id')->references('id')->on('risk_predictions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_recommendations');
    }
};