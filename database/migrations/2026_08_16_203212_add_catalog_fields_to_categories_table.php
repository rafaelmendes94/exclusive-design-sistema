<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_menu')->default(true)->after('active');
            $table->text('description')->nullable()->after('name');
            $table->string('banner_desktop')->nullable()->after('description');
            $table->string('banner_mobile')->nullable()->after('banner_desktop');
            $table->string('banner_link')->nullable()->after('banner_mobile');
            $table->string('icon_image')->nullable()->after('banner_link');
            $table->string('seo_description')->nullable()->after('icon_image');
            $table->string('seo_keywords')->nullable()->after('seo_description');
            $table->string('seo_url')->nullable()->after('seo_keywords');
            $table->string('seo_title')->nullable()->after('seo_url');
            $table->boolean('update_items_price_table')->default(false)->after('seo_title');
            $table->foreignId('category_factor_table_id')->nullable()->after('update_items_price_table')->constrained('factor_tables')->nullOnDelete();
            $table->boolean('featured')->default(false)->after('category_factor_table_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_factor_table_id');
            $table->dropColumn([
                'show_in_menu',
                'description',
                'banner_desktop',
                'banner_mobile',
                'banner_link',
                'icon_image',
                'seo_description',
                'seo_keywords',
                'seo_url',
                'seo_title',
                'update_items_price_table',
                'featured',
            ]);
        });
    }
};
