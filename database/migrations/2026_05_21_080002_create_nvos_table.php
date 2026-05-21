<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Organization profile, one-to-one with a user that has the "nvo" role.
     */
    public function up(): void
    {
        Schema::create('nvos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('organization_name');
            $table->string('pib')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->text('intro_message')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nvos');
    }
};
