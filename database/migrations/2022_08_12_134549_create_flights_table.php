<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('departure_city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('destination_city_id')->constrained('cities')->cascadeOnDelete();
            $table->timestamp('departure_at');
            $table->timestamp('arrival_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
