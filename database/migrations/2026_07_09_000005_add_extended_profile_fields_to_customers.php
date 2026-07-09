<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_customers', function (Blueprint $table) {
            // Personal (additional)
            $table->string('religion')->nullable()->after('marital_status');
            $table->string('spouse_name')->nullable()->after('religion');

            // Financial
            $table->string('bank_name')->nullable()->after('nominee_nid_number');
            $table->string('bank_account_name')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_name');

            // Emergency contact
            $table->string('emergency_contact_name')->nullable()->after('bank_account_number');
            $table->string('emergency_contact_mobile')->nullable()->after('emergency_contact_name');

            // Joint owner
            $table->boolean('has_joint_owner')->default(false)->after('emergency_contact_mobile');
            $table->string('joint_owner_name')->nullable()->after('has_joint_owner');
            $table->string('joint_owner_mobile')->nullable()->after('joint_owner_name');
            $table->string('joint_owner_nid')->nullable()->after('joint_owner_mobile');
            $table->text('joint_owner_address')->nullable()->after('joint_owner_nid');
        });
    }

    public function down(): void
    {
        Schema::table('p_customers', function (Blueprint $table) {
            $table->dropColumn([
                'religion',
                'spouse_name',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'emergency_contact_name',
                'emergency_contact_mobile',
                'has_joint_owner',
                'joint_owner_name',
                'joint_owner_mobile',
                'joint_owner_nid',
                'joint_owner_address',
            ]);
        });
    }
};
