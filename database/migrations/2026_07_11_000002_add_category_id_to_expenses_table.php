<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_expenses', function (Blueprint $table) {
            // Managed category. Nullable so existing rows (which only have the
            // free-text `category` slug) keep working; new rows link here.
            $table->foreignId('category_id')
                ->nullable()
                ->after('created_by')
                ->constrained('p_expense_categories')
                ->nullOnDelete();

            $table->index(['company_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('p_expenses', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'category_id']);
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
