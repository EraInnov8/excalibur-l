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
        Schema::create('skill_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('energy');
            $table->string('orientation');
            $table->string('structure');
            $table->string('drive');
            $table->string('reaction');
            $table->string('skill_1');
            $table->string('skill_2');
            $table->string('skill_3');
            $table->string('skill_4');
            $table->string('skill_5');
            $table->timestamps();
            $table->unique(['energy', 'orientation', 'structure', 'drive', 'reaction']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_mappings');
    }
};
