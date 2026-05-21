<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wishlist ("save for later").
     */
    public function up(): void
    {
        Schema::create('saved_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['call_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_calls');
    }
};
