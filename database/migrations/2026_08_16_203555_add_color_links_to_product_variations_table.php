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
        Schema::table('product_variations', function (Blueprint $table) {
            $table->foreignId('color_id')->nullable()->after('sku')->constrained('colors')->nullOnDelete();
            $table->foreignId('secondary_color_id')->nullable()->after('color_id')->constrained('colors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('secondary_color_id');
            $table->dropConstrainedForeignId('color_id');
        });
    }
};
