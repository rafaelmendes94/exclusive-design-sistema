<?php

namespace App\Console\Commands;

use App\Models\Color;
use App\Models\ColorGroup;
use App\Models\FactorTable;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Supplier;
use App\Models\SupplierSyncLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:import-asia-products {--file= : Importa a partir de um JSON salvo} {--url= : URL da API de produtos da ASIA} {--limit= : Limita a quantidade para testes}')]
#[Description('Importa produtos e variações da API ASIA Import')]
class ImportAsiaProducts extends Command
{
    public function handle(): int
    {
        $log = SupplierSyncLog::create([
            'supplier' => $this->supplierCode(),
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            [$items, $httpStatus] = $this->loadItems();
            $limit = (int) $this->option('limit');
            if ($limit > 0) {
                $items = array_slice($items, 0, $limit);
            }

            $products = 0;
            $variations = 0;

            foreach ($this->groupItems($items) as $baseSku => $group) {
                $first = $group[0];
                $product = Product::firstOrNew(['base_sku' => (string) $baseSku]);

                if (!$product->exists || !$product->block_supplier_update) {
                    $product->fill([
                        'supplier' => $this->supplierCode(),
                        'factor_table_id' => $product->factor_table_id ?: FactorTable::defaultMostExpensive()?->id,
                        'supplier_product_id' => (string) ($first['supplier_product_id'] ?? $baseSku),
                        'name' => $first['name'] ?: $baseSku,
                        'description' => $first['description'] ?? null,
                        'status' => ($first['stock'] ?? 0) > 0 ? 'active' : 'inactive',
                        'cost_price' => $this->decimal($first['cost_price'] ?? $first['sale_price'] ?? 0),
                        'sale_price' => $this->decimal($first['sale_price'] ?? $first['cost_price'] ?? 0),
                        'minimum_quantity' => (int) ($first['minimum_quantity'] ?? 1),
                        'weight' => $this->decimal($first['weight'] ?? 0),
                        'height' => $this->decimal($first['height'] ?? 0),
                        'width' => $this->decimal($first['width'] ?? 0),
                        'depth' => $this->decimal($first['depth'] ?? 0),
                        'ncm' => $first['ncm'] ?? null,
                        'image_url' => $first['image_url'] ?? null,
                        'supplier_updated_at' => now(),
                    ])->save();
                    $products++;
                }

                foreach ($group as $item) {
                    $primaryColor = $this->colorFor($item['color'] ?? null);
                    $secondaryColor = $this->colorFor($item['secondary_color'] ?? null);

                    ProductVariation::updateOrCreate(
                        ['sku' => (string) ($item['sku'] ?: $baseSku)],
                        [
                            'product_id' => $product->id,
                            'supplier_variation_id' => (string) ($item['supplier_variation_id'] ?? ''),
                            'color_id' => $primaryColor?->id,
                            'secondary_color_id' => $secondaryColor?->id,
                            'color' => $item['color'] ?? null,
                            'secondary_color' => $item['secondary_color'] ?? null,
                            'status' => ($item['stock'] ?? 0) > 0 ? 'active' : 'inactive',
                            'cost_price' => $this->decimal($item['cost_price'] ?? $item['sale_price'] ?? 0),
                            'sale_price' => $this->decimal($item['sale_price'] ?? $item['cost_price'] ?? 0),
                            'stock' => (int) ($item['stock'] ?? 0),
                            'main_stock' => (int) ($item['main_stock'] ?? $item['stock'] ?? 0),
                            'image_url' => $item['image_url'] ?? null,
                            'supplier_url' => $item['supplier_url'] ?? null,
                            'raw_payload' => $item['raw_payload'] ?? $item,
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
                'message' => "Importação {$this->supplierLabel()} concluída.",
                'finished_at' => now(),
            ]);

            $this->info("Importação {$this->supplierLabel()} concluída: {$products} produtos, {$variations} variações.");

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

            return [$this->extractItems(json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR)), 200];
        }

        $supplier = Supplier::where('code', $this->supplierCode())->first();
        $url = $this->option('url') ?: config($this->apiUrlConfigPath()) ?: $supplier?->api_url;
        if (!$url) {
            throw new \RuntimeException("Cadastre a URL de produtos da API {$this->supplierLabel()} no fornecedor ou informe --url.");
        }

        $apiKey = config($this->apiKeyConfigPath()) ?: $supplier?->api_key;
        $apiSecret = config($this->apiSecretConfigPath()) ?: $supplier?->api_secret;

        $response = Http::timeout(90)
            ->acceptJson()
            ->withHeaders($this->credentialHeaders($apiKey, $apiSecret))
            ->get($url, $this->credentialQuery($apiKey, $apiSecret));

        if (!$response->successful()) {
            throw new \RuntimeException("{$this->supplierLabel()} retornou HTTP {$response->status()}.");
        }

        return [$this->extractItems($response->json()), $response->status()];
    }

    protected function supplierCode(): string
    {
        return 'ASIA_IMPORT';
    }

    protected function supplierLabel(): string
    {
        return 'ASIA';
    }

    protected function apiUrlConfigPath(): string
    {
        return 'services.asia.url';
    }

    protected function apiKeyConfigPath(): string
    {
        return 'services.asia.key';
    }

    protected function apiSecretConfigPath(): string
    {
        return 'services.asia.secret';
    }

    protected function credentialHeaders(?string $apiKey, ?string $apiSecret): array
    {
        return [
            'api_key' => (string) $apiKey,
            'api_secret' => (string) $apiSecret,
            'x-api-key' => (string) $apiKey,
            'x-api-secret' => (string) $apiSecret,
            'client_id' => (string) $apiKey,
            'access_key' => (string) $apiSecret,
        ];
    }

    protected function credentialQuery(?string $apiKey, ?string $apiSecret): array
    {
        return [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'client_id' => $apiKey,
            'access_key' => $apiSecret,
        ];
    }

    private function extractItems(array $payload): array
    {
        $items = data_get($payload, 'data')
            ?? data_get($payload, 'produtos')
            ?? data_get($payload, 'products')
            ?? data_get($payload, 'items')
            ?? $payload;

        if (!is_array($items)) {
            throw new \RuntimeException("Retorno da {$this->supplierLabel()} não contém uma lista de produtos.");
        }

        return array_values($this->flattenProducts($items));
    }

    private function flattenProducts(array $items): array
    {
        $flat = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $variations = $item['variacoes'] ?? $item['variations'] ?? $item['cores'] ?? null;
            if (is_array($variations) && $variations !== []) {
                foreach ($variations as $variation) {
                    if (!is_array($variation)) {
                        continue;
                    }
                    $flat[] = $this->normalizeItem($variation + $item, $item);
                }
                continue;
            }

            $flat[] = $this->normalizeItem($item);
        }

        return $flat;
    }

    private function normalizeItem(array $item, array $parent = []): array
    {
        $baseSku = $this->firstFilled($parent, ['codigo', 'referencia', 'sku_pai', 'codigo_pai', 'code', 'reference'])
            ?: $this->firstFilled($item, ['codigo_pai', 'referencia_pai', 'sku_pai', 'referencia', 'codigo', 'code', 'reference', 'sku']);
        $sku = $this->firstFilled($item, ['sku', 'codigo_variacao', 'codigo_completo', 'codigo', 'code'])
            ?: $baseSku;

        return [
            'base_sku' => (string) $baseSku,
            'sku' => (string) $sku,
            'supplier_product_id' => $this->firstFilled($parent, ['id', 'id_produto', 'produto_id']) ?: $baseSku,
            'supplier_variation_id' => $this->firstFilled($item, ['id', 'id_variacao', 'variacao_id']) ?: $sku,
            'name' => $this->firstFilled($parent, ['nome', 'name', 'titulo', 'title']) ?: $this->firstFilled($item, ['nome', 'name', 'titulo', 'title']) ?: $baseSku,
            'description' => $this->firstFilled($parent, ['descricao', 'description', 'detalhes']) ?: $this->firstFilled($item, ['descricao', 'description', 'detalhes']),
            'color' => $this->firstFilled($item, ['cor', 'color', 'cor_principal', 'primary_color']),
            'secondary_color' => $this->firstFilled($item, ['cor_secundaria', 'secondary_color']),
            'cost_price' => $this->firstFilled($item, ['preco_custo', 'cost_price', 'preco', 'price']),
            'sale_price' => $this->firstFilled($item, ['preco_venda', 'sale_price', 'preco', 'price']),
            'stock' => $this->firstFilled($item, ['estoque', 'stock', 'quantidade', 'saldo']) ?: 0,
            'main_stock' => $this->firstFilled($item, ['estoque_principal', 'main_stock']),
            'minimum_quantity' => $this->firstFilled($parent, ['quantidade_minima', 'minimum_quantity']) ?: 1,
            'weight' => $this->firstFilled($parent, ['peso', 'weight']) ?: $this->firstFilled($item, ['peso', 'weight']),
            'height' => $this->firstFilled($parent, ['altura', 'height']) ?: $this->firstFilled($item, ['altura', 'height']),
            'width' => $this->firstFilled($parent, ['largura', 'width']) ?: $this->firstFilled($item, ['largura', 'width']),
            'depth' => $this->firstFilled($parent, ['profundidade', 'depth']) ?: $this->firstFilled($item, ['profundidade', 'depth']),
            'ncm' => $this->firstFilled($parent, ['ncm']) ?: $this->firstFilled($item, ['ncm']),
            'image_url' => $this->firstFilled($item, ['imagem', 'image', 'image_url', 'foto', 'url_imagem'])
                ?: $this->firstFilled($parent, ['imagem', 'image', 'image_url', 'foto', 'url_imagem']),
            'supplier_url' => $this->firstFilled($item, ['url', 'link', 'supplier_url'])
                ?: $this->firstFilled($parent, ['url', 'link', 'supplier_url']),
            'raw_payload' => $item,
        ];
    }

    private function groupItems(array $items): array
    {
        $groups = [];

        foreach ($items as $item) {
            $baseSku = trim((string) ($item['base_sku'] ?? ''));
            if ($baseSku === '') {
                continue;
            }
            $groups[$baseSku][] = $item;
        }

        return $groups;
    }

    private function firstFilled(array $item, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (isset($item[$key]) && $item[$key] !== '') {
                return $item[$key];
            }
        }

        return null;
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
            $value = str_replace(['R$', ' '], '', $value);
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        return (float) $value;
    }
}
