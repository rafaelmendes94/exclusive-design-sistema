<?php

namespace Database\Seeders;

use App\Models\FactorTable;
use App\Models\BusinessSegment;
use App\Models\Color;
use App\Models\ColorGroup;
use App\Models\Engraving;
use App\Models\QuoteStatus;
use App\Models\SiteSetting;
use App\Models\Splash;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Aguardando', 'color' => '#f59e0b', 'position' => 1],
            ['name' => 'Em negociação', 'color' => '#2563eb', 'position' => 2],
            ['name' => 'Aprovado', 'color' => '#16a34a', 'position' => 3],
            ['name' => 'Cancelado', 'color' => '#dc2626', 'position' => 4],
        ] as $status) {
            QuoteStatus::firstOrCreate(['name' => $status['name']], $status + ['active' => true]);
        }

        $tables = [
            'TAB.1' => 100 / 212.7660,
            'TAB.2' => 100 / 200.0000,
            'TAB.3' => 100 / 188.6792,
            'TAB.4' => 100 / 178.5714,
            'TAB.5' => 100 / 169.4915,
            'TAB.6' => 100 / 161.2903,
            'TAB.7' => 100 / 153.8462,
        ];

        foreach ($tables as $title => $coefficient) {
            $table = FactorTable::updateOrCreate(['title' => $title], ['active' => true]);
            $table->ranges()->delete();
            $table->ranges()->create([
                'quantity_from' => 1,
                'quantity_to' => 10000,
                'coefficient' => $coefficient,
            ]);
        }

        foreach (['Agência', 'Indústria', 'Tecnologia', 'Eventos', 'Educação'] as $segment) {
            BusinessSegment::firstOrCreate(['name' => $segment], ['active' => true]);
        }

        foreach (['Branco', 'Preto', 'Azul', 'Vermelho', 'Verde', 'Amarelo', 'Metalizado'] as $groupName) {
            $group = ColorGroup::firstOrCreate(['name' => $groupName], ['active' => true]);
            Color::firstOrCreate(['name' => $groupName], [
                'color_group_id' => $group->id,
                'code' => strtoupper(substr($groupName, 0, 3)),
                'active' => true,
            ]);
        }

        foreach ([
            'BORDADO',
            'DIGITAL UV',
            'DTF - UV GRAVAÇÃO 30X30MM',
            'LASER',
            'SILK - UMA COR',
            'SUBLIMAÇÃO',
        ] as $engravingName) {
            $engraving = Engraving::firstOrCreate(['name' => $engravingName], ['active' => true]);
            $engraving->priceRanges()->firstOrCreate([
                'quantity_from' => 1,
                'quantity_to' => 10000,
            ], [
                'price' => 0,
            ]);
        }

        foreach (['Lançamento', 'Promoção', 'Mais vendido'] as $splashName) {
            Splash::firstOrCreate(['name' => $splashName], ['active' => true]);
        }

        foreach (SiteSetting::defaults() as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], [
                'value' => $value,
                'type' => str_contains($key, 'color') ? 'color' : 'text',
            ]);
        }

        Supplier::updateOrCreate(['code' => 'XBZ'], [
            'name' => 'XBZ Brindes',
            'active' => true,
            'cnpj' => '66.163.388/0001-50',
            'api_url' => 'https://api.minhaxbz.com.br:5001/api/clientes/GetListaDeProdutos',
            'notes' => 'Fornecedor integrado via API de produtos.',
        ]);

        Supplier::updateOrCreate(['code' => 'EXCLUSIVE'], [
            'name' => 'Exclusive Design',
            'active' => true,
            'cnpj' => '66.163.388/0001-50',
            'email' => 'contato@exclusivedesign.com.br',
            'phone' => '(11) 52861010',
            'notes' => 'Produtos próprios e cadastrados manualmente no site atual.',
        ]);

        $strickerData = [
            'name' => 'Stricker Brasil',
            'active' => true,
            'notes' => 'Fornecedor listado no módulo de integrações do site atual.',
        ];
        foreach (['api_key' => env('STRICKER_CLIENT_ID'), 'api_secret' => env('STRICKER_ACCESS_KEY'), 'api_url' => env('STRICKER_API_URL')] as $field => $value) {
            if (filled($value)) {
                $strickerData[$field] = $value;
            }
        }
        Supplier::updateOrCreate(['code' => 'STRICKER'], $strickerData);

        $asiaData = [
            'name' => 'Asia Import',
            'active' => true,
            'notes' => 'Fornecedor listado no módulo de integrações do site atual. A importação depende da URL de produtos da API ASIA.',
        ];
        foreach (['api_key' => env('ASIA_API_KEY'), 'api_secret' => env('ASIA_API_SECRET'), 'api_url' => env('ASIA_API_URL')] as $field => $value) {
            if (filled($value)) {
                $asiaData[$field] = $value;
            }
        }
        Supplier::updateOrCreate(['code' => 'ASIA_IMPORT'], $asiaData);

        Supplier::updateOrCreate(['code' => 'BLING'], [
            'name' => 'Bling',
            'active' => true,
            'notes' => 'Integração/listagem de fornecedor presente no painel atual.',
        ]);

        User::updateOrCreate(['email' => 'admin@local.test'], [
            'name' => 'Administrador',
            'password' => 'admin123',
            'role' => 'admin',
            'active' => true,
            'can_view_supplier' => true,
            'can_view_cost' => true,
            'can_view_factor' => true,
        ]);

        User::updateOrCreate(['email' => 'admin@test.com'], [
            'name' => 'Administrador',
            'password' => 'admin123',
            'role' => 'admin',
            'active' => true,
            'can_view_supplier' => true,
            'can_view_cost' => true,
            'can_view_factor' => true,
        ]);

        User::updateOrCreate(['email' => 'vendedor@local.test'], [
            'name' => 'Vendedor Teste',
            'password' => 'vendedor123',
            'role' => 'seller',
            'active' => true,
            'can_view_supplier' => false,
            'can_view_cost' => false,
            'can_view_factor' => false,
        ]);
    }
}
