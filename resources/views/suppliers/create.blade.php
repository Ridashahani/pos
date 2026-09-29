@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Create Suppliers</h4>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>

                <div class="card-body">
                    @include('suppliers.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection