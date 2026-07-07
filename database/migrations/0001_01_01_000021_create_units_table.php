<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_number');
            $table->decimal('size', 10, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('facing', 50)->nullable();
            $table->enum('status', ['available', 'reserved', 'booked', 'sold', 'handovered'])->default('available');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'unit_number']);
            $table->index('project_id');
            $table->index('building_id');
            $table->index('floor_id');
            $table->index('status');
            $table->index('unit_type_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
