@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Edit Payment Transaction</h4>
                    <a href="{{ route('payments.index') }}" class="btn btn-light btn-sm"><x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.update', $payment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="type">Transaction Type <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="send" @selected(old('type', $payment->type) === 'send')>Send Money</option>
                                    <option value="withdrawal" @selected(old('type', $payment->type) === 'withdrawal')>Cash Withdrawal</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="payment_account_id">Payment Account <span class="text-danger">*</span></label>
                                <select id="payment_account_id" name="payment_account_id" class="form-control @error('payment_account_id') is-invalid @enderror" required>
                                    @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('payment_account_id', $payment->payment_account_id) == $account->id)>{{ $account->name }} (Rs {{ number_format($account->balance, 0) }})</option>
                                    @endforeach
                                </select>
                                @error('payment_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                                <input id="customer_name" name="customer_name" value="{{ old('customer_name', $payment->customer_name) }}" class="form-control @error('customer_name') is-invalid @enderror" required>
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="customer_phone">Customer Phone</label>
                                <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $payment->customer_phone) }}" class="form-control @error('customer_phone') is-invalid @enderror">
                                @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="recipient_name">Recipient Name</label>
                                <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $payment->recipient_name) }}" class="form-control @error('recipient_name') is-invalid @enderror">
                                @error('recipient_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="transaction_date">Transaction Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $payment->transaction_date->format('Y-m-d\TH:i')) }}" class="form-control @error('transaction_date') is-invalid @enderror" required>
                                @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" class="form-control @error('amount') is-invalid @enderror" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="commission">Service Charge / Commission <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" id="commission" name="commission" value="{{ old('commission', $payment->commission) }}" class="form-control @error('commission') is-invalid @enderror" required>
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                          
                            <div class="form-group col-md-12">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $payment->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2"><x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update</button>
                        <a href="{{ route('payments.index') }}" class="btn btn-orange"><x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection