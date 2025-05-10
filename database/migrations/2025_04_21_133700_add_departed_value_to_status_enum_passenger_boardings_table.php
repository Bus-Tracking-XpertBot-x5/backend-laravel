<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('passenger_boardings', function (Blueprint $table) {
            DB::statement("ALTER TABLE passenger_boardings MODIFY COLUMN status ENUM('scheduled', 'boarded', 'missed', 'departed') DEFAULT 'scheduled'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_boardings', function (Blueprint $table) {
            DB::statement("ALTER TABLE passenger_boardings MODIFY COLUMN status ENUM('scheduled', 'boarded', 'missed') DEFAULT 'scheduled'");
        });
    }
};
