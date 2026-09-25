<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subcategory\StoreSubcategoryRequest;
use App\Http\Requests\Subcategory\UpdateSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Redirect;
use Spatie\QueryBuilder\QueryBuilder;

class SubcategoryController extends Controller
{
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return view('subcategories.index', [
            'subcategories' => QueryBuilder::for(Subcategory::class)
                ->with('category')
                ->allowedSorts(['name', 'slug'])
                ->filter(request(['search']))
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    public function create()
    {
        return view('subcategories.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreSubcategoryRequest $request)
    {
        Subcategory::create($request->validated());

        return Redirect::route('subcategories.index')->with('success', 'Subcategory has been created!');
    }

    public function edit(Subcategory $subcategory)
    {
        return view('subcategories.edit', [
            'subcategory' => $subcategory,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSubcategoryRequest $request, Subcategory $subcategory)
    {
        $subcategory->update($request->validated());

        return Redirect::route('subcategories.index')->with('success', 'Subcategory has been updated!');
    }

    public function destroy(Subcategory $subcategory)
    {
        if ($subcategory->products()->exists()) {
            return Redirect::back()->with('error', 'Cannot delete subcategory because it has related products.');
        }

        $subcategory->delete();

        return Redirect::route('subcategories.index')->with('success', 'Subcategory has been deleted!');
    }
}
