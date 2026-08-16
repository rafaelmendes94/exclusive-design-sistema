<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings['site_name'])</title>
    <meta name="description" content="{{ $settings['meta_description'] }}">
    <link rel="stylesheet" href="/css/site.css">
    <link rel="stylesheet" href="/css/site-gallery.css">
    <style>
        :root {
            --brand-primary: {{ $settings['primary_color'] }};
            --brand-secondary: {{ $settings['secondary_color'] }};
            --brand-dark: {{ $settings['dark_color'] }};
            --brand-light: {{ $settings['light_color'] }};
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="site-top">
            <div>{{ $settings['phone'] }} <span>{{ $settings['email'] }}</span></div>
            <a href="{{ route('login') }}">Área restrita</a>
        </div>
        <div class="site-mainbar">
            <a class="site-logo" href="{{ route('site.home') }}"><img src="{{ $settings['logo'] }}" alt="{{ $settings['site_name'] }}"></a>
            <form class="site-search" method="get" action="{{ route('site.products') }}">
                <input name="q" value="{{ request('q') }}" placeholder="Encontre aqui seu produto ...">
                <button>Buscar</button>
            </form>
            <button class="site-cart" type="button" data-cart-open>0</button>
        </div>
        <nav class="site-nav">
            <a href="{{ route('site.home') }}">Home</a>
            <a href="{{ route('site.products') }}">Produtos</a>
            <a href="{{ route('site.products', ['novidades' => 1]) }}">Lançamentos</a>
            <a href="{{ route('site.products', ['promocoes' => 1]) }}">Promoções</a>
            <a href="#catalogos">Catálogos</a>
            <a href="#sobre">Sobre nós</a>
            <a href="#contato">Contato</a>
        </nav>
    </header>

    @yield('content')

    <footer class="site-footer" id="contato">
        <div class="footer-grid">
            <div>
                <img src="{{ $settings['logo_white'] }}" alt="{{ $settings['site_name'] }}">
                <p>{{ $settings['company_name'] }}</p>
                <p>CNPJ: {{ $settings['cnpj'] }}</p>
                <p>{{ $settings['phone'] }}</p>
                <p>{{ $settings['email'] }}</p>
            </div>
            <div>
                <h3>Atendimento</h3>
                <p>{{ $settings['address_line_1'] }}</p>
                <p>{{ $settings['address_line_2'] }}</p>
                <p>{{ $settings['district_city_state'] }}</p>
                <p>CEP: {{ $settings['zip'] }}</p>
            </div>
            <div>
                <h3>Menu</h3>
                <a href="{{ route('site.products') }}">Produtos</a>
                <a href="#sobre">Sobre</a>
                <a href="{{ route('login') }}">Área restrita</a>
            </div>
        </div>
        <div class="footer-bottom">Todos os direitos reservados © {{ $settings['site_name'] }} {{ date('Y') }}</div>
    </footer>
    <aside class="cart-panel" data-cart-panel aria-hidden="true">
        <div class="cart-head">
            <strong>Meu orçamento</strong>
            <button type="button" data-cart-close>×</button>
        </div>
        <div class="cart-items" data-cart-items></div>
        <div class="cart-form">
            <input data-cart-name placeholder="Seu nome">
            <input data-cart-company placeholder="Empresa">
            <input data-cart-phone placeholder="Telefone">
            <button type="button" data-cart-whatsapp>Enviar pelo WhatsApp</button>
        </div>
    </aside>
    <div class="cart-backdrop" data-cart-close></div>
    <button class="whatsapp-float" type="button" data-cart-open>Orçamento</button>
    <script>
        (() => {
            const phone = @json($settings['whatsapp']);
            const cartButton = document.querySelector('.site-cart');
            const panel = document.querySelector('[data-cart-panel]');
            const itemsBox = document.querySelector('[data-cart-items]');
            const backdrop = document.querySelector('.cart-backdrop');
            const storageKey = 'exclusive_quote_cart';
            const getCart = () => JSON.parse(localStorage.getItem(storageKey) || '[]');
            const setCart = (items) => localStorage.setItem(storageKey, JSON.stringify(items));
            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            const openCart = () => {
                panel.classList.add('open');
                backdrop.classList.add('open');
                panel.setAttribute('aria-hidden', 'false');
            };
            const closeCart = () => {
                panel.classList.remove('open');
                backdrop.classList.remove('open');
                panel.setAttribute('aria-hidden', 'true');
            };
            const render = () => {
                const items = getCart();
                cartButton.textContent = items.length;
                itemsBox.innerHTML = items.length ? items.map((item, index) => `
                    <div class="cart-item">
                        ${item.image ? `<img src="${escapeHtml(item.image)}" alt="">` : '<span></span>'}
                        <div>
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>Cód. ${escapeHtml(item.code)}</small>
                        </div>
                        <button type="button" data-remove-cart="${index}">×</button>
                    </div>
                `).join('') : '<p class="cart-empty">Nenhum produto no orçamento.</p>';
            };
            const selectImage = (image) => {
                if (!image) return;
                const mainImage = document.querySelector('[data-main-product-image]');
                if (mainImage) {
                    mainImage.src = image;
                }
                document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
                    thumb.classList.toggle('active', thumb.dataset.image === image);
                });
            };
            document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
                thumb.addEventListener('click', () => selectImage(thumb.dataset.image));
            });
            document.querySelectorAll('[data-variation-option]').forEach((option) => {
                option.addEventListener('click', () => {
                    const card = document.querySelector('.detail-cart[data-product-card]');
                    const code = document.querySelector('[data-current-code]');
                    const label = document.querySelector('[data-current-variation]');

                    document.querySelectorAll('[data-variation-option]').forEach((current) => current.classList.remove('active'));
                    option.classList.add('active');

                    if (card) {
                        card.dataset.code = option.dataset.code || card.dataset.baseCode || '';
                        card.dataset.name = option.dataset.name || card.dataset.baseName || '';
                        card.dataset.image = option.dataset.image || '';
                    }
                    if (code) {
                        code.textContent = option.dataset.code || '';
                    }
                    if (label) {
                        label.textContent = option.dataset.label || 'Selecionado';
                    }
                    selectImage(option.dataset.image);
                });
            });
            document.querySelectorAll('[data-add-cart]').forEach((button) => {
                button.addEventListener('click', () => {
                    const card = button.closest('[data-product-card]');
                    const item = { code: card.dataset.code, name: card.dataset.name, image: card.dataset.image };
                    const items = getCart();
                    if (!items.some((current) => current.code === item.code)) {
                        items.push(item);
                        setCart(items);
                    }
                    render();
                    openCart();
                });
            });
            document.querySelectorAll('[data-cart-open]').forEach((button) => button.addEventListener('click', openCart));
            document.querySelectorAll('[data-cart-close]').forEach((button) => button.addEventListener('click', closeCart));
            itemsBox.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-remove-cart]');
                if (!remove) return;
                const items = getCart();
                items.splice(Number(remove.dataset.removeCart), 1);
                setCart(items);
                render();
            });
            document.querySelector('[data-cart-whatsapp]').addEventListener('click', () => {
                const items = getCart();
                const name = document.querySelector('[data-cart-name]').value.trim();
                const company = document.querySelector('[data-cart-company]').value.trim();
                const contact = document.querySelector('[data-cart-phone]').value.trim();
                const lines = [
                    'Olá, gostaria de solicitar um orçamento:',
                    name ? `Nome: ${name}` : '',
                    company ? `Empresa: ${company}` : '',
                    contact ? `Telefone: ${contact}` : '',
                    '',
                    ...items.map((item, index) => `${index + 1}. ${item.name} - Cód. ${item.code}`),
                ].filter((line) => line !== '');
                window.open(`https://wa.me/${phone}?text=${encodeURIComponent(lines.join('\n'))}`, '_blank');
            });
            render();
        })();
    </script>
</body>
</html>
