<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('brands.index', [
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Brand::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('brands.index')->with('success', 'Brand has been created!');
    }

    public function edit(Brand $brand): View
    {
        return view('brands.edit', ['brand' => $brand]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]));

        return redirect()->route('brands.index')->with('success', 'Brand has been updated!');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand has been deleted!');
    }
}