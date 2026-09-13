<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment');
            $table->boolean('is_visible')->default(true);
            $table->unsignedTinyInteger('reported_count')->default(0);
            $table->timestamps();

            $table->index('venue_id');
            $table->index('user_id');
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
