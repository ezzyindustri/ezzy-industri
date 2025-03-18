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
        Schema::table('quality_check_details', function (Blueprint $table) {
            $table->decimal('standard_value', 10, 6)->change();
            $table->decimal('measured_value', 10, 6)->change();
            $table->decimal('tolerance_min', 10, 6)->change();
            $table->decimal('tolerance_max', 10, 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_check_details', function (Blueprint $table) {
            $table->decimal('standard_value', 10, 2)->change();
            $table->decimal('measured_value', 10, 2)->change();
            $table->decimal('tolerance_min', 10, 2)->change();
            $table->decimal('tolerance_max', 10, 2)->change();
        });
    }
};