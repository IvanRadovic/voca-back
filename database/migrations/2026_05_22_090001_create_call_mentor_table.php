<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links mentors to the opportunities they host / have hosted, so a mentor
     * profile can show their history and upcoming sessions.
     */
    public function up(): void
    {
        Schema::create('call_mentor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable(); // e.g. speaker, host, mentor
            $table->timestamps();

            $table->unique(['call_id', 'mentor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_mentor');
    }
};
