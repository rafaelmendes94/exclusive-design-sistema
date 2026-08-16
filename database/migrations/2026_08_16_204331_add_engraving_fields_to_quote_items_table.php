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
        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreignId('engraving_id')->nullable()->after('factor_table_id')->constrained('engravings')->nullOnDelete();
            $table->decimal('engraving_cost', 12, 4)->default(0)->after('engraving');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('engraving_id');
            $table->dropColumn('engraving_cost');
        });
    }
};
