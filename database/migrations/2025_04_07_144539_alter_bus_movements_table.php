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
        Schema::table('bus_movements', function (Blueprint $table) {
            $table->dropColumn('passenger_count');

            $table->integer('booked_passenger_count')->default(0);
            $table->integer('actual_passenger_count')->default(0);
            $table->foreignId('organization_id')->constrained('organizations')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_movements', function (Blueprint $table) {
            $table->integer('passenger_count')->default(0)->after('actual_end');

            // Remove the new columns
            $table->dropColumn('booked_passenger_count');
            $table->dropColumn('actual_passenger_count');

            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
