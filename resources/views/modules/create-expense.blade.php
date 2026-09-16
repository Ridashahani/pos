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
                    <form class="compact-form" onsubmit="return false;">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" class="form-control" value="2026-09-15" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="branch">Branch <span class="text-danger">*</span></label>
                                <select id="branch" class="form-control" required>
                                    <option selected>Saddar Main Branch</option>
                                    <option>Commercial Market Branch</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="category">Category <span class="text-danger">*</span></label>
                                <select id="category" class="form-control" required>
                                    <option selected>Rent</option>
                                    <option>Utilities</option>
                                    <option>Staff Salary</option>
                                    <option>Marketing</option>
                                    <option>Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" id="amount" class="form-control" placeholder="0.00" min="0" step="0.01" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="note">Note</label>
                                <textarea id="note" class="form-control" rows="4" placeholder="Add expense details"></textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-save mr-2">
                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save Expense
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-cancel">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection