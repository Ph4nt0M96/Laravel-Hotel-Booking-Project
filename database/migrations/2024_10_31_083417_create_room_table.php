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
        Schema::create('room', function (Blueprint $table) {
            $table->id('room_id');
            $table->unsignedBigInteger('room_type_id');
            $table->integer('floor');
            $table->unsignedBigInteger('view_id');
            $table->boolean('is_held');
            $table->boolean('is_available');
            $table->foreign('room_type_id')->references('room_type_id')->on('room_type')->onDelete('cascade');
            $table->foreign('view_id')->references('view_id')->on('view')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room');
    }
};
