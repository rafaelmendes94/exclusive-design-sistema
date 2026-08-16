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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('splash_id')->nullable()->after('factor_table_id')->constrained('splashes')->nullOnDelete();
            $table->string('availability')->nullable()->after('supplier_product_id');
            $table->string('additional_info')->nullable()->after('sale_price');
            $table->decimal('thickness', 12, 3)->nullable()->after('depth');
            $table->decimal('length', 12, 3)->nullable()->after('thickness');
            $table->decimal('circumference', 12, 3)->nullable()->after('length');
            $table->decimal('diameter', 12, 3)->nullable()->after('circumference');
            $table->string('energy')->nullable()->after('weight');
            $table->string('warranty')->nullable()->after('energy');
            $table->string('engraving_measure')->nullable()->after('warranty');
            $table->string('total_size')->nullable()->after('engraving_measure');
            $table->string('youtube_url')->nullable()->after('image_url');
            $table->boolean('youtube_active')->default(false)->after('youtube_url');
            $table->text('technical_information')->nullable()->after('youtube_active');
            $table->text('engraving_description')->nullable()->after('technical_information');
            $table->string('refill_description')->nullable()->after('engraving_description');
            $table->string('seo_keywords')->nullable()->after('refill_description');
            $table->string('seo_description')->nullable()->after('seo_keywords');
            $table->string('seo_url')->nullable()->after('seo_description');
            $table->string('seo_title')->nullable()->after('seo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('splash_id');
            $table->dropColumn([
                'availability',
                'additional_info',
                'thickness',
                'length',
                'circumference',
                'diameter',
                'energy',
                'warranty',
                'engraving_measure',
                'total_size',
                'youtube_url',
                'youtube_active',
                'technical_information',
                'engraving_description',
                'refill_description',
                'seo_keywords',
                'seo_description',
                'seo_url',
                'seo_title',
            ]);
        });
    }
};
