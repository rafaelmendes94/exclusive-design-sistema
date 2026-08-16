<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Color;
use App\Models\ColorGroup;
use App\Models\FactorTable;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\SupplierSyncLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:import-csv-products {file : Caminho do CSV exportado} {--limit= : Limita linhas para testes}')]
#[Description('Importa produtos do CSV e complementa imagens sem sobrepor produtos existentes')]
class ImportCsvProducts extends Command
{
    private array $headers = [];

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (!is_readable($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return self::FAILURE;
        }

        $log = SupplierSyncLog::create([
            'supplier' => 'CSV_CLIC',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $parents = [];
            $childrenCount = [];
            $limit = (int) $this->option('limit');
            $itemsReceived = 0;

            $this->foreachRow($file, function (array $row) use (&$parents, &$childrenCount, &$itemsReceived) {
                $itemsReceived++;
                if (!$this->isVariation($row)) {
                    $sku = $this->value($row, 'sku');
                    if ($sku !== '') {
                        $parents[$sku] = $row;
                    }
                } else {
                    $baseSku = $this->baseSku($row);
                    if ($baseSku !== '') {
                        $childrenCount[$baseSku] = ($childrenCount[$baseSku] ?? 0) + 1;
                    }
                }
            }, $limit);

            $productsCreated = 0;
            $variationsCreated = 0;
            $productsCompleted = 0;
            $variationsCompleted = 0;
            $xbzProductsWithImageFromCsv = 0;
            $xbzVariationsWithImageFromCsv = 0;

            $this->foreachRow($file, function (array $row) use (
                &$parents,
                &$childrenCount,
                &$productsCreated,
                &$variationsCreated,
                &$productsCompleted,
                &$variationsCompleted,
                &$xbzProductsWithImageFromCsv,
                &$xbzVariationsWithImageFromCsv
            ) {
                $baseSku = $this->baseSku($row);
                $sku = $this->value($row, 'sku') ?: $baseSku;
                if ($baseSku === '' || $sku === '') {
                    return;
                }

                $parent = $parents[$baseSku] ?? $row;
                $product = Product::where('base_sku', $baseSku)->first();
                $productImage = $this->firstImage($parent) ?: $this->firstImage($row);
                $productExisted = (bool) $product;

                if (!$product) {
                    $product = Product::create($this->productPayload($parent, $baseSku, $productImage));
                    $productsCreated++;
                } else {
                    $updates = [];
                    if (blank($product->image_url) && $productImage !== '') {
                        $updates['image_url'] = $productImage;
                    }
                    if (blank($product->description) && $this->value($parent, 'descricao') !== '') {
                        $updates['description'] = $this->value($parent, 'descricao');
                    }

                    if ($updates !== []) {
                        $product->update($updates);
                        $productsCompleted++;
                        if ($product->supplier === 'XBZ' && isset($updates['image_url'])) {
                            $xbzProductsWithImageFromCsv++;
                        }
                    }
                }

                if (!$this->isVariation($row) && ($childrenCount[$baseSku] ?? 0) > 0) {
                    return;
                }

                $variationImage = $this->firstImage($row) ?: $productImage;
                $variation = ProductVariation::where('sku', $sku)->first();

                if (!$variation) {
                    ProductVariation::create($this->variationPayload($row, $product, $sku, $variationImage));
                    $variationsCreated++;
                    return;
                }

                $updates = [];
                if (blank($variation->image_url) && $variationImage !== '') {
                    $updates['image_url'] = $variationImage;
                }
                if (blank($variation->supplier_url) && $this->value($row, 'url') !== '') {
                    $updates['supplier_url'] = $this->value($row, 'url');
                }
                if (blank($variation->color) && $this->value($row, 'cor') !== '') {
                    $updates['color'] = $this->value($row, 'cor');
                }

                if ($updates !== []) {
                    $variation->update($updates);
                    $variationsCompleted++;
                    if ($productExisted && $product->supplier === 'XBZ' && isset($updates['image_url'])) {
                        $xbzVariationsWithImageFromCsv++;
                    }
                }
            }, $limit);

            $message = "CSV importado. Produtos criados: {$productsCreated}. Variações criadas: {$variationsCreated}. Produtos complementados: {$productsCompleted}. Variações complementadas: {$variationsCompleted}. Fotos XBZ via CSV: produtos {$xbzProductsWithImageFromCsv}, variações {$xbzVariationsWithImageFromCsv}.";

            $log->update([
                'items_received' => $itemsReceived,
                'products_upserted' => $productsCreated + $productsCompleted,
                'variations_upserted' => $variationsCreated + $variationsCompleted,
                'status' => 'success',
                'message' => $message,
                'finished_at' => now(),
            ]);

            $this->info($message);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function foreachRow(string $file, callable $callback, int $limit = 0): void
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir o CSV: {$file}");
        }

        $this->headers = [];
        $count = 0;
        while (($line = fgets($handle)) !== false) {
            $data = str_getcsv($line, ';');
            $data = array_map(fn ($value) => $this->normalizeEncoding((string) $value), $data);

            if ($this->headers === []) {
                $this->headers = $data;
                continue;
            }

            if (count(array_filter($data, fn ($value) => $value !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($this->headers as $index => $header) {
                $row[$header] = $data[$index] ?? '';
            }
            $callback($row);
            $count++;

            if ($limit > 0 && $count >= $limit) {
                break;
            }
        }

        fclose($handle);
    }

    private function productPayload(array $row, string $baseSku, string $image): array
    {
        $category = null;
        if ($this->value($row, 'categoria') !== '') {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($this->value($row, 'categoria'))],
                ['name' => $this->value($row, 'categoria'), 'active' => true]
            );
        }

        return [
            'category_id' => $category?->id,
            'factor_table_id' => FactorTable::defaultMostExpensive()?->id,
            'supplier' => $this->supplier($row),
            'supplier_product_id' => $this->value($row, 'id') ?: $baseSku,
            'base_sku' => $baseSku,
            'name' => $this->value($row, 'nome') ?: $baseSku,
            'description' => $this->value($row, 'descricao') ?: null,
            'status' => $this->active($row) ? 'active' : 'inactive',
            'cost_price' => $this->decimal($this->value($row, 'custo')),
            'sale_price' => $this->decimal($this->value($row, 'custo')),
            'minimum_quantity' => max(1, (int) $this->decimal($this->value($row, 'quantidade minima de pedido'))),
            'weight' => $this->decimalOrNull($this->value($row, 'peso')),
            'height' => $this->decimalOrNull($this->value($row, 'altura')),
            'width' => $this->decimalOrNull($this->value($row, 'largura')),
            'depth' => $this->decimalOrNull($this->value($row, 'profundidade')),
            'thickness' => $this->decimalOrNull($this->value($row, 'espessura')),
            'length' => $this->decimalOrNull($this->value($row, 'comprimento')),
            'diameter' => $this->decimalOrNull($this->value($row, 'diametro')),
            'warranty' => $this->value($row, 'garantia') ?: null,
            'engraving_measure' => $this->value($row, 'tamanho da gravacao') ?: null,
            'total_size' => $this->value($row, 'tamanho total') ?: null,
            'energy' => $this->value($row, 'voltagem') ?: null,
            'image_url' => $image ?: null,
            'supplier_updated_at' => $this->dateOrNow($this->value($row, 'data de atualizacao')),
        ];
    }

    private function variationPayload(array $row, Product $product, string $sku, string $image): array
    {
        $color = $this->value($row, 'cor');
        $primaryColor = $this->colorFor($color);

        return [
            'product_id' => $product->id,
            'supplier_variation_id' => $this->value($row, 'id'),
            'sku' => $sku,
            'color_id' => $primaryColor?->id,
            'color' => $color ?: null,
            'status' => $this->active($row) ? 'active' : 'inactive',
            'cost_price' => $this->decimal($this->value($row, 'custo')),
            'sale_price' => $this->decimal($this->value($row, 'custo')),
            'stock' => 0,
            'main_stock' => 0,
            'image_url' => $image ?: null,
            'supplier_url' => $this->value($row, 'url') ?: null,
            'raw_payload' => $row,
        ];
    }

    private function baseSku(array $row): string
    {
        if ($this->isVariation($row) && $this->value($row, 'SKU relacional') !== '') {
            return $this->value($row, 'SKU relacional');
        }

        return $this->value($row, 'sku');
    }

    private function isVariation(array $row): bool
    {
        return $this->value($row, 'ID relacional') !== ''
            || $this->value($row, 'SKU relacional') !== ''
            || str_contains(Str::lower($this->value($row, 'tipo')), 'varia');
    }

    private function firstImage(array $row): string
    {
        foreach (['imagem', 'imagem_d1', 'imagem_d2', 'imagem_d3', 'imagem_d4', 'imagem_d5', 'imagem_d6', 'imagem_d7'] as $key) {
            $image = $this->value($row, $key);
            if ($image !== '') {
                return $image;
            }
        }

        return '';
    }

    private function supplier(array $row): string
    {
        $supplier = trim($this->value($row, 'fornecedor'));
        if ($supplier === '') {
            return 'CSV_CLIC';
        }

        return Str::upper(Str::ascii($supplier)) === 'XBZ' ? 'XBZ' : $supplier;
    }

    private function active(array $row): bool
    {
        return !in_array(Str::lower($this->value($row, 'status')), ['inativo', 'inactive', '0'], true);
    }

    private function colorFor(?string $name): ?Color
    {
        $name = trim((string) $name);
        if ($name === '' || $name === '-') {
            return null;
        }

        $group = ColorGroup::firstOrCreate(['name' => $name], ['active' => true]);

        return Color::firstOrCreate(['name' => $name], [
            'color_group_id' => $group->id,
            'code' => Str::upper(Str::substr($name, 0, 3)),
            'active' => true,
        ]);
    }

    private function dateOrNow(string $value): mixed
    {
        return $value !== '' ? $value : now();
    }

    private function decimalOrNull(string $value): ?float
    {
        return $value === '' ? null : $this->decimal($value);
    }

    private function decimal(string $value): float
    {
        $value = trim(str_replace(['R$', ' '], '', $value));
        if ($value === '') {
            return 0.0;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function value(array $row, string $key): string
    {
        return trim((string) ($row[$key] ?? ''));
    }

    private function normalizeEncoding(string $value): string
    {
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252, ISO-8859-1');
    }
}
