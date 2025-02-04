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
        Schema::create('booking', function (Blueprint $table) {
            $table->id('booking_id');
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamp('booking_date');
            $table->date('confirm_until');
            $table->string('booking_status');
            $table->timestamps();
            $table->foreign('guest_id')->references('guest_id')->on('guest')->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('admin')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
