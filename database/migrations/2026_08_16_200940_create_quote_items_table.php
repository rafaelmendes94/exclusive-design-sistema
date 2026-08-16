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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('factor_table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('quantity_2')->default(0);
            $table->integer('quantity_3')->default(0);
            $table->decimal('cost_price', 12, 4)->default(0);
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->decimal('unit_price_2', 12, 4)->default(0);
            $table->decimal('unit_price_3', 12, 4)->default(0);
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('subtotal_2', 12, 4)->default(0);
            $table->decimal('subtotal_3', 12, 4)->default(0);
            $table->decimal('freight', 12, 4)->default(0);
            $table->decimal('extra_percent', 8, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->string('engraving')->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
