<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('license_plate', 20)->unique();
            $table->integer('capacity');
            $table->enum('status', ['inactive', 'active','maintenance'])->default('active');
            $table->foreignId('driver_id')->constrained('drivers')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buses');
    }
};
