@php
$editing = isset($product) && $product;
$field = fn ($name, $default = '') => old($name, $editing ? ($product->{$name} ?? $default) : $default);
$variationTypes = $field('variation_types', []);
$selectedVariationIds = old('variation_ids', $editing ? ($product->variation_ids ?: ($product->variation_id ? [$product->variation_id] : [])) : []);
$selectedVariationIds = is_array($selectedVariationIds) ? $selectedVariationIds : [$selectedVariationIds];
@endphp

<div class="container-fluid product-form-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $editing ? 'Edit Product' : 'Add Product' }}</h4>
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($formMethod !== 'POST') @method($formMethod) @endif
                <div class="form-section-heading">
                    <div>
                        <h5>Product information</h5>
                        <p>Set the details for this product.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12 mb-3">
                        <label class="font-weight-bold d-block mb-2">Category Type</label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="cat_type_accessories" name="product_category_type" value="accessories" class="custom-control-input" @checked(old('product_category_type', ($editing && ($product->imei || $product->model || $product->mobile_type)) ? 'mobile' : 'accessories') === 'accessories')>
                            <label class="custom-control-label" for="cat_type_accessories" style="cursor: pointer; font-size: 0.84rem; font-weight: 600;">Accessories</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline mr-4">
                            <input type="radio" id="cat_type_mobile" name="product_category_type" value="mobile" class="custom-control-input" @checked(old('product_category_type', ($editing && ($product->imei || $product->model || $product->mobile_type)) ? 'mobile' : 'accessories') === 'mobile')>
                            <label class="custom-control-label" for="cat_type_mobile" style="cursor: pointer; font-size: 0.84rem; font-weight: 600;">Mobile</label>
                        </div>
                    </div>

                    <div class="form-group col-md-6"><label>Product Name <span class="text-danger">*</span></label><input name="name" value="{{ $field('name') }}" class="form-control" placeholder="Enter Name" required></div>
                    <div class="form-group col-md-6"><label>Brand</label><select name="brand" class="form-control">
                            <option value="">Choose Brand</option>@foreach($brands as $brand)<option value="{{ $brand }}" @selected($field('brand') === $brand)>{{ $brand }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mobile-extra-fields form-group col-md-4"><label>IMEI</label><input name="imei" value="{{ $field('imei') }}" class="form-control" placeholder="Enter IMEI"></div>
                    <div class="mobile-extra-fields form-group col-md-4"><label>Model</label><input name="model" value="{{ $field('model') }}" class="form-control" placeholder="Enter Model"></div>
                    <div class="mobile-extra-fields form-group col-md-4"><label>Mobile Type</label><select name="mobile_type" class="form-control">
                            <option value="">Choose Mobile Type</option>
                            <option value="new" @selected($field('mobile_type')==='new')>New</option>
                            <option value="used" @selected($field('mobile_type')==='used')>Used</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6"><label>Multiple Images</label><input name="images[]" type="file" multiple accept="image/*" class="form-control-file"></div>
                    <div class="form-group col-md-6"><label>Note</label><textarea name="note" class="form-control" rows="0" placeholder="Enter Note">{{ $field('note') }}</textarea></div>
                </div>
                <div class="d-flex justify-content-start mt-3">
                    <button type="submit" class="btn btn-save mr-2">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> {{ $submitLabel }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-cancel">
                        <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .product-form-page {
        max-width: 1440px;
        padding-bottom: 24px;
        color: #24324a;
    }

    .product-form-page .form-card {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 8px 28px rgba(35, 54, 84, .08);
    }

    .product-form-page>.card {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 8px 28px rgba(35, 54, 84, .08);
    }

    .product-form-page>.card>.card-body {
        padding: 28px 30px;
    }

    .product-form-page .form-group {
        margin-bottom: 20px;
    }

    .product-form-page label {
        display: block;
        margin-bottom: 8px;
        color: #34435b;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .01em;
    }

    .product-form-page .form-control {
        height: 42px;
        border: 1px solid #d9e0ea;
        border-radius: 6px;
        color: #26364f;
        background: #fff;
        font-size: .84rem;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .product-form-page .form-control-file {
        width: 100%;
        height: 42px;
        padding: 5px 8px;
        border: 1px solid #d9e0ea;
        border-radius: 6px;
        color: #6d7b8f;
        background: #fff;
        font-size: .78rem;
        /* line-height: 1; */
        display: flex;
        align-items: center;
    }

    .product-form-page .form-control-file::file-selector-button,
    .product-form-page .form-control-file::-webkit-file-upload-button {
        height: 26px;
        padding: 0 10px;
        margin-right: 10px;
        border: 1px solid #cdd5e0;
        border-radius: 4px;
        background: #f1f3f8;
        color: #34435b;
        font-weight: 600;
        font-size: .72rem;
        line-height: 24px;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }

    .product-form-page .form-control-file::file-selector-button:hover,
    .product-form-page .form-control-file::-webkit-file-upload-button:hover {
        background: #e2e7f0;
        border-color: #bdc7d5;
    }

    .product-form-page .form-control:focus {
        border-color: #6474f5;
        box-shadow: 0 0 0 3px rgba(100, 116, 245, .12);
    }

    .product-form-page textarea.form-control {
        height: auto;
        min-height: 88px;
        resize: vertical;
    }

    .product-form-page .form-control::placeholder {
        color: #9aa6b6;
    }

    .product-form-page .input-group-text {
        border-color: #d9e0ea;
        background: #f3f5f8;
        color: #69778b;
    }

    .form-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 4px 0 22px;
    }

    .form-section-heading.section-divider {
        border-top: 1px solid #e7ebf1;
        padding-top: 24px;
        margin-top: 6px;
    }

    .form-section-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: #eef0ff;
        color: #5e6df2;
        font-size: .72rem;
        font-weight: 700;
    }

    .form-section-heading h5 {
        margin: 0 0 3px;
        color: #26364f;
        font-size: 1rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .form-section-heading p {
        margin: 0;
        color: #8a96a8;
        font-size: .76rem;
    }

    .mobile-extra-fields,
    .variation-only,
    .pricing-fields,
    .pricing-section-heading {
        display: none
    }

    .product-form-page.is-single .pricing-fields,
    .product-form-page.is-single .pricing-section-heading {
        display: flex
    }

    .product-form-page.is-variation .variation-only {
        display: block
    }

    .product-form-page.is-variation:not(.has-variation-selection) .variation-types-picker {
        display: none
    }

    .product-form-page.is-variation .variation-details {
        display: none
    }

    .product-form-page.is-variation.has-variation-types .variation-details,
    .product-form-page.is-variation.has-variation-types .pricing-section-heading,
    .product-form-page.is-variation.has-variation-types .pricing-fields {
        display: flex
    }

    .product-form-page.is-variation .single-pricing-field {
        display: none
    }

    .variation-label {
        display: none
    }

    .product-form-page.is-variation .single-label {
        display: none
    }

    .product-form-page.is-variation .variation-label {
        display: inline
    }

    .multi-picker {
        position: relative
    }

    .multi-picker-toggle {
        background: #fff;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #6c757d;
        cursor: pointer
    }

    .multi-picker-chevron {
        font-size: 12px;
        color: #adb5bd
    }

    .multi-picker-menu {
        display: none;
        position: absolute;
        z-index: 20;
        top: calc(100% + 3px);
        left: 0;
        right: 0;
        max-height: 190px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dce1e6;
        border-radius: 6px;
        box-shadow: 0 8px 20px rgba(35, 54, 84, .14);
        padding: 6px
    }

    .multi-picker.open .multi-picker-menu {
        display: block
    }

    .multi-picker-menu label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 10px;
        margin: 0;
        border-radius: 4px;
        cursor: pointer;
        font-size: .82rem
    }

    .multi-picker-menu label:hover {
        background: #f1f3f8
    }

    .multi-picker-menu input {
        margin: 0
    }

    .picker-values {
        display: none
    }

    .multi-picker-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 8px
    }

    .product-form-page .btn-primary {
        background: #6372f4;
        border-color: #6372f4;
        border-radius: 6px;
        padding: 10px 22px;
        font-weight: 600;
    }

    .product-form-page .btn-secondary,
    .product-form-page .btn-outline-primary {
        border-radius: 6px;
        padding: 10px 22px;
    }

    @media (max-width: 767px) {
        .product-form-page>.card>.card-body {
            padding: 20px 16px;
        }

        .form-section-heading p {
            display: none;
        }
    }
