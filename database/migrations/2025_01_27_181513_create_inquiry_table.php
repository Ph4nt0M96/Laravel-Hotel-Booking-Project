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
        Schema::create('inquiry', function (Blueprint $table) {
            $table->id('inquiry_id');
            $table->unsignedBigInteger('guest_id');
            $table->string('message');
            $table->timestamp('sentdate');
            $table->timestamps();
            $table->foreign('guest_id')->references('guest_id')->on('guest')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry');
    }
};
