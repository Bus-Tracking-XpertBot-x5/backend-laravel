<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->string('name',50);
                $table->foreignId('user_id')->constrained('users');
                $table->string('license_number',20)->unique();
                $table->timestamps();
            });
        }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
