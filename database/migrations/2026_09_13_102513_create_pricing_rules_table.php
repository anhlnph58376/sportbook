<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price_per_hour', 12, 2);
            $table->string('day_type', 20)->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('court_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
