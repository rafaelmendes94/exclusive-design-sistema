<?php

namespace App\Console\Commands;

use App\Models\ProductVariation;
use App\Support\VariationImageGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:audit-variation-images {--fix : Corrige imagens conflitantes usando imagem irmã segura ou limpando a imagem}')]
#[Description('Audita imagens de variações para evitar foto de cor diferente no orçamento')]
class AuditVariationImages extends Command
{
    public function handle(): int
    {
        $audited = 0;
        $conflicts = 0;
        $fixedWithSibling = 0;
        $cleared = 0;
        $samples = [];

        ProductVariation::query()
            ->with('product.variations')
            ->whereNotNull('image_url')
            ->where('image_url', '!=', '')
            ->chunkById(500, function ($variations) use (&$audited, &$conflicts, &$fixedWithSibling, &$cleared, &$samples) {
                foreach ($variations as $variation) {
                    $audited++;
                    if (!VariationImageGuard::conflicts($variation)) {
                        continue;
                    }

                    $conflicts++;
                    $replacement = $this->siblingImageFor($variation);

                    if ($this->option('fix')) {
                        $variation->update(['image_url' => $replacement]);
                        $replacement ? $fixedWithSibling++ : $cleared++;
                    }

                    if (count($samples) < 20) {
                        $samples[] = [
                            $variation->product?->supplier,
                            $variation->sku,
                            trim(collect([$variation->color, $variation->secondary_color])->filter()->implode(' / ')),
                            $replacement ? 'trocada por imagem irmã' : 'imagem removida',
                        ];
                    }
                }
            });

        $this->info("Variações auditadas: {$audited}");
        $this->info("Conflitos de cor/imagem: {$conflicts}");
        if ($this->option('fix')) {
            $this->info("Corrigidas com imagem irmã: {$fixedWithSibling}");
            $this->info("Imagem removida por falta de opção segura: {$cleared}");
        }

        foreach ($samples as $sample) {
            $this->line(implode(' | ', $sample));
        }

        return self::SUCCESS;
    }

    private function siblingImageFor(ProductVariation $variation): ?string
    {
        $expected = VariationImageGuard::signalColors($variation) ?: VariationImageGuard::expectedColors($variation);
        if ($expected === []) {
            return null;
        }

        return $variation->product?->variations
            ->filter(fn (ProductVariation $sibling) => $sibling->id !== $variation->id && filled($sibling->image_url))
            ->first(function (ProductVariation $sibling) use ($variation, $expected) {
                return !VariationImageGuard::conflicts($variation, $sibling->image_url)
                    && array_intersect($expected, VariationImageGuard::imageColors($sibling->image_url)) !== [];
            })
            ?->image_url;
    }
}
