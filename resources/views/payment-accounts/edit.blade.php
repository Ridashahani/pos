@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="card card-block card-stretch card-height">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Edit Payment Account</h4>
            <a href="{{ route('payment-accounts.index') }}" class="btn btn-light btn-sm">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('payment-accounts.update', $paymentAccount) }}" method="POST">
                @csrf
                @method('PUT')
                @include('payment-accounts.form', ['account' => $paymentAccount])
                <button type="submit" class="btn btn-primary mr-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
                </button>
                <a href="{{ route('payment-accounts.index') }}" class="btn btn-orange">
                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection