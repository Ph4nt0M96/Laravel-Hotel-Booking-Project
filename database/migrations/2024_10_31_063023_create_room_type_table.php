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
        Schema::create('room_type', function (Blueprint $table) {
            $table->id('room_type_id');
            $table->string('room_type');
            $table->decimal('base_price');
            $table->integer('max_occupancy');
            $table->string('amenities');
            $table->string('description');
            $table->string('image');
            $table->string('image2');
            $table->string('image3');
            $table->string('image4');
            $table->boolean('delete_status')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type');
    }
};
