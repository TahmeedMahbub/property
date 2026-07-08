<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Immutable cap-table ledger — the source of truth for all share movements.
        Schema::create('p_share_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('shareholder_id')->comment('The holder whose balance changes');
            $table->unsignedBigInteger('related_shareholder_id')->nullable()->comment('Counterparty for transfers');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Actor who recorded the transaction');
            $table->enum('type', ['issue', 'transfer', 'buyback', 'cancellation']);
            $table->decimal('investment_amount', 20, 2)->nullable()->comment('Cash in (issue) — null for transfer/cancellation');
            $table->decimal('share_price', 20, 6)->comment('Price per share at the time of this transaction');
            $table->decimal('shares_issued', 20, 6)->comment('Signed share delta applied to the holder (+in / -out)');
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('p_companies')->cascadeOnDelete();
            $table->foreign('shareholder_id')->references('id')->on('p_shareholders')->cascadeOnDelete();
            $table->foreign('related_shareholder_id')->references('id')->on('p_shareholders')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('p_users')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['shareholder_id', 'created_at']);
        });

        // Cached, per-company cap-table aggregates.
        Schema::create('p_company_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id')->unique();
            $table->decimal('total_shares', 20, 6)->default(0);
            $table->decimal('current_share_price', 20, 6)->default(0);
            $table->decimal('current_valuation', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('p_companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_share_transactions');
        Schema::dropIfExists('p_company_metrics');
    }
};
