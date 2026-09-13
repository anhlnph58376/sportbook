<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address', 500);
            $table->string('province', 100);
            $table->string('district', 100);
            $table->string('ward', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->time('opening_time');
            $table->time('closing_time');
            $table->string('status', 20)->default('draft');
            $table->string('verification_status', 30)->default('pending_review');
            $table->text('rejection_reason')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('verification_status');
            $table->index(['province', 'district']);
            $table->index(['latitude', 'longitude']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
