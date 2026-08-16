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
        Schema::create('supplier_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('supplier')->default('XBZ');
            $table->integer('http_status')->nullable();
            $table->integer('items_received')->default(0);
            $table->integer('products_upserted')->default(0);
            $table->integer('variations_upserted')->default(0);
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_logs');
    }
};
