<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_sport', function (Blueprint $table) {
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->primary(['venue_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_sport');
    }
};
