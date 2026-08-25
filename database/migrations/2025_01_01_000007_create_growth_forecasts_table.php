<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growth_forecasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->unsignedInteger('forecast_horizon_days')->default(90);
            $table->date('forecast_date');
            $table->decimal('predicted_weight_kg', 5, 2)->nullable();
            $table->decimal('predicted_height_cm', 5, 2)->nullable();
            $table->string('predicted_status')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();
            $table->string('model_version')->default('stub-v0');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growth_forecasts');
    }
};