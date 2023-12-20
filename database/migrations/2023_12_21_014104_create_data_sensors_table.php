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
        Schema::create('data_sensors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sensor_id');
            $table->foreign('sensor_id')->references('id')->on('sensors')->onDelete('cascade')->onUpdate('cascade');
            $table->string('data_type_id');
            $table->string('si_value')->nullable();
            $table->string('si_unit')->nullable();
            $table->string('us_value')->nullable();
            $table->string('us_unit')->nullable();
            $table->string('scaled_value')->nullable();
            $table->string('scaled_unit')->nullable();
            $table->string('sinyal')->nullable();
            $table->string('timestamp');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sensors');
    }
};
