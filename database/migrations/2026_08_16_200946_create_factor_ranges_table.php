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
        Schema::create('factor_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factor_table_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_from');
            $table->integer('quantity_to')->nullable();
            $table->decimal('coefficient', 12, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factor_ranges');
    }
};