</style>
<script>

    // ── Mobile / Accessories toggle ──────────────────────────────────────────
    (function () {
        const mobileRadio = document.getElementById('cat_type_mobile');
        const accessoriesRadio = document.getElementById('cat_type_accessories');
        const mobileFields = document.querySelectorAll('.mobile-extra-fields');

        function toggleMobileFields() {
            const isMobile = mobileRadio && mobileRadio.checked;
            mobileFields.forEach(function (fieldGroup) {
                if (isMobile) {
                    fieldGroup.style.display = 'block';
                    const inputs = fieldGroup.querySelectorAll('input, select, textarea');
                    inputs.forEach(function(input) { input.disabled = false; });
                } else {
                    fieldGroup.style.display = 'none';
                    const inputs = fieldGroup.querySelectorAll('input, select, textarea');
                    inputs.forEach(function(input) { input.disabled = true; });
                }
            });
        }

        const categoryTypeRadios = document.querySelectorAll('input[name="product_category_type"]');
        categoryTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', toggleMobileFields);
            radio.addEventListener('click', toggleMobileFields);
        });

        toggleMobileFields();
        document.addEventListener('DOMContentLoaded', toggleMobileFields);
    }());
    // ─────────────────────────────────────────────────────────────────────────
    (function() {
        const page = document.querySelector('.product-form-page');
        if (!page) return;
        const currencySelect = page.querySelector('[name="currency"]');
        const currencyLabels = page.querySelectorAll('.currency-label');

        function syncCurrencyLabels() {
            if (currencySelect) {
                currencyLabels.forEach((label) => label.textContent = currencySelect.value);
            }
        }

        if (currencySelect) {
            currencySelect.addEventListener('change', syncCurrencyLabels);
            syncCurrencyLabels();
        }

        const barcodeScanner = document.getElementById('barcode_scanner');
        if (barcodeScanner) {
            barcodeScanner.addEventListener('change', function() {
                const codeField = document.getElementById('code');
                if (codeField) codeField.value = this.value;
            });
        }
    }());
</script>