<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('role');
            $table->boolean('default_seller')->default(false)->after('active');
            $table->boolean('can_view_supplier')->default(false)->after('default_seller');
            $table->boolean('can_view_cost')->default(false)->after('can_view_supplier');
            $table->boolean('can_view_factor')->default(false)->after('can_view_cost');
            $table->string('phone')->nullable()->after('password');
            $table->string('mobile')->nullable()->after('phone');
            $table->string('rg')->nullable()->after('mobile');
            $table->string('rg_issuer')->nullable()->after('rg');
            $table->string('cpf')->nullable()->after('rg_issuer');
            $table->string('company')->nullable()->after('cpf');
            $table->string('trade_name')->nullable()->after('company');
            $table->string('cnpj')->nullable()->after('trade_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'active',
                'default_seller',
                'can_view_supplier',
                'can_view_cost',
                'can_view_factor',
                'phone',
                'mobile',
                'rg',
                'rg_issuer',
                'cpf',
                'company',
                'trade_name',
                'cnpj',
            ]);
        });
    }
};
