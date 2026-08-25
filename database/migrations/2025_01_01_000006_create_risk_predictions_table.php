<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_predictions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->uuid('health_record_id')->nullable();
            $table->enum('risk_level', ['low', 'moderate', 'high', 'very_high']);
            $table->decimal('risk_score', 5, 4);
            $table->string('model_version')->default('rule-based-v0');
            $table->jsonb('top_factors')->default('[]');
            $table->timestamp('predicted_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->foreign('health_record_id')->references('id')->on('health_records')->nullOnDelete();
            $table->index(['child_id', 'predicted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_predictions');
    }
};