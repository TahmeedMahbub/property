<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_shareholders', function (Blueprint $table) {
            // Source of truth for ownership.
            $table->decimal('shares_owned', 20, 6)->default(0)->after('share_amount');
            // Cached, derived value — never edited manually (maintained by ShareOwnershipService).
            $table->decimal('ownership_percentage', 9, 6)->default(0)->after('shares_owned');
            // Legacy manual field is no longer collected on input — make it optional.
            $table->decimal('share_percentage', 8, 4)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('p_shareholders', function (Blueprint $table) {
            $table->dropColumn(['shares_owned', 'ownership_percentage']);
            $table->decimal('share_percentage', 8, 4)->nullable(false)->default(null)->change();
        });
    }
};
