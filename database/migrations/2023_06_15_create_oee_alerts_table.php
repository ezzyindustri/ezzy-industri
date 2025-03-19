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
        Schema::create('oee_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->float('oee_score');
            $table->float('target_oee');
            $table->timestamp('sent_at');
            $table->timestamps();
            
            // Ensure we don't send duplicate alerts
            $table->unique(['production_id', 'machine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oee_alerts');
    }
};