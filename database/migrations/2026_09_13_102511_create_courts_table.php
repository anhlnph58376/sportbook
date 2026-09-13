<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->string('status', 30)->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->index('venue_id');
            $table->index('sport_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
