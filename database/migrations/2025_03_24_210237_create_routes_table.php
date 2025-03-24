<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name', 100);
            $table->json('start_location');
            $table->json('end_location');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('routes');
    }
};
