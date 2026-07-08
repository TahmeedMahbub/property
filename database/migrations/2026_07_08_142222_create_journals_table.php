<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('p_journals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Who performed the action');
            $table->enum('type', ['credit', 'debit'])->comment('credit=increase, debit=decrease');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->comment('Running balance after this entry');
            $table->string('category', 50)->nullable()->comment('investment, sale, purchase, expense, refund, adjustment, etc.');
            $table->string('reference_type')->nullable()->comment('Polymorphic: model class');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Polymorphic: model id');
            $table->string('remarks', 500)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('p_companies')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('p_users')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_journals');
    }
};
