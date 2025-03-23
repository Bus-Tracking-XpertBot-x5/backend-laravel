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
        Schema::table('passenger_boardings', function (Blueprint $table) {
            $table->foreignId('bus_location_id')->constrained('bus_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_boardings', function (Blueprint $table) {
            $table->dropForeign(['bus_location_id']);
            $table->dropcolumn('bus_location_id');
        });
    }
};
