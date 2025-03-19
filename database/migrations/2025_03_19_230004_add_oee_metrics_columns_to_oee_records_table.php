<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oee_records', function (Blueprint $table) {
            // Tambahkan kolom yang belum ada
            if (!Schema::hasColumn('oee_records', 'target_output')) {
                $table->integer('target_output')->default(0);
            }
            
            if (!Schema::hasColumn('oee_records', 'availability')) {
                $table->decimal('availability', 8, 2)->default(0);
            }
            
            if (!Schema::hasColumn('oee_records', 'performance')) {
                $table->decimal('performance', 8, 2)->default(0);
            }
            
            if (!Schema::hasColumn('oee_records', 'quality')) {
                $table->decimal('quality', 8, 2)->default(0);
            }
            
            // Pastikan kolom lain yang digunakan dalam model juga ada
            if (!Schema::hasColumn('oee_records', 'downtime_problems')) {
                $table->integer('downtime_problems')->default(0);
            }
            
            if (!Schema::hasColumn('oee_records', 'downtime_maintenance')) {
                $table->integer('downtime_maintenance')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oee_records', function (Blueprint $table) {
            $table->dropColumn([
                'target_output',
                'availability',
                'performance',
                'quality',
                'downtime_problems',
                'downtime_maintenance'
            ]);
        });
    }
};