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
            // Tambahkan nilai default untuk availability_rate
            $table->float('availability_rate')->default(0)->change();
            
            // Pastikan kolom-kolom OEE lainnya juga memiliki nilai default
            $table->float('performance_rate')->default(0)->change();
            $table->float('quality_rate')->default(0)->change();
            $table->float('target_output')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oee_records', function (Blueprint $table) {
            // Kembalikan kolom-kolom ke kondisi semula tanpa default
            $table->float('availability_rate')->change();
            $table->float('performance_rate')->change();
            $table->float('quality_rate')->change();
            $table->float('target_output')->change();
        });
    }
};