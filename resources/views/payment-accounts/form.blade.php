@php($account = $account ?? null)
<div class="row">
    <div class="form-group col-md-6">
        <label for="name">Account Name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $account->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="type">Account Type <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
            @foreach (['jazzcash' => 'JazzCash', 'easypaisa' => 'Easypaisa', 'bank' => 'Bank'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $account->type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="account_number">Account Number</label>
        <input id="account_number" name="account_number" value="{{ old('account_number', $account->account_number ?? '') }}" class="form-control @error('account_number') is-invalid @enderror">
        @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="holder_name">Holder Name</label>
        <input id="holder_name" name="holder_name" value="{{ old('holder_name', $account->holder_name ?? '') }}" class="form-control @error('holder_name') is-invalid @enderror">
        @error('holder_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="opening_balance">Opening Balance <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', $account->opening_balance ?? 0) }}" class="form-control @error('opening_balance') is-invalid @enderror" required>
        @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $account->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $account->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>