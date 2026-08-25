<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_predictions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->decimal('likelihood_percent', 5, 2);
            $table->uuid('based_on_intervention_id')->nullable();
            $table->string('model_version')->default('stub-v0');
            $table->timestamp('predicted_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_predictions');
    }
};