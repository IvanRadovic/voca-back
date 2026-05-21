<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Opportunity ("call") published by an NVO.
     */
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            // Owner NVO (a user with role = nvo).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('subtitle', 150)->nullable();
            $table->longText('description');
            $table->string('image')->nullable();
            // seminar | conference | education | camp | competition | course | workshop | mentorship | volunteering
            $table->string('type');
            $table->dateTime('application_deadline');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_online')->default(false);
            $table->unsignedInteger('max_participants')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            // Array of prerequisite keys, e.g. ["english","age","skills"].
            $table->json('prerequisites')->nullable();
            // active | finished | cancelled
            $table->string('status')->default('active');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
