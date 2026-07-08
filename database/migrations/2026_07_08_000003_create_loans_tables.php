<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('p_projects')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->enum('lender_type', ['bank', 'shareholder', 'director', 'third_party']);
            $table->string('lender_name');
            $table->string('reference_no')->nullable(); // bank sanction / loan account no

            $table->decimal('principal_amount', 20, 2);
            $table->decimal('interest_rate', 8, 4)->default(0); // annual %
            $table->enum('interest_type', ['flat', 'reducing'])->default('flat');
            $table->decimal('emi_amount', 20, 2)->nullable(); // scheduled installment (informational)

            $table->date('start_date');
            $table->date('end_date')->nullable(); // maturity date
            $table->enum('repayment_frequency', ['monthly', 'quarterly', 'yearly'])->default('monthly');

            $table->text('collateral')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'closed', 'defaulted'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'lender_type']);
            $table->index(['project_id', 'status']);
            $table->index('end_date');
        });

        Schema::create('p_loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('loan_id')->constrained('p_loans')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->date('payment_date');
            $table->decimal('principal_paid', 20, 2)->default(0);
            $table->decimal('interest_paid', 20, 2)->default(0);
            $table->decimal('penalty', 20, 2)->default(0);

            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'mobile_banking', 'other'])->default('bank_transfer');
            $table->string('reference_no')->nullable(); // cheque / transaction no
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['loan_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_loan_repayments');
        Schema::dropIfExists('p_loans');
    }
};
