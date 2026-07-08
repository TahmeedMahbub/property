<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group', 50);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('p_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('p_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('p_permissions')->cascadeOnDelete();

            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_role_permissions');
        Schema::dropIfExists('p_permissions');
    }
};
