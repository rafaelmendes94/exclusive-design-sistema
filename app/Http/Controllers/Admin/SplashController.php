<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Splash;
use Illuminate\Http\Request;

class SplashController extends Controller
{
    public function index()
    {
        return view('admin.splashes.index', [
            'splashes' => Splash::latest()->paginate(30),
        ]);
    }

    public function store(Request $request)
    {
        Splash::create($this->validated($request));

        return back()->with('success', 'Splash criado.');
    }

    public function update(Request $request, Splash $splash)
    {
        $splash->update($this->validated($request));

        return back()->with('success', 'Splash atualizado.');
    }

    public function destroy(Splash $splash)
    {
        $splash->delete();

        return back()->with('success', 'Splash excluído.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);
        $validated['active'] = $request->boolean('active');

        return $validated;
    }
}
