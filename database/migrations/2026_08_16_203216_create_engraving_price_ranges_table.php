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
        Schema::create('engraving_price_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engraving_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_from');
            $table->integer('quantity_to')->nullable();
            $table->decimal('price', 12, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engraving_price_ranges');
    }
};
