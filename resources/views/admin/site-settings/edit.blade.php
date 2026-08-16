@extends('layouts.admin')

@section('title', 'CMS do Site')

@section('content')
    <div class="page-head">
        <div>
            <h1>CMS do Site</h1>
            <p>Identidade, contatos, cores, hero e textos usados no front e na proposta.</p>
        </div>
        <a class="btn" target="_blank" href="{{ route('site.home') }}">Ver site</a>
    </div>

    <form class="form-grid" method="post" action="{{ route('admin.site-settings.update') }}">
        @csrf
        @method('put')
        <label>Nome do site<input name="settings[site_name]" value="{{ $settings['site_name'] }}"></label>
        <label>Empresa<input name="settings[company_name]" value="{{ $settings['company_name'] }}"></label>
        <label>CNPJ<input name="settings[cnpj]" value="{{ $settings['cnpj'] }}"></label>
        <label>Telefone<input name="settings[phone]" value="{{ $settings['phone'] }}"></label>
        <label>WhatsApp<input name="settings[whatsapp]" value="{{ $settings['whatsapp'] }}"></label>
        <label>E-mail<input type="email" name="settings[email]" value="{{ $settings['email'] }}"></label>
        <label>Logo<input name="settings[logo]" value="{{ $settings['logo'] }}"></label>
        <label>Logo branca<input name="settings[logo_white]" value="{{ $settings['logo_white'] }}"></label>
        <label>Cor principal<input type="color" name="settings[primary_color]" value="{{ $settings['primary_color'] }}"></label>
        <label>Cor secundaria<input type="color" name="settings[secondary_color]" value="{{ $settings['secondary_color'] }}"></label>
        <label>Cor escura<input type="color" name="settings[dark_color]" value="{{ $settings['dark_color'] }}"></label>
        <label>Cor clara<input type="color" name="settings[light_color]" value="{{ $settings['light_color'] }}"></label>
        <label>Endereço<input name="settings[address_line_1]" value="{{ $settings['address_line_1'] }}"></label>
        <label>Complemento<input name="settings[address_line_2]" value="{{ $settings['address_line_2'] }}"></label>
        <label>Bairro / Cidade<input name="settings[district_city_state]" value="{{ $settings['district_city_state'] }}"></label>
        <label>CEP<input name="settings[zip]" value="{{ $settings['zip'] }}"></label>
        <label class="wide">Descrição SEO<textarea name="settings[meta_description]" rows="2">{{ $settings['meta_description'] }}</textarea></label>
        <label>Chamada do banner<input name="settings[hero_badge]" value="{{ $settings['hero_badge'] }}"></label>
        <label class="wide">Título do banner<input name="settings[hero_title]" value="{{ $settings['hero_title'] }}"></label>
        <label class="wide">Texto do banner<textarea name="settings[hero_text]" rows="2">{{ $settings['hero_text'] }}</textarea></label>
        <label class="wide">Imagem do banner<input name="settings[hero_image]" value="{{ $settings['hero_image'] }}"></label>
        <label class="wide">Termos da proposta<textarea name="settings[terms]" rows="6">{{ $settings['terms'] }}</textarea></label>
        <div class="wide actions"><button class="btn primary">Salvar CMS</button></div>
    </form>
@endsection
