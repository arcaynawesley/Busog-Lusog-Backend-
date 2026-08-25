<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->date('record_date');
            $table->unsignedInteger('age_months')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('muac_cm', 4, 1)->nullable();
            $table->decimal('weight_for_age_z', 4, 2)->nullable();
            $table->decimal('height_for_age_z', 4, 2)->nullable();
            $table->decimal('weight_for_height_z', 4, 2)->nullable();
            $table->boolean('diarrhea_past_2weeks')->default(false);
            $table->boolean('febrile_illness_past_2weeks')->default(false);
            $table->boolean('vitamin_a_received')->default(false);
            $table->enum('nutritional_status', [
                'normal', 'underweight', 'stunted', 'wasted', 'severely_wasted', 'overweight',
            ])->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['child_id', 'record_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};