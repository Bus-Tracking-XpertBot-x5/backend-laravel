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
        Schema::table('bus_locations', function (Blueprint $table) {
            $table->foreignId('bus_id')->constrained('buses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_locations', function (Blueprint $table) {
            $table->dropForeign(['bus_id']);
            $table->dropForeign('bus_id');
        });
    }
};
