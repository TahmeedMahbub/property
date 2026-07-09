<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_customers', function (Blueprint $table) {
            // Optional link to a project (attach at creation time).
            $table->foreignId('project_id')->nullable()->after('user_id')
                ->constrained('p_projects')->nullOnDelete();

            // ─── Personal ────────────────────────────────────────
            $table->string('full_name_en')->nullable()->after('name');
            $table->string('full_name_bn')->nullable()->after('full_name_en');
            $table->string('father_name')->nullable()->after('full_name_bn');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->date('date_of_birth')->nullable()->after('mother_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('profession')->nullable()->after('marital_status');
            $table->string('nationality')->nullable()->after('profession');

            // ─── Contact ─────────────────────────────────────────
            $table->string('alternative_mobile', 20)->nullable()->after('phone');
            $table->text('present_address')->nullable()->after('address');
            $table->text('permanent_address')->nullable()->after('present_address');

            // ─── Identity ────────────────────────────────────────
            $table->string('nid_number')->nullable()->after('permanent_address');
            $table->string('tin_number')->nullable()->after('nid_number');
            $table->string('passport_number')->nullable()->after('tin_number');

            // ─── Nominee ─────────────────────────────────────────
            $table->string('nominee_name')->nullable()->after('passport_number');
            $table->string('nominee_relationship')->nullable()->after('nominee_name');
            $table->string('nominee_mobile', 20)->nullable()->after('nominee_relationship');
            $table->text('nominee_address')->nullable()->after('nominee_mobile');

            $table->index(['company_id', 'project_id']);
        });

        // Migrate the status column to the sales lifecycle (lead → customer → verified).
        // Widen the enum, remap existing rows, then narrow to the final set.
        DB::statement("ALTER TABLE p_customers MODIFY COLUMN status ENUM('active','inactive','blacklisted','lead','customer','verified') NOT NULL DEFAULT 'customer'");
        DB::statement("UPDATE p_customers SET status = 'customer' WHERE status = 'active'");
        DB::statement("UPDATE p_customers SET status = 'lead' WHERE status IN ('inactive', 'blacklisted')");
        DB::statement("ALTER TABLE p_customers MODIFY COLUMN status ENUM('lead','customer','verified') NOT NULL DEFAULT 'customer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE p_customers MODIFY COLUMN status ENUM('lead','customer','verified','active','inactive','blacklisted') NOT NULL DEFAULT 'customer'");
        DB::statement("UPDATE p_customers SET status = 'active' WHERE status IN ('customer', 'verified')");
        DB::statement("UPDATE p_customers SET status = 'inactive' WHERE status = 'lead'");
        DB::statement("ALTER TABLE p_customers MODIFY COLUMN status ENUM('active','inactive','blacklisted') NOT NULL DEFAULT 'active'");

        Schema::table('p_customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'project_id']);
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'full_name_en', 'full_name_bn', 'father_name', 'mother_name',
                'date_of_birth', 'gender', 'marital_status', 'profession', 'nationality',
                'alternative_mobile', 'present_address', 'permanent_address',
                'nid_number', 'tin_number', 'passport_number',
                'nominee_name', 'nominee_relationship', 'nominee_mobile', 'nominee_address',
            ]);
        });
    }
};
