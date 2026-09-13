<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('document_type', 100);
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->timestamps();

            $table->index('venue_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_documents');
    }
};
