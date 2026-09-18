@extends('dashboard.body.main')

@section('container')
<style>
    .btn-pure-red {
        background-color: #ff0000 !important;
        border-color: #ff0000 !important;
        color: #fff !important;
    }

    .btn-pure-red:hover,
    .btn-pure-red:focus {
        background-color: #d90000 !important;
        border-color: #d90000 !important;
        color: #fff !important;
    }

    .compact-form .form-control {
        height: 38px;
        padding-bottom: 6px;
        padding-top: 6px;
    }

    .compact-form textarea.form-control {
        height: auto;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Add Expense</h4>
                    <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>
                <div class="card-body">
                    <form class="compact-form" action="{{ route('expenses.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" name="date" class="form-control" value="{{ old('date') }}" required>
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="branch">Branch <span class="text-danger">*</span></label>
                                <select id="branch" name="branch_id" class="form-control" required>
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="category">Category <span class="text-danger">*</span></label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="" {{ old('category') == '' ? 'selected' : '' }}>Select Category</option>
                                    <option value="Rent" {{ old('category') == 'Rent' ? 'selected' : '' }}>Rent</option>
                                    <option value="Utilities" {{ old('category') == 'Utilities' ? 'selected' : '' }}>Utilities</option>
                                    <option value="Staff Salary" {{ old('category') == 'Staff Salary' ? 'selected' : '' }}>Staff Salary</option>
                                    <option value="Marketing" {{ old('category') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" value="{{ old('amount') }}" placeholder="0.00" min="0" step="0.01" required>
                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Add expense details">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class=" btn btn-primary mr-2">
                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-orange">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection