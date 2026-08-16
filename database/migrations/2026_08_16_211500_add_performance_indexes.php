<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'products_status_created_at_index');
            $table->index(['category_id', 'status'], 'products_category_status_index');
            $table->index('name', 'products_name_index');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->index(['product_id', 'status'], 'product_variations_product_status_index');
            $table->index(['status', 'stock'], 'product_variations_status_stock_index');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['quote_status_id', 'created_at'], 'quotes_status_created_at_index');
            $table->index('customer_id', 'quotes_customer_id_index');
            $table->index('created_at', 'quotes_created_at_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index(['active', 'created_at'], 'customers_active_created_at_index');
            $table->index('company', 'customers_company_index');
            $table->index('name', 'customers_name_index');
            $table->index('email', 'customers_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_created_at_index');
            $table->dropIndex('products_category_status_index');
            $table->dropIndex('products_name_index');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropIndex('product_variations_product_status_index');
            $table->dropIndex('product_variations_status_stock_index');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('quotes_status_created_at_index');
            $table->dropIndex('quotes_customer_id_index');
            $table->dropIndex('quotes_created_at_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_active_created_at_index');
            $table->dropIndex('customers_company_index');
            $table->dropIndex('customers_name_index');
            $table->dropIndex('customers_email_index');
        });
    }
};
