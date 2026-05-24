<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Certificates of participation, issued when an application is completed.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // public verification code
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->unique(['user_id', 'call_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
