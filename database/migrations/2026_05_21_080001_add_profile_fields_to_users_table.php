<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add profile/role fields used by both youth users and NVO accounts.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // youth | nvo | admin
            $table->string('role')->default('youth')->after('email');
            $table->date('date_of_birth')->nullable()->after('role');
            $table->string('city')->nullable()->after('date_of_birth');
            // high_school | bachelor | undergraduate | master
            $table->string('education_level')->nullable()->after('city');
            $table->string('avatar')->nullable()->after('education_level');
            $table->text('bio')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'date_of_birth',
                'city',
                'education_level',
                'avatar',
                'bio',
            ]);
        });
    }
};
