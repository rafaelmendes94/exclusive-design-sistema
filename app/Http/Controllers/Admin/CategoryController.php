<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FactorTable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->with(['parent', 'categoryFactorTable'])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.edit', $this->formData(new Category(['active' => true, 'show_in_menu' => true])));
    }

    public function store(Request $request)
    {
        $category = Category::create($this->validated($request));

        return redirect()->route('admin.categories.edit', $category)->with('success', 'Categoria criada.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', $this->formData($category));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('success', 'Categoria atualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída.');
    }

    public function featured()
    {
        return view('admin.categories.featured', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updateFeatured(Request $request)
    {
        Category::query()->update(['featured' => false]);
        Category::whereIn('id', $request->input('category_ids', []))->update(['featured' => true]);

        return back()->with('success', 'Categorias selecionadas atualizadas.');
    }

    private function formData(Category $category): array
    {
        return [
            'category' => $category,
            'parents' => Category::whereKeyNot($category->id ?: 0)->orderBy('name')->get(),
            'factorTables' => FactorTable::where('active', true)->orderBy('title')->get(),
        ];
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'banner_desktop' => ['nullable', 'url', 'max:255'],
            'banner_mobile' => ['nullable', 'url', 'max:255'],
            'banner_link' => ['nullable', 'url', 'max:255'],
            'icon_image' => ['nullable', 'url', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'seo_url' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'category_factor_table_id' => ['nullable', 'exists:factor_tables,id'],
        ]);

        $validated['slug'] = $validated['seo_url'] ?: Str::slug($validated['name']);
        if ($category && $category->exists) {
            $baseSlug = $validated['slug'];
            $i = 2;
            while (Category::where('slug', $validated['slug'])->whereKeyNot($category->id)->exists()) {
                $validated['slug'] = "{$baseSlug}-{$i}";
                $i++;
            }
        }

        $validated['parent_id'] = $validated['parent_id'] ?: null;
        $validated['category_factor_table_id'] = $validated['category_factor_table_id'] ?: null;
        $validated['active'] = $request->boolean('active');
        $validated['show_in_menu'] = $request->boolean('show_in_menu');
        $validated['update_items_price_table'] = $request->boolean('update_items_price_table');
        $validated['featured'] = $request->boolean('featured');

        return $validated;
    }
}
