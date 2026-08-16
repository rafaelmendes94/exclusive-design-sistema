<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:import-exclusive-customers {file : JSON extraído do painel atual da Exclusive}')]
#[Description('Importa clientes do painel atual da Exclusive Design')]
class ImportExclusiveCustomers extends Command
{
    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (!is_readable($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        $created = 0;
        $updated = 0;
        $sellersCreated = 0;

        foreach ($items as $item) {
            $fields = $item['fields'] ?? [];
            $remoteId = (string) ($item['remote_id'] ?? $fields['id'] ?? '');
            $seller = $this->sellerFor($fields['seller_id__label'] ?? null, $sellersCreated);
            $document = preg_replace('/\D+/', '', (string) ($fields['document'] ?? ''));
            $email = trim((string) ($fields['email'] ?? ''));

            $payload = [
                'active' => !in_array($fields['status'] ?? '', ['inativo', 'bloqueado'], true),
                'seller_id' => $seller?->id,
                'name' => $this->clean($fields['name'] ?? null),
                'company' => $this->clean($fields['trade_name'] ?? null) ?: $this->clean($fields['company_name'] ?? null) ?: $this->clean($fields['name'] ?? null),
                'legal_name' => $this->clean($fields['company_name'] ?? null),
                'state_registration' => $this->clean($fields['state_registration'] ?? null),
                'cnpj' => strlen($document) === 14 ? $document : null,
                'cpf' => strlen($document) === 11 ? $document : null,
                'zip' => $this->clean($fields['address_zipcode'] ?? null),
                'street' => $this->clean($fields['address_street'] ?? null),
                'number' => $this->clean($fields['address_number'] ?? null),
                'complement' => $this->clean($fields['address_complement'] ?? null),
                'district' => $this->clean($fields['address_district'] ?? null),
                'city' => $this->clean($fields['address_city'] ?? null),
                'state' => Str::upper($this->clean($fields['address_state'] ?? null)),
                'email' => $email ?: null,
                'commercial_phone' => $this->clean($fields['phone'] ?? null),
                'mobile_phone' => $this->clean($fields['whatsapp'] ?? null),
                'notes' => $this->notes($remoteId, $fields),
            ];

            $query = Customer::query();
            if ($payload['cnpj']) {
                $query->where('cnpj', $payload['cnpj']);
            } elseif ($payload['cpf']) {
                $query->where('cpf', $payload['cpf']);
            } elseif ($payload['email']) {
                $query->where('email', $payload['email']);
            } else {
                $query->where('name', $payload['name'])->where('company', $payload['company']);
            }

            $customer = $query->first();
            if ($customer) {
                $customer->update($payload);
                $updated++;
            } else {
                Customer::create($payload);
                $created++;
            }
        }

        $this->info("Clientes criados: {$created}");
        $this->info("Clientes atualizados: {$updated}");
        $this->info("Vendedores criados: {$sellersCreated}");

        return self::SUCCESS;
    }

    private function sellerFor(?string $name, int &$created): ?User
    {
        $name = $this->clean($name);
        if (!$name || $name === 'Sem vendedor') {
            return null;
        }

        $email = Str::slug($name) . '@exclusive.local';
        $seller = User::where('role', 'seller')->where('name', $name)->first();
        if ($seller) {
            return $seller;
        }

        $created++;

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'vendedor123',
            'role' => 'seller',
            'active' => true,
            'can_view_supplier' => false,
            'can_view_cost' => false,
            'can_view_factor' => false,
        ]);
    }

    private function notes(string $remoteId, array $fields): string
    {
        $contacts = [];
        foreach (($fields['contact_name_items'] ?? []) as $index => $name) {
            $contact = array_filter([
                'nome' => $this->clean($name),
                'cargo' => $this->clean(($fields['contact_position_items'] ?? [])[$index] ?? null),
                'email' => $this->clean(($fields['contact_email'] ?? [])[$index] ?? null),
                'telefone' => $this->clean(($fields['contact_phone'] ?? [])[$index] ?? null),
                'whatsapp' => $this->clean(($fields['contact_whatsapp'] ?? [])[$index] ?? null),
                'observacao' => $this->clean(($fields['contact_notes'] ?? [])[$index] ?? null),
            ]);

            if ($contact !== []) {
                $contacts[] = $contact;
            }
        }

        $lines = array_filter([
            "Importado do painel atual Exclusive. ID original: {$remoteId}",
            'Status original: ' . $this->clean($fields['status__label'] ?? $fields['status'] ?? null),
            'Tipo de pessoa: ' . $this->clean($fields['type'] ?? null),
            'Data nascimento/fundação: ' . $this->clean($fields['birth_date'] ?? null),
            'Inscrição municipal: ' . $this->clean($fields['municipal_registration'] ?? null),
            'Tipo de endereço: ' . $this->clean($fields['address_type__label'] ?? $fields['address_type'] ?? null),
            $this->clean($fields['notes'] ?? null) ? 'Observações: ' . $this->clean($fields['notes']) : null,
            $this->clean($fields['internal_notes'] ?? null) ? 'Notas internas: ' . $this->clean($fields['internal_notes']) : null,
            $contacts ? 'Contatos adicionais: ' . json_encode($contacts, JSON_UNESCAPED_UNICODE) : null,
        ]);

        return implode("\n", $lines);
    }

    private function clean(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === 'Array') {
            return null;
        }

        return $value;
    }
}
