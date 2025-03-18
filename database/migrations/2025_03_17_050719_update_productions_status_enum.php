<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE productions MODIFY COLUMN status ENUM('waiting_approval', 'running', 'problem', 'finished', 'paused') DEFAULT 'waiting_approval'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE productions MODIFY COLUMN status ENUM('waiting_approval', 'running', 'problem', 'finished') DEFAULT 'waiting_approval'");
    }
};