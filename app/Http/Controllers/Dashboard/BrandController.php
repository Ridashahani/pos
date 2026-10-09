<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $search = request('search');

        return view('brands.index', [
            'brands' => Brand::query()
                ->when(filled($search), fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->paginate(10)
                ->appends(request()->query()),
        ]);
    }

    public function create(): View
    {
        return view('brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return redirect()->route('brands.index')->with('success', 'Brand has been created!');
    }

    public function edit(Brand $brand): View
    {
        return view('brands.edit', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('brands.index')->with('success', 'Brand has been updated!');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')
                ->with('error', 'This brand cannot be deleted because it is assigned to one or more products.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand has been deleted!');
    }
}
