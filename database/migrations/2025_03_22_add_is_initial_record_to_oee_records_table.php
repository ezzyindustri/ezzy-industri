<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('oee_records', function (Blueprint $table) {
            $table->boolean('is_initial_record')->default(false)->after('oee_score');
        });
    }

    public function down()
    {
        Schema::table('oee_records', function (Blueprint $table) {
            $table->dropColumn('is_initial_record');
        });
    }
};