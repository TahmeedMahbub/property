<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_investors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('investment_amount', 15, 2);
            $table->decimal('investment_percentage', 8, 4)->nullable();
            $table->enum('investment_type', ['equity', 'debt', 'convertible'])->default('equity');
            $table->date('invested_at')->nullable();
            $table->decimal('expected_return', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'withdrawn'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_investors');
    }
};
