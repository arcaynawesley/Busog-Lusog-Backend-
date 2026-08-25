<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('barangay_id')->nullable();
            $table->enum('residence_type', ['urban', 'rural']);
            $table->unsignedInteger('household_size');
            $table->unsignedInteger('num_children_under5')->default(0);
            $table->enum('economic_status', ['low', 'middle', 'high']);
            $table->enum('water_source', ['improved', 'unimproved']);
            $table->enum('latrine_facility', ['available', 'not_available']);
            $table->boolean('participates_feeding_program')->default(false);
            $table->enum('maternal_literacy', ['can_read', 'cannot_read']);
            $table->enum('anc_attendance', ['adequate', 'inadequate', 'none']);
            $table->timestamps();

            $table->foreign('barangay_id')->references('id')->on('barangays')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};