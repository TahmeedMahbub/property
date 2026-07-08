<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_document_folders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('p_document_folders')->cascadeOnDelete();
            $table->string('name');
            $table->string('path')->comment('Full materialized path for fast lookups');
            $table->foreignId('created_by')->nullable()->constrained('p_users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'parent_id']);
            $table->index(['company_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_document_folders');
    }
};
