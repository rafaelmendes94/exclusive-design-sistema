@extends('layouts.admin')

@section('title', 'Categoria')

@section('content')
    <div class="page-head">
        <div><h1>{{ $category->exists ? 'Editar Categoria' : 'Nova Categoria' }}</h1><p>Cadastro baseado na tela de categorias do Clic.</p></div>
        <a class="btn" href="{{ route('admin.categories.index') }}">Voltar</a>
    </div>

    <form class="form-grid" method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($category->exists) @method('put') @endif
        <label class="check"><input type="checkbox" name="active" value="1" @checked(old('active', $category->active))> Ativo?</label>
        <label class="check"><input type="checkbox" name="show_in_menu" value="1" @checked(old('show_in_menu', $category->show_in_menu))> Aparece no menu?</label>
        <label class="check"><input type="checkbox" name="featured" value="1" @checked(old('featured', $category->featured))> Categoria selecionada</label>
        <label class="check"><input type="checkbox" name="update_items_price_table" value="1" @checked(old('update_items_price_table', $category->update_items_price_table))> Atualizar itens?</label>
        <label>Nome<input name="name" value="{{ old('name', $category->name) }}" required></label>
        <label>Categoria pai
            <select name="parent_id"><option value="">Sem categoria pai</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected($category->parent_id === $parent->id)>{{ $parent->name }}</option>@endforeach</select>
        </label>
        <label>Tabela de Fatores
            <select name="category_factor_table_id"><option value="">Sem tabela</option>@foreach($factorTables as $factorTable)<option value="{{ $factorTable->id }}" @selected($category->category_factor_table_id === $factorTable->id)>{{ $factorTable->title }}</option>@endforeach</select>
        </label>
        <label>Link do banner<input name="banner_link" value="{{ old('banner_link', $category->banner_link) }}"></label>
        <label>Banner Desktop URL<input name="banner_desktop" value="{{ old('banner_desktop', $category->banner_desktop) }}"></label>
        <label>Banner Mobile URL<input name="banner_mobile" value="{{ old('banner_mobile', $category->banner_mobile) }}"></label>
        <label>Imagem ícone URL<input name="icon_image" value="{{ old('icon_image', $category->icon_image) }}"></label>
        <label>SEO URL<input name="seo_url" value="{{ old('seo_url', $category->seo_url ?: $category->slug) }}"></label>
        <label>SEO Title<input name="seo_title" value="{{ old('seo_title', $category->seo_title) }}"></label>
        <label>SEO Keywords<input name="seo_keywords" value="{{ old('seo_keywords', $category->seo_keywords) }}"></label>
        <label>SEO Description<input name="seo_description" value="{{ old('seo_description', $category->seo_description) }}"></label>
        <label class="wide">Descrição<textarea name="description" rows="5">{{ old('description', $category->description) }}</textarea></label>
        <div class="wide actions"><button class="btn primary">Salvar</button></div>
    </form>
@endsection
