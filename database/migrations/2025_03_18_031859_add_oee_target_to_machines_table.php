<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('oee_target', 5, 2)->default(85.00); // Default target 85%
            $table->boolean('alert_enabled')->default(true);
            $table->string('alert_email')->nullable();
        });
    }

    public function down()
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn(['oee_target', 'alert_enabled', 'alert_email']);
        });
    }
};