@php
$editing = isset($product) && $product;
$field = fn ($name, $default = '') => old($name, $editing ? ($product->{$name} ?? $default) : $default);
$variationTypes = $field('variation_types', []);
$selectedVariationIds = old('variation_ids', $editing ? ($product->variation_ids ?: ($product->variation_id ? [$product->variation_id] : [])) : []);
$selectedVariationIds = is_array($selectedVariationIds) ? $selectedVariationIds : [$selectedVariationIds];
$selectedCategoryId = (string) old('category_id', $editing ? ($product->category_id ?? '') : '');
if ($selectedCategoryId === '' && $categories->isNotEmpty()) {
    $selectedCategoryId = (string) $categories->first()->id;
}
$selectedSubcategoryId = (string) old('subcategory_id', $editing ? ($product->subcategory_id ?? '') : '');
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
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Product could not be saved:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <input type="hidden" name="category_id" id="selected_category_id" value="{{ $selectedCategoryId }}">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold d-block mb-2">Category Type</label>
                        @foreach($categories as $category)
                            <div class="custom-control custom-radio custom-control-inline mr-3">
                                <input type="radio" id="category_type_{{ $category->id }}" name="product_category_type" value="{{ $category->id }}" data-category="{{ $category->id }}" data-mobile-fields="{{ \Illuminate\Support\Str::slug($category->name) === 'mobile' ? '1' : '0' }}" class="custom-control-input" @checked($selectedCategoryId === (string) $category->id)>
                                <label class="custom-control-label" for="category_type_{{ $category->id }}" style="cursor: pointer; font-size: 0.84rem; font-weight: 600;">{{ $category->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Product Subcategory</label>
                        <select name="subcategory_id" id="subcategory_select" class="form-control" @disabled(!$selectedCategoryId)>
                            <option value="">{{ $selectedCategoryId ? 'Choose Product Subcategory' : 'Select a category first' }}</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" @selected($selectedSubcategoryId === (string) $subcategory->id && $selectedCategoryId === (string) $subcategory->category_id) @if((string) $subcategory->category_id !== $selectedCategoryId) hidden disabled @endif>{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                        @error('subcategory_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-6"><label>Product Name <span class="text-danger">*</span></label><input name="name" value="{{ $field('name') }}" class="form-control" placeholder="Enter Name" required></div>
                    <div class="form-group col-md-6"><label>Brand</label><select name="brand_id" class="form-control">
                            <option value="">Choose Brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected((string) $field('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mobile-extra-fields form-group col-md-6"><label>Model</label><input name="model" value="{{ $field('model') }}" class="form-control" placeholder="Enter Model"></div>
                    <div class="mobile-extra-fields form-group col-md-6"><label>Condition</label><select name="condition" class="form-control">
                            <option value="">Choose Condition</option>
                            <option value="new" @selected($field('condition')==='new')>New</option>
                            <option value="used" @selected($field('condition')==='used')>Used</option>
                        </select>
                    </div>
                   <div class="form-group col-md-6">
                        <label for="cost_price">Cost Price</label>
                        <input type="number" min="0" step="0.01" id="cost_price" name="cost_price" value="{{ $field('cost_price', 0) }}" class="form-control @error('cost_price') is-invalid @enderror" placeholder="Enter cost price">
                        @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="selling_price">Sale Price</label>
                        <input type="number" min="0" step="0.01" id="selling_price" name="selling_price" value="{{ $field('selling_price', 0) }}" class="form-control @error('selling_price') is-invalid @enderror" placeholder="Enter sale price">
                        @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    

                    <div class="form-group col-md-6">
                        <label>GST (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="gst_tax"
                            value="{{ $field('gst_tax', $defaultGst ?? 0) }}"
                            class="form-control @error('gst_tax') is-invalid @enderror"
                            placeholder="Enter GST">
                        @error('gst_tax')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Price after GST: <span id="price_including_gst">0.00</span></small>
                    </div>
                    

                    <div class="form-group col-md-6">
                        <label>Multiple Images</label>
                        <input name="images[]" type="file" multiple accept="image/*" class="form-control-file">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Note</label>
                        <input type="text" name="note" value="{{ $field('note') }}" class="form-control @error('note') is-invalid @enderror" placeholder="Enter Note">
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status</label>
                        <div class="custom-control custom-switch mt-2">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" class="custom-control-input" id="status" name="status" value="1"
                                @checked($field('status', true))>
                            <label class="custom-control-label" for="status">Active</label>
                        </div>
                    </div>
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

    .product-form-page .select2-container {
        width: 100% !important;
    }

    .product-form-page .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d9e0ea;
        border-radius: 6px;
    }

    .product-form-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 12px;
        line-height: 40px;
        color: #26364f;
        font-size: .84rem;
    }

    .product-form-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
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

    (function () {
        const categoryInput = document.getElementById('selected_category_id');
        const subcategorySelect = document.getElementById('subcategory_select');
        if (!categoryInput || !subcategorySelect) return;

        const $subcategorySelect = window.jQuery && window.jQuery.fn.select2
            ? window.jQuery(subcategorySelect).select2({
                width: '100%',
                placeholder: 'Choose Product Subcategory',
                allowClear: true,
                minimumResultsForSearch: 0,
                templateResult: function (option) {
                    if (option.element && option.element.hidden) return null;
                    return option.text;
                }
            })
            : null;

        const subcategoryOptions = Array.from(subcategorySelect.options).filter(function (option) {
            return option.value !== '';
        });
        const placeholder = subcategorySelect.options[0];

        function filterSubcategories(categoryId) {
            let visibleCount = 0;
            let categorySubcategoryCount = 0;
            let selectedOptionVisible = false;
            categoryInput.value = categoryId || '';

            subcategoryOptions.forEach(function (option) {
                const belongsToCategory = categoryId !== '' && option.dataset.category === categoryId;
                const matches = belongsToCategory;
                if (belongsToCategory) categorySubcategoryCount += 1;
                option.hidden = !matches;
                option.disabled = !matches;
                if (matches) visibleCount += 1;
                if (matches && option.selected) selectedOptionVisible = true;
            });

            if (!selectedOptionVisible) subcategorySelect.value = '';
            placeholder.textContent = !categoryId
                ? 'Select a category first'
                : categorySubcategoryCount === 0
                    ? 'No subcategories for this category'
                    : visibleCount === 0
                        ? 'No matching subcategories'
                        : 'Choose Product Subcategory';
            subcategorySelect.disabled = !categoryId || categorySubcategoryCount === 0;
            if ($subcategorySelect) $subcategorySelect.trigger('change.select2');
        }

        const categoryTypeRadios = document.querySelectorAll('input[name="product_category_type"]');
        categoryTypeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                filterSubcategories(radio.dataset.category);
            });
        });

        const selectedCategoryType = document.querySelector('input[name="product_category_type"]:checked');
        filterSubcategories(selectedCategoryType ? selectedCategoryType.dataset.category : categoryInput.value);
    }());

    // ── Mobile / Accessories toggle ──────────────────────────────────────────
    (function () {
        const categoryTypeRadios = document.querySelectorAll('input[name="product_category_type"]');
        const mobileFields = document.querySelectorAll('.mobile-extra-fields');

        function toggleMobileFields() {
            const selectedCategory = document.querySelector('input[name="product_category_type"]:checked');
            const isMobile = selectedCategory && selectedCategory.dataset.mobileFields === '1';
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

        categoryTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', toggleMobileFields);
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

    (function () {
        const priceInput = document.getElementById('selling_price');
        const taxInput = document.querySelector('[name="gst_tax"]');
        const totalOutput = document.getElementById('price_including_gst');
        if (!priceInput || !taxInput || !totalOutput) return;

        function updatePriceIncludingGst() {
            const price = Number(priceInput.value) || 0;
            const taxRate = Number(taxInput.value) || 0;
            totalOutput.textContent = (price * (1 + taxRate / 100)).toFixed(2);
        }

        priceInput.addEventListener('input', updatePriceIncludingGst);
        taxInput.addEventListener('input', updatePriceIncludingGst);
        updatePriceIncludingGst();
    }());
</script>