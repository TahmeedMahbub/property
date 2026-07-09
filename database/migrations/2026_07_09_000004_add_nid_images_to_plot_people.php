<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['p_plot_sellers', 'p_plot_owners'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('nid_front')->nullable()->after('nid');
                $table->string('nid_back')->nullable()->after('nid_front');
                $table->string('photo')->nullable()->after('nid_back');
            });
        }
    }

    public function down(): void
    {
        foreach (['p_plot_sellers', 'p_plot_owners'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['nid_front', 'nid_back', 'photo']);
            });
        }
    }
};
