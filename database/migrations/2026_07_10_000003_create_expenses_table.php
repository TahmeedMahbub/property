<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Generic, polymorphic expense ledger. Any module (company, project,
        // plot, booking, …) can attach an expense via the expensable_* morph so
        // a single Expense module can later list every expense in one place.
        // Each expense is cash leaving the company and posts a DEBIT to the
        // company Journal.
        Schema::create('p_expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            // Source module the expense belongs to (nullable = standalone/company).
            $table->nullableMorphs('expensable');

            $table->string('category')->default('general');
            $table->string('title')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->date('expense_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_expenses');
    }
};
