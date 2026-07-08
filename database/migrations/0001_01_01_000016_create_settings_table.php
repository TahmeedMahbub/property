<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('p_companies')->cascadeOnDelete();
            $table->string('group', 50);
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'group', 'key']);
            $table->index(['company_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p_settings');
    }
};
