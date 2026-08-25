<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('create extension if not exists "uuid-ossp"');
    }

    public function down(): void
    {
        // Leave the extension in place — other tables may still use it.
    }
};