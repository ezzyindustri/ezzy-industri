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
            $table->timestamp('last_updated')->nullable()->after('oee_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oee_records', function (Blueprint $table) {
            $table->dropColumn('last_updated');
        });
    }
};