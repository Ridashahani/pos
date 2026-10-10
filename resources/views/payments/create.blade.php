@extends('dashboard.body.main')

@section('container')
<style>
    .payment-page-title {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.2;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h4 class="card-title payment-page-title mb-0">Add Payment Transaction</h4>
                    <a href="{{ route('payments.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" /> Back
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="type">Transaction Type <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="send" @selected(old('type', 'send') === 'send')>Send Money</option>
                                    <option value="withdrawal" @selected(old('type') === 'withdrawal')>Cash Withdrawal</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="payment_account_id">Payment Account <span class="text-danger">*</span></label>
                                <select id="payment_account_id" name="payment_account_id" class="form-control @error('payment_account_id') is-invalid @enderror" required>
                                    <option value="">Select account</option>
                                    @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('payment_account_id') == $account->id)>{{ $account->name }} (Rs {{ number_format($account->balance, 0) }})</option>
                                    @endforeach
                                </select>
                                @error('payment_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6 transaction-withdrawal-field d-none">
                                <label>Transaction ID</label>
                                <input class="form-control" value="Generated automatically after saving" readonly>
                            </div>
                            <div class="form-group col-md-6 send-money-field">
                                <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                                <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" class="form-control @error('customer_name') is-invalid @enderror" required>
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6 send-money-field">
                                <label for="customer_phone">Customer Phone</label>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="30" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" class="form-control @error('customer_phone') is-invalid @enderror">
                                @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6 send-money-field">
                                <label for="recipient_name">Recipient Name</label>
                                <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" class="form-control @error('recipient_name') is-invalid @enderror">
                                @error('recipient_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6 send-money-field">
                                <label for="recipient_phone">Recipient Phone</label>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="30" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone') }}" class="form-control @error('recipient_phone') is-invalid @enderror">
                                @error('recipient_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="commission">Service Charge / Commission <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" id="commission" name="commission" value="{{ old('commission', 0) }}" class="form-control @error('commission') is-invalid @enderror" required>
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2"><x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save</button>
                        <a href="{{ route('payments.index') }}" class="btn btn-orange"><x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const transactionType = document.getElementById('type');
        const withdrawalFields = document.querySelectorAll('.transaction-withdrawal-field');
        const sendMoneyFields = document.querySelectorAll('.send-money-field');
        const phoneFields = document.querySelectorAll('#customer_phone, #recipient_phone');

        phoneFields.forEach(function (field) {
            field.addEventListener('input', function () {
                field.value = field.value.replace(/[^0-9]/g, '');
            });
        });

        function updateTransactionFields() {
            const isWithdrawal = transactionType.value === 'withdrawal';
            withdrawalFields.forEach(function (field) {
                field.classList.toggle('d-none', !isWithdrawal);
            });

            sendMoneyFields.forEach(function (field) {
                field.classList.toggle('d-none', isWithdrawal);
                field.querySelectorAll('input').forEach(function (input) {
                    input.disabled = isWithdrawal;
                });
            });
        }

        transactionType.addEventListener('change', updateTransactionFields);
        updateTransactionFields();
    });
</script>
@endsection
