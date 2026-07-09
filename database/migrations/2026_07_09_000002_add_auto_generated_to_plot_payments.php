<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_plot_payments', function (Blueprint $table) {
            $table->boolean('auto_generated')->default(false)->after('created_by');
        });

        // Add an "other" payment type so miscellaneous plot costs can be marked paid.
        DB::statement("ALTER TABLE p_plot_payments MODIFY COLUMN payment_type ENUM('bayna','land','registration','legal','mutation','broker','other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE p_plot_payments MODIFY COLUMN payment_type ENUM('bayna','land','registration','legal','mutation','broker') NOT NULL");

        Schema::table('p_plot_payments', function (Blueprint $table) {
            $table->dropColumn('auto_generated');
        });
    }
};
