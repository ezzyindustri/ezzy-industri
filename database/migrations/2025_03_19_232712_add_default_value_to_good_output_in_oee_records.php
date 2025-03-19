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
            // Ubah kolom good_output untuk memiliki nilai default 0
            $table->integer('good_output')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oee_records', function (Blueprint $table) {
            // Kembalikan kolom good_output ke kondisi semula tanpa default
            $table->integer('good_output')->change();
        });
    }
};