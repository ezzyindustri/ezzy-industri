<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('machine_id')->constrained();
            $table->string('machine');
            $table->foreignId('product_id')->constrained();
            $table->string('product');
            $table->integer('target_per_shift');
            $table->integer('total_production')->default(0);
            $table->foreignId('shift_id')->constrained();
            $table->enum('status', ['pending', 'running', 'completed', 'stopped'])->default('pending');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productions');
    }
};