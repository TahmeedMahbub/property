<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('floor_number');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('total_units')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'floor_number']);
            $table->index('building_id');
            $table->index('project_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
