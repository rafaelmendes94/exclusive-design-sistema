<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proposta #{{ str_pad($quote->id, 6, '0', STR_PAD_LEFT) }} - {{ $settings['site_name'] }}</title>
    <link rel="stylesheet" href="/css/proposal.css">
    <style>
        :root {
            --proposal-primary: {{ $settings['primary_color'] }};
            --proposal-secondary: {{ $settings['secondary_color'] }};
            --proposal-dark: {{ $settings['dark_color'] }};
            --proposal-light: {{ $settings['light_color'] }};
        }
    </style>
</head>
<body>
    <div class="proposal-actions">
        @unless($isPublic)
            <a href="{{ route('admin.quotes.edit', $quote) }}">Voltar</a>
        @endunless
        <button onclick="window.print()">Imprimir / PDF</button>
    </div>

    <main class="proposal-page">
        <header class="proposal-top">
            <div class="proposal-company">
                <img src="{{ $settings['logo'] }}" alt="{{ $settings['site_name'] }}">
                <div>
                    <strong>{{ $settings['company_name'] }}</strong>
                    <span>CNPJ: {{ $settings['cnpj'] }}</span>
                    <span>{{ $settings['phone'] }}</span>
                    <span>{{ $settings['email'] }}</span>
                    <span>{{ $settings['address_line_1'] }}</span>
                    <span>{{ $settings['address_line_2'] }}</span>
                    <span>{{ $settings['district_city_state'] }}</span>
                    <span>CEP: {{ $settings['zip'] }}</span>
                </div>
            </div>
            <div class="proposal-number">
                <span>PROPOSTA COMERCIAL</span>
                <strong>#{{ str_pad($quote->id, 6, '0', STR_PAD_LEFT) }}</strong>
                <small>{{ $quote->created_at->format('d/m/Y') }}</small>
            </div>
        </header>

        <div class="proposal-line"></div>

        <section class="proposal-boxes">
            <div class="proposal-box">
                <h2>Dados da proposta</h2>
                <dl>
                    <dt>Número:</dt><dd>#{{ str_pad($quote->id, 6, '0', STR_PAD_LEFT) }}</dd>
                    <dt>Data:</dt><dd>{{ $quote->created_at->format('d/m/Y') }}</dd>
                    <dt>Validade:</dt><dd>15 dias</dd>
                    <dt>Prazo de entrega:</dt><dd>20 dias úteis</dd>
                    <dt>Pagamento:</dt><dd>A definir</dd>
                    <dt>Vendedor:</dt><dd>{{ $quote->seller ?: 'Admin' }}</dd>
                    <dt>E-mail vendedor:</dt><dd>{{ auth()->check() ? auth()->user()->email : $settings['email'] }}</dd>
                </dl>
            </div>
            <div class="proposal-box">
                <h2>Dados do cliente</h2>
                <dl>
                    <dt>Empresa:</dt><dd>{{ $quote->company ?: 'Nao informado' }}</dd>
                    <dt>Contato:</dt><dd>{{ $quote->contact ?: 'Nao informado' }}</dd>
                    <dt>Telefone:</dt><dd>{{ $quote->phone ?: 'Nao informado' }}</dd>
                    <dt>E-mail:</dt><dd>{{ $quote->email ?: 'Nao informado' }}</dd>
                    <dt>CNPJ:</dt><dd>{{ $quote->customer?->cnpj ?: 'Nao informado' }}</dd>
                </dl>
            </div>
        </section>

        <table class="proposal-items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Código</th>
                    <th>Produto / Descrição</th>
                    <th>Qtd.</th>
                    <th>Preço Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>@if($item->image_url)<img src="{{ $item->image_url }}" alt="">@endif</td>
                        <td>{{ $item->sku }}</td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->description)<span>{{ $item->description }}</span>@endif
                            @if($item->variation?->color)<em>Cor / Variação: {{ $item->variation->color }}</em>@endif
                            <em>{{ $item->engraving ? 'Gravação: ' . $item->engraving : 'Sem gravação' }}</em>
                        </td>
                        <td>{{ number_format((int) $item->quantity, 0, ',', '.') }} un</td>
                        <td>R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="proposal-notes">
            <h2>Observações comerciais</h2>
            <p>{{ $quote->notes ?: 'Conforme alinhamento comercial.' }}</p>
        </section>

        <section class="proposal-summary">
            <div><span>Subtotal</span><strong>R$ {{ number_format((float) ($proposalTotals[1] ?? 0), 2, ',', '.') }}</strong></div>
            <div><span>Total Geral</span><strong>R$ {{ number_format((float) ($proposalTotals[1] ?? 0), 2, ',', '.') }}</strong></div>
        </section>

        <section class="proposal-terms">
            <h2>Termos e condições</h2>
            <p>{!! nl2br(e($settings['terms'])) !!}</p>
        </section>

        <section class="proposal-signatures">
            <div><span></span><strong>{{ $settings['company_name'] }}</strong><small>{{ $quote->seller ?: 'Admin' }}</small></div>
            <div><span></span><strong>Cliente</strong><small>{{ $quote->company ?: $quote->contact }}</small></div>
        </section>
    </main>
</body>
</html>
