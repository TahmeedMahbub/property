<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A customer books one or more shares (flats) of a plot. Price per share
        // is set here (per booking). Extra fees (registration, other) and a
        // discount adjust the payable. Each payment received is cash-in (credit).
        Schema::create('p_plot_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained('p_plots')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('p_customers')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->string('booking_no');
            $table->unsignedInteger('shares_count')->default(1);
            $table->decimal('share_price', 20, 2)->default(0);
            $table->decimal('registration_fee', 20, 2)->default(0);
            $table->decimal('other_fee', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->text('other_info')->nullable();
            $table->date('booking_date')->nullable();
            $table->enum('status', ['booked', 'active', 'completed', 'cancelled'])->default('booked');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('plot_id');
            $table->index('customer_id');
        });

        // Manual installment schedule rows for a booking (due date + amount).
        Schema::create('p_plot_booking_installments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained('p_plot_bookings')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->unsignedInteger('installment_no')->default(1);
            $table->string('title')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount', 20, 2)->default(0);
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->index('booking_id');
        });

        // Actual cash received against a booking (optionally tied to an
        // installment). Every payment posts a CREDIT to the company cash ledger.
        Schema::create('p_plot_booking_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained('p_plot_bookings')->cascadeOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained('p_plot_booking_installments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->enum('payment_type', ['booking', 'installment', 'registration', 'other', 'full'])->default('installment');
            $table->decimal('amount', 20, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('booking_id');
            $table->index('installment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_plot_booking_payments');
        Schema::dropIfExists('p_plot_booking_installments');
        Schema::dropIfExists('p_plot_bookings');
    }
};
