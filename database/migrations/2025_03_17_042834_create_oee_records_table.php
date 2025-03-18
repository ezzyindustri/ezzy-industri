<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained();
            $table->foreignId('production_id')->constrained();
            $table->foreignId('shift_id')->constrained();
            $table->date('date');
            $table->integer('planned_production_time');
            $table->integer('operating_time');
            $table->integer('total_output');
            $table->integer('good_output');
            $table->decimal('availability_rate', 5, 2);
            $table->decimal('performance_rate', 5, 2);
            $table->decimal('quality_rate', 5, 2);
            $table->decimal('oee_score', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oee_records');
    }
};