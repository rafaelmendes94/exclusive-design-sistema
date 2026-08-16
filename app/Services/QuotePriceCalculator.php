<?php

namespace App\Services;

use App\Models\FactorRange;
use App\Models\FactorTable;
use App\Models\Product;
use App\Models\ProductVariation;

class QuotePriceCalculator
{
    public function calculate(
        Product $product,
        ?ProductVariation $variation,
        int $quantity,
        ?int $factorTableId,
        float $manualCost = 0,
        float $freight = 0,
        float $extraPercent = 0,
        float $taxPercent = 0,
        float $engravingCost = 0
    ): array {
        $quantity = max(0, $quantity);

        if ($quantity === 0) {
            return $this->result(0, 0, 'Quantidade zerada.');
        }

        $baseCost = $manualCost > 0
            ? $manualCost
            : (float) ($variation?->sale_price ?: $product->sale_price ?: $variation?->cost_price ?: $product->cost_price);

        $manualPrice = $this->manualPriceFor($product, $quantity);

        if ($product->use_manual_price_table && $manualPrice !== null) {
            $unit = $manualPrice;
            $memory = "Tabela manual aplicada para {$quantity} un.: R$ ".number_format($unit, 2, ',', '.');
        } else {
            $coefficient = $this->coefficientFor($factorTableId ?: $product->factor_table_id, $quantity);
            $factorPercent = $coefficient > 0 ? 100 / $coefficient : 100;
            $unit = $baseCost * ($factorPercent / 100);
            $memory = 'Base R$ '.number_format($baseCost, 2, ',', '.').' x fator '.number_format($factorPercent, 4, ',', '.').'%';
        }

        if ($engravingCost > 0) {
            $unit += $engravingCost;
            $memory .= ' + gravação R$ '.number_format($engravingCost, 2, ',', '.');
        }

        if ($freight > 0) {
            $unit += ($freight / $quantity);
            $memory .= ' + frete rateado R$ '.number_format($freight / $quantity, 2, ',', '.');
        }

        if ($extraPercent > 0) {
            $unit *= (1 + ($extraPercent / 100));
            $memory .= ' + BV/outros '.number_format($extraPercent, 2, ',', '.').'%';
        }

        if ($taxPercent > 0) {
            $unit *= (1 + ($taxPercent / 100));
            $memory .= ' + imposto '.number_format($taxPercent, 2, ',', '.').'%';
        }

        $unit = round($unit, 2);

        return $this->result($unit, $unit * $quantity, $memory);
    }

    private function coefficientFor(?int $factorTableId, int $quantity): float
    {
        if (!$factorTableId) {
            $factorTableId = FactorTable::defaultMostExpensive()?->id;
        }

        if (!$factorTableId) {
            return 1;
        }

        $range = FactorRange::query()
            ->where('factor_table_id', $factorTableId)
            ->where('quantity_from', '<=', $quantity)
            ->where(fn ($query) => $query->whereNull('quantity_to')->orWhere('quantity_to', '>=', $quantity))
            ->orderByDesc('quantity_from')
            ->first();

        return $range ? max((float) $range->coefficient, 0.0001) : 1;
    }

    private function manualPriceFor(Product $product, int $quantity): ?float
    {
        if (!$product->use_manual_price_table) {
            return null;
        }

        $range = $product->manualPriceRanges()
            ->where('quantity_from', '<=', $quantity)
            ->where(fn ($query) => $query->whereNull('quantity_to')->orWhere('quantity_to', '>=', $quantity))
            ->orderByDesc('quantity_from')
            ->first();

        return $range ? (float) $range->price : null;
    }

    private function result(float $unit, float $subtotal, string $memory): array
    {
        return [
            'unit_price' => round($unit, 2),
            'subtotal' => round($subtotal, 2),
            'memory' => $memory,
        ];
    }
}
