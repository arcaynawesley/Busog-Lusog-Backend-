<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_timeline_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->enum('event_type', [
                'health_record', 'risk_prediction', 'intervention', 'follow_up',
                'recommendation', 'growth_forecast', 'recovery_prediction', 'note',
            ]);
            $table->timestamp('event_date')->useCurrent();
            $table->uuid('related_id')->nullable();
            $table->text('summary');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('child_id')->references('id')->on('children')->cascadeOnDelete();
            $table->index(['child_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_timeline_events');
    }
};