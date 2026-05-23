<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Richer youth profile so NGOs can evaluate applicants.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('headline')->nullable()->after('bio');
            $table->text('about')->nullable()->after('headline');
            $table->text('education')->nullable()->after('about');
            $table->text('work_experience')->nullable()->after('education');
            $table->text('skills')->nullable()->after('work_experience');
            $table->string('linkedin')->nullable()->after('skills');
            $table->string('phone')->nullable()->after('linkedin');
            // male | female | other | undisclosed
            $table->string('gender')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'headline',
                'about',
                'education',
                'work_experience',
                'skills',
                'linkedin',
                'phone',
                'gender',
            ]);
        });
    }
};
