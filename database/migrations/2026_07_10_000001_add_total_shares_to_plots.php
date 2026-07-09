<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_plots', function (Blueprint $table) {
            // How many predefined shares (per share = per flat) this plot is
            // divided into for sale. Share price is set per booking, not here.
            $table->unsignedInteger('total_shares')->nullable()->after('other_cost');
        });
    }

    public function down(): void
    {
        Schema::table('p_plots', function (Blueprint $table) {
            $table->dropColumn('total_shares');
        });
    }
};
