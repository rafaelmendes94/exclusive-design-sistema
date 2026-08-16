<?php

namespace App\Support;

use App\Models\ProductVariation;
use Illuminate\Support\Str;

class VariationImageGuard
{
    private const COLORS = [
        'PRETO' => ['PRETO', 'PRETA', 'BLACK', 'PRE'],
        'BRANCO' => ['BRANCO', 'BRANCA', 'WHITE', 'BCO', 'BRA'],
        'AZUL' => ['AZUL', 'BLUE', 'AZU', 'AZC', 'AZE'],
        'VERMELHO' => ['VERMELHO', 'VERMELHA', 'RED', 'VM', 'VER'],
        'VERDE' => ['VERDE', 'GREEN', 'VD', 'VDC', 'VDE', 'VLM'],
        'AMARELO' => ['AMARELO', 'AMARELA', 'YELLOW', 'AMA'],
        'LARANJA' => ['LARANJA', 'ORANGE', 'LAR'],
        'ROSA' => ['ROSA', 'PINK', 'ROS'],
        'ROXO' => ['ROXO', 'ROXA', 'PURPLE', 'ROX'],
        'CINZA' => ['CINZA', 'GREY', 'GRAY', 'CIN', 'CZC', 'CZE'],
        'PRATA' => ['PRATA', 'SILVER', 'PRA'],
        'DOURADO' => ['DOURADO', 'GOLD', 'DOU'],
        'MARROM' => ['MARROM', 'BROWN', 'MAR'],
        'TRANSPARENTE' => ['TRANSPARENTE', 'TRANS', 'TRA'],
    ];

    private const NEUTRAL = ['BRANCO', 'PRATA', 'INOX', 'TRANSPARENTE', 'SEM COR DEFINIDA'];

    public static function safeImage(ProductVariation $variation): ?string
    {
        if (filled($variation->image_url)) {
            return self::conflicts($variation) ? null : $variation->image_url;
        }

        if (blank($variation->color) && blank($variation->secondary_color)) {
            return $variation->product?->image_url;
        }

        return null;
    }

    public static function expectedColors(ProductVariation $variation): array
    {
        $text = collect([$variation->sku, $variation->color, $variation->secondary_color])
            ->filter()
            ->implode(' ');

        return self::colorsIn($text);
    }

    public static function imageColors(?string $imageUrl): array
    {
        return self::colorsIn((string) $imageUrl);
    }

    public static function conflicts(ProductVariation $variation, ?string $imageUrl = null): bool
    {
        $expected = self::withoutNeutral(self::expectedColors($variation));
        $image = self::imageColors($imageUrl ?? $variation->image_url);

        return $expected !== [] && $image !== [] && array_intersect($expected, $image) === [];
    }

    public static function signalColors(ProductVariation $variation): array
    {
        return self::withoutNeutral(self::expectedColors($variation));
    }

    public static function colorsIn(string $text): array
    {
        $text = urldecode($text);
        $normalized = Str::of($text)->ascii()->upper()->replaceMatches('/[^A-Z0-9@\/_-]+/', ' ')->toString();
        $tokens = preg_split('/[^A-Z0-9@]+/', $normalized) ?: [];
        $tokens = array_flip(array_filter($tokens));
        $found = [];

        foreach (self::COLORS as $color => $patterns) {
            foreach ($patterns as $pattern) {
                if (isset($tokens[$pattern]) || str_contains($normalized, '-' . $pattern) || str_contains($normalized, '_' . $pattern) || str_contains($normalized, '/' . $pattern)) {
                    $found[] = $color;
                    break;
                }
            }
        }

        return array_values(array_unique($found));
    }

    private static function withoutNeutral(array $colors): array
    {
        return array_values(array_diff($colors, self::NEUTRAL));
    }
}
