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
        Schema::create('passenger_boardings', function (Blueprint $table) {
            $table->id();
            $table->integer('current_passenger_count')->default(0);
            $table->time('actual_boarding_time')->nullable();
            $table->time('actual_exit_time')->nullable();
            $table->time('estimated_boarding_time')->nullable();
            $table->time('estimated_exit_time')->nullable();
            $table->enum('status',['pending','boarded','exited','missed'])->default('pending');
            $table->foreignId('passenger_id')->constrained('passengers');
            $table->foreignId('route_id')->constrained('routes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_boardings');
    }
};
