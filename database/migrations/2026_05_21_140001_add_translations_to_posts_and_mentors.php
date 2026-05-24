<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bilingual content: primary columns hold Montenegrin (default),
     * *_en columns hold the English translation.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('excerpt_en', 300)->nullable()->after('excerpt');
            $table->longText('body_en')->nullable()->after('body');
        });

        Schema::table('mentors', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->text('bio_en')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'excerpt_en', 'body_en']);
        });
        Schema::table('mentors', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'bio_en']);
        });
    }
};
