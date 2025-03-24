<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::create('passenger_boardings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('movement_id')->constrained('bus_movements');
                $table->dateTime('estimated_boarding_time');
                $table->enum('status', ['scheduled', 'boarded', 'missed'])->default('scheduled');
                $table->foreignId('user_id')->constrained('users');
                $table->timestamps();
            });
        }

    public function down()
    {
        Schema::dropIfExists('passenger_boardings');
    }
};
