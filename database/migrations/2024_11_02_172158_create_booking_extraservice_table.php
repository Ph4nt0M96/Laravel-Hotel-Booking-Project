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
        Schema::create('booking_extraservice', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_id');
            $table->unsignedBigInteger('service_id');
            $table->integer('quantity');
            $table->timestamps();
            $table->foreign('detail_id')->references('detail_id')->on('booking_detail')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('extra_service')->onDelete('cascade');
            $table->primary(['detail_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_extraservice');
    }
};
