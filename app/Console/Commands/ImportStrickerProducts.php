<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('app:import-stricker-products {--file= : Importa a partir de um JSON salvo} {--url= : URL da API de produtos da STRICKER/SPOT} {--limit= : Limita a quantidade para testes}')]
#[Description('Importa produtos e variações da API STRICKER/SPOT')]
class ImportStrickerProducts extends ImportAsiaProducts
{
    protected function supplierCode(): string
    {
        return 'STRICKER';
    }

    protected function supplierLabel(): string
    {
        return 'STRICKER/SPOT';
    }

    protected function apiUrlConfigPath(): string
    {
        return 'services.stricker.url';
    }

    protected function apiKeyConfigPath(): string
    {
        return 'services.stricker.client_id';
    }

    protected function apiSecretConfigPath(): string
    {
        return 'services.stricker.access_key';
    }
}
