<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Color;
use App\Models\ColorGroup;
use App\Models\FactorTable;
use App\Models\SupplierSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-xbz-products {--file= : Importa a partir de um JSON salvo} {--limit= : Limita a quantidade de variações para testes}')]
#[Description('Importa produtos e variações da API XBZ')]
class ImportXbzProducts extends Command
{
    public function handle(): int
    {
        $log = SupplierSyncLog::create([
            'supplier' => 'XBZ',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            [$items, $httpStatus] = $this->loadItems();
            $limit = (int) $this->option('limit');
            if ($limit > 0) {
                $items = array_slice($items, 0, $limit);
            }

            $groups = collect($items)->groupBy(fn ($item) => $item['CodigoAmigavel'] ?: $item['CodigoXbz'] ?: $item['CodigoComposto']);
            $products = 0;
            $variations = 0;

            foreach ($groups as $baseSku => $group) {
                $first = $group->first();
                $product = Product::firstOrNew(['base_sku' => (string) $baseSku]);

                if (!$product->exists || !$product->block_supplier_update) {
                    $product->fill([
                        'supplier' => 'XBZ',
                        'factor_table_id' => $product->factor_table_id ?: FactorTable::defaultMostExpensive()?->id,
                        'supplier_product_id' => (string) ($first['IdProduto'] ?? $baseSku),
                        'name' => $first['Nome'] ?: $baseSku,
                        'description' => $first['Descricao'] ?? null,
                        'status' => 'active',
                        'cost_price' => $this->money($first['PrecoVenda'] ?? 0),
                        'sale_price' => $this->money($first['PrecoVenda'] ?? 0),
                        'minimum_quantity' => 1,
                        'weight' => $this->decimal($first['Peso'] ?? 0),
                        'height' => $this->decimal($first['Altura'] ?? 0),
                        'width' => $this->decimal($first['Largura'] ?? 0),
                        'depth' => $this->decimal($first['Profundidade'] ?? 0),
                        'ncm' => $first['Ncm'] ?? null,
                        'image_url' => $first['ImageLink'] ?? null,
                        'supplier_updated_at' => now(),
                    ])->save();
                    $products++;
                }

                foreach ($group as $item) {
                    $primaryColor = $this->colorFor($item['CorWebPrincipal'] ?? null);
                    $secondaryColor = $this->colorFor($item['CorWebSecundaria'] ?? null);

                    ProductVariation::updateOrCreate(
                        ['sku' => (string) ($item['CodigoComposto'] ?: $item['CodigoXbz'])],
                        [
                            'product_id' => $product->id,
                            'supplier_variation_id' => (string) ($item['IdProduto'] ?? ''),
                            'color_id' => $primaryColor?->id,
                            'secondary_color_id' => $secondaryColor?->id,
                            'color' => $item['CorWebPrincipal'] ?? null,
                            'secondary_color' => $item['CorWebSecundaria'] ?? null,
                            'status' => (($item['QuantidadeDisponivel'] ?? 0) > 0) ? 'active' : 'inactive',
                            'cost_price' => $this->money($item['PrecoVenda'] ?? 0),
                            'sale_price' => $this->money($item['PrecoVenda'] ?? 0),
                            'stock' => (int) ($item['QuantidadeDisponivel'] ?? 0),
                            'main_stock' => (int) ($item['QuantidadeDisponivelEstoquePrincipal'] ?? 0),
                            'image_url' => $item['ImageLink'] ?? null,
                            'supplier_url' => $item['SiteLink'] ?? null,
                            'raw_payload' => $item,
                        ]
                    );
                    $variations++;
                }
            }

            $log->update([
                'http_status' => $httpStatus,
                'items_received' => count($items),
                'products_upserted' => $products,
                'variations_upserted' => $variations,
                'status' => 'success',
                'message' => 'Importação concluída.',
                'finished_at' => now(),
            ]);

            $this->info("Importação XBZ concluída: {$products} produtos, {$variations} variações.");

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

    private function loadItems(): array
    {
        if ($file = $this->option('file')) {
            if (!is_readable($file)) {
                throw new \RuntimeException("Arquivo não encontrado: {$file}");
            }

            return [json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR), 200];
        }

        $url = config('services.xbz.url') ?: sprintf(
            'https://api.minhaxbz.com.br:5001/api/clientes/GetListaDeProdutos?cnpj=%s&token=%s',
            config('services.xbz.cnpj'),
            config('services.xbz.token')
        );

        $response = Http::timeout(60)->get($url);
        if (!$response->successful()) {
            throw new \RuntimeException("XBZ retornou HTTP {$response->status()}.");
        }

        return [$response->json(), $response->status()];
    }

    private function money(mixed $value): float
    {
        return $this->decimal($value);
    }

    private function colorFor(?string $name): ?Color
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $group = ColorGroup::firstOrCreate(['name' => $name], ['active' => true]);

        return Color::firstOrCreate(['name' => $name], [
            'color_group_id' => $group->id,
            'code' => strtoupper(mb_substr($name, 0, 3)),
            'active' => true,
        ]);
    }

    private function decimal(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return (float) $value;
    }
}
