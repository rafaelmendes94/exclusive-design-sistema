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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('factor_table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier')->default('XBZ');
            $table->string('supplier_product_id')->nullable()->index();
            $table->string('base_sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->boolean('block_supplier_update')->default(false);
            $table->boolean('use_manual_price_table')->default(false);
            $table->decimal('cost_price', 12, 4)->default(0);
            $table->decimal('sale_price', 12, 4)->default(0);
            $table->integer('minimum_quantity')->default(1);
            $table->decimal('weight', 12, 3)->nullable();
            $table->decimal('height', 12, 3)->nullable();
            $table->decimal('width', 12, 3)->nullable();
            $table->decimal('depth', 12, 3)->nullable();
            $table->string('ncm')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('supplier_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
