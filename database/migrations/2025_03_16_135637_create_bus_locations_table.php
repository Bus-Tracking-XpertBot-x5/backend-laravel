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
        Schema::create('bus_locations', function (Blueprint $table) {
            $table->id();
            $table->time('actual_start_time');
            $table->time('actual_end_time');
            $table->enum('status',['scheduled','in_transit','arrived','stopped'])->default('scheduled');
            $table->json('current_location');
            $table->foreignId('route_id')->constrained('routes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_locations');
    }
};
