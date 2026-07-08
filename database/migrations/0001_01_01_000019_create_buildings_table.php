<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_buildings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('p_companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('p_projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('total_floors')->default(0);
            $table->unsignedSmallInteger('total_units')->default(0);
            $table->string('address')->nullable();
            $table->enum('status', ['planning', 'under_construction', 'completed', 'handed_over'])->default('planning');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'slug']);
            $table->index('project_id');
            $table->index('company_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_buildings');
    }
};
