<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

    public static function defaults(): array
    {
        return [
            'site_name' => 'Exclusive Design',
            'meta_description' => 'Brindes corporativos, produtos promocionais e soluções personalizadas.',
            'logo' => '/logo-exclusive.png',
            'logo_white' => '/logo-exclusive-white.png',
            'primary_color' => '#8f6332',
            'secondary_color' => '#c8a25a',
            'dark_color' => '#050505',
            'light_color' => '#f6f2ea',
            'company_name' => 'EXCLUSIVEDESIGN LTDA',
            'cnpj' => '66.163.388/0001-50',
            'phone' => '(11) 52861010',
            'whatsapp' => '551152861010',
            'email' => 'contato@exclusivedesign.com.br',
            'address_line_1' => 'R Manoel Alves Garcia, 130',
            'address_line_2' => 'Galpão B10',
            'district_city_state' => 'Jardim São Luiz | Jandira - SP',
            'zip' => '06618-010',
            'hero_badge' => 'COLECAO CORPORATIVA',
            'hero_title' => 'Garrafas, copos e kits que acompanham a rotina',
            'hero_text' => 'Selecao de itens para acoes promocionais, onboardings e campanhas com alto valor percebido.',
            'hero_image' => 'https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=1600&q=80',
            'terms' => "1. Esta proposta tem validade de 15 dias a partir da data de emissão.\n2. Prazo de entrega de 20 dias úteis após aprovação do pedido e da arte.\n3. Preços sujeitos a alteração sem aviso prévio após o vencimento desta proposta.\n4. Frete e impostos conforme forma de pagamento acordada.\n5. Imagens dos produtos são meramente ilustrativas.",
        ];
    }

    public static function mapped(): array
    {
        $settings = static::query()->pluck('value', 'key')->all();

        return array_merge(static::defaults(), array_filter($settings, fn ($value) => $value !== null && $value !== ''));
    }

    public static function value(string $key): ?string
    {
        $settings = static::mapped();

        return $settings[$key] ?? null;
    }
}
