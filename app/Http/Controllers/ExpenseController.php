<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Branch;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::search(request('search'))
        ->with('branch')
        ->latest()
        ->paginate(4);

        return view('expense.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches=Branch::orderBy('name')->get();
        return view('expense.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        Expense::create($request->validated());
        return redirect()->route('expenses.index')->with('success','Expense Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $branches = Branch::orderBy('name')->get();

        return view('expense.edit', compact('expense', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Expense Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense Deleted Successfully');    
    }
}
