<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_customers', function (Blueprint $table) {
            // Additional bilingual / identity fields introduced by the
            // customer self-service profile form.
            $table->string('father_name_bn')->nullable()->after('father_name');
            $table->string('mother_name_bn')->nullable()->after('mother_name');
            $table->string('driving_license_number')->nullable()->after('passport_number');
            $table->string('nominee_nid_number')->nullable()->after('nominee_address');

            // Secure public profile-completion link.
            $table->string('profile_token', 80)->nullable()->unique()->after('status');
            $table->timestamp('profile_link_generated_at')->nullable()->after('profile_token');
            $table->timestamp('profile_link_expires_at')->nullable()->after('profile_link_generated_at');

            // Profile completion / verification lifecycle.
            $table->unsignedTinyInteger('profile_completion_percentage')->default(0)->after('profile_link_expires_at');
            $table->timestamp('profile_completed_at')->nullable()->after('profile_completion_percentage');
            $table->timestamp('profile_verified_at')->nullable()->after('profile_completed_at');
            $table->boolean('profile_locked')->default(false)->after('profile_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('p_customers', function (Blueprint $table) {
            $table->dropColumn([
                'father_name_bn',
                'mother_name_bn',
                'driving_license_number',
                'nominee_nid_number',
                'profile_token',
                'profile_link_generated_at',
                'profile_link_expires_at',
                'profile_completion_percentage',
                'profile_completed_at',
                'profile_verified_at',
                'profile_locked',
            ]);
        });
    }
};
