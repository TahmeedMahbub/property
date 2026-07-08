<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_plots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            // Basic information
            $table->string('plot_code');
            $table->string('plot_name');
            $table->enum('status', [
                'prospect',
                'negotiation',
                'bayna_done',
                'registration_pending',
                'registration_complete',
                'development_ready',
            ])->default('prospect');

            // Location
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('area')->nullable();
            $table->text('address')->nullable();

            // Land records
            $table->string('mouza')->nullable();
            $table->string('jl_no')->nullable();
            $table->string('khatian_no')->nullable();
            $table->string('dag_no')->nullable();

            // Land details
            $table->decimal('land_size', 15, 4)->default(0);
            $table->enum('land_unit', ['katha', 'decimal', 'acre'])->default('katha');

            // Purchase information
            $table->decimal('purchase_price', 20, 2)->default(0);
            $table->decimal('price_per_katha', 20, 2)->nullable();
            $table->decimal('bayna_amount', 20, 2)->default(0);
            $table->decimal('registration_cost', 20, 2)->default(0);
            $table->decimal('mutation_cost', 20, 2)->default(0);
            $table->decimal('legal_cost', 20, 2)->default(0);
            $table->decimal('broker_cost', 20, 2)->default(0);
            $table->decimal('other_cost', 20, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'plot_code']);
        });

        Schema::create('p_plot_sellers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('plot_id')->constrained('p_plots')->cascadeOnDelete();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('nid')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();

            $table->index('plot_id');
        });

        Schema::create('p_plot_owners', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('plot_id')->constrained('p_plots')->cascadeOnDelete();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('nid')->nullable();
            $table->text('address')->nullable();
            $table->decimal('ownership_percentage', 8, 4)->default(0);

            $table->timestamps();

            $table->index('plot_id');
        });

        Schema::create('p_plot_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('plot_id')->constrained('p_plots')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();

            $table->enum('payment_type', [
                'bayna',
                'land',
                'registration',
                'legal',
                'mutation',
                'broker',
            ]);
            $table->decimal('amount', 20, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'mobile_banking', 'other'])->default('bank_transfer');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['plot_id', 'payment_date']);
            $table->index(['plot_id', 'payment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_plot_payments');
        Schema::dropIfExists('p_plot_owners');
        Schema::dropIfExists('p_plot_sellers');
        Schema::dropIfExists('p_plots');
    }
};
