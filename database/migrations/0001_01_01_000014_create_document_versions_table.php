<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('p_documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->text('changes_summary')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('p_users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_document_versions');
    }
};
