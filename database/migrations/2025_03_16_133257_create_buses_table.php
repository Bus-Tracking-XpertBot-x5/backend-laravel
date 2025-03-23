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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->integer('bus_number');
            $table->integer('max_passengers');
            $table->enum('status',['operational','maintenance','out_of_service'])->default('operational');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
