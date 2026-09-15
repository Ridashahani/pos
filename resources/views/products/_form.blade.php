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
    <div class="card"><div class="card-body">
        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($formMethod !== 'POST') @method($formMethod) @endif
            <div class="row">
                <div class="form-group col-md-6"><label>Product Name <span class="text-danger">*</span></label><input name="name" value="{{ $field('name') }}" class="form-control" placeholder="Enter Name" required></div>
                <div class="form-group col-md-3"><label>Product Code / SKU/Barcode <span class="text-danger">*</span></label><input name="code" id="code" value="{{ $field('code') }}" class="form-control" placeholder="Enter Code"></div>
                <div class="form-group col-md-3"><label>Multiple Images</label><input name="images[]" type="file" multiple accept="image/*" class="form-control-file pt-2"></div>
                <div class="form-group col-md-3"><label>Barcode Scanner</label><input id="barcode_scanner" class="form-control" placeholder="Scan barcode" autocomplete="off"></div>
                <div class="form-group col-md-4"><label>Product Category <span class="text-danger">*</span></label><select name="category_id" class="form-control" required><option value="">Choose Product Category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($field('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="form-group col-md-4"><label>Brand</label><input name="brand" value="{{ $field('brand') }}" class="form-control" placeholder="Choose Brand"></div>
                <div class="form-group col-md-4"><label>Warehouse</label><input name="warehouse" value="{{ $field('warehouse') }}" class="form-control" placeholder="Choose Warehouse"></div>
                <div class="form-group col-md-4"><label>Barcode Symbology</label><select name="barcode_symbology" class="form-control"><option value="">Choose Barcode Symbology</option>@foreach(['CODE128','EAN13','UPC','CODE39'] as $option)<option @selected($field('barcode_symbology') === $option)>{{ $option }}</option>@endforeach</select></div>
                <div class="form-group col-md-4"><label>Product Unit</label><input name="product_unit" value="{{ $field('product_unit') }}" class="form-control" placeholder="Choose Product Unit"></div>
                <div class="form-group col-md-4"><label>Supplier</label><select name="supplier_id" class="form-control"><option value="">Choose Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected($field('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></div>
                <div class="form-group col-md-4"><label>Sale Unit</label><input name="sale_unit" value="{{ $field('sale_unit') }}" class="form-control" placeholder="Choose Sale Unit"></div>
                <div class="form-group col-md-4"><label>Purchase Unit</label><input name="purchase_unit" value="{{ $field('purchase_unit') }}" class="form-control" placeholder="Choose Purchase Unit"></div>
                <div class="form-group col-md-4"><label>Status <span class="text-danger">*</span></label><select name="status" class="form-control"><option value="received" @selected($field('status', 'received') === 'received')>Received</option><option value="pending" @selected($field('status') === 'pending')>Pending</option><option value="ordered" @selected($field('status') === 'ordered')>Ordered</option></select></div>
                <div class="form-group col-md-4"><label>Quantity Limitation</label><input name="quantity_limit" type="number" min="0" value="{{ $field('quantity_limit') }}" class="form-control" placeholder="Enter Quantity Limitation"></div>
                <div class="form-group col-md-4"><label>Expiry Date</label><input name="expire_date" type="date" value="{{ $field('expire_date') }}" class="form-control"></div>
                <div class="form-group col-md-8"><label>Note</label><textarea name="note" class="form-control" rows="2" placeholder="Enter Note">{{ $field('note') }}</textarea></div>
            </div>
            <hr>
            <div class="product-type-row row">
                <div class="form-group col-md-4"><label>Product Type <span class="text-danger">*</span></label><select name="product_type" id="product_type" class="form-control" required><option value="" @selected($field('product_type') === '')>Choose Product Type</option><option value="single" @selected($field('product_type') === 'single')>Single</option><option value="variation" @selected($field('product_type') === 'variation')>Variation</option></select></div>
                <div class="variation-only form-group col-md-4"><label>Variations <span class="text-danger">*</span></label><div class="multi-picker" data-picker="variations"><button type="button" class="form-control multi-picker-toggle"><span class="multi-picker-label">Choose Variation</span><span class="multi-picker-chevron">&#9662;</span></button><div class="multi-picker-menu">@foreach($variations as $variation)<label><input type="checkbox" value="{{ $variation->id }}" data-types="{{ json_encode($variation->types) }}" @checked(in_array($variation->id, $selectedVariationIds))>{{ $variation->name }}</label>@endforeach</div></div><select name="variation_ids[]" id="variation_ids" class="picker-values" multiple></select></div>
                <div class="variation-only variation-types-picker form-group col-md-4"><label>Variation Types <span class="text-danger">*</span></label><div class="multi-picker" data-picker="types"><button type="button" class="form-control multi-picker-toggle"><span class="multi-picker-label">Choose Variation Types</span><span class="multi-picker-chevron">&#9662;</span></button><div class="multi-picker-menu" id="variation-types-menu"></div></div><select name="variation_types[]" id="variation_types" class="picker-values" multiple></select></div>
            </div>
            <hr class="variation-only variation-details">
            <div class="variation-only variation-details row">
                <div class="form-group col-md-3"><label>Variation Type</label><input name="variation_type" value="{{ $field('variation_type') }}" class="form-control" placeholder="XXL"></div>
                <div class="form-group col-md-3"><label>Product Cost <span class="text-danger">*</span></label><input name="product_cost" value="{{ $field('product_cost', $field('buying_price')) }}" type="number" step="0.01" class="form-control variation-input"></div>
                <div class="form-group col-md-3"><label>SKU/Barcode <span class="text-danger">*</span></label><input value="{{ $field('code') }}" class="form-control" readonly></div>
                <div class="form-group col-md-3"><label>Product Price <span class="text-danger">*</span></label><input name="product_price" value="{{ $field('product_price', $field('selling_price')) }}" type="number" step="0.01" class="form-control variation-input"></div>
            </div>
            <div class="pricing-fields row">
                <div class="form-group col-md-3"><label>Product Cost <span class="text-danger">*</span></label><div class="input-group"><input name="single_product_cost" value="{{ $field('product_cost', $field('buying_price')) }}" type="number" step="0.01" class="form-control single-input"><div class="input-group-append"><span class="input-group-text">$</span></div></div></div>
                <div class="form-group col-md-3"><label><span class="single-label">Product Retail Price</span><span class="variation-label">Product Price</span> <span class="text-danger">*</span></label><div class="input-group"><input name="single_product_price" value="{{ $field('product_price', $field('selling_price')) }}" type="number" step="0.01" class="form-control single-input"><div class="input-group-append"><span class="input-group-text">$</span></div></div></div>
                <div class="form-group col-md-3"><label>Product Wholesale Price</label><div class="input-group"><input name="wholesale_price" value="{{ $field('wholesale_price') }}" type="number" step="0.01" class="form-control"><div class="input-group-append"><span class="input-group-text">$</span></div></div></div>
                <div class="form-group col-md-3"><label>Product Special/Offer Price</label><div class="input-group"><input name="special_price" value="{{ $field('special_price') }}" type="number" step="0.01" class="form-control"><div class="input-group-append"><span class="input-group-text">$</span></div></div></div>
                <div class="form-group col-md-3"><label>Stock Alert</label><input name="stock_alert" value="{{ $field('stock_alert', 0) }}" type="number" min="0" class="form-control"></div>
                <div class="form-group col-md-3"><label>Order Tax</label><div class="input-group"><input name="order_tax" value="{{ $field('order_tax', 0) }}" type="number" min="0" step="0.01" class="form-control"><div class="input-group-append"><span class="input-group-text">%</span></div></div></div>
                <div class="form-group col-md-3"><label>Tax Type <span class="text-danger">*</span></label><select name="tax_type" class="form-control"><option value="">Choose Tax Type</option><option @selected($field('tax_type') === 'exclusive') value="exclusive">Exclusive</option><option @selected($field('tax_type') === 'inclusive') value="inclusive">Inclusive</option></select></div>
                <div class="form-group col-md-3"><label>Add Product Quantity <span class="text-danger">*</span></label><input name="add_product_quantity" value="{{ $field('add_product_quantity', $field('stock', 0)) }}" type="number" min="0" class="form-control"></div>
            </div>
            <input type="hidden" name="buying_price" id="buying_price" value="{{ $field('buying_price', 0) }}"><input type="hidden" name="selling_price" id="selling_price" value="{{ $field('selling_price', 0) }}"><input type="hidden" name="stock" id="stock" value="{{ $field('stock', 0) }}">
            <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-primary mr-2">{{ $submitLabel }}</button><a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
<style>.product-form-page .card{border-radius:6px}.product-form-page label{font-size:.82rem;color:#495057}.product-form-page .form-control{height:38px;border-color:#dce1e6;font-size:.82rem}.product-form-page textarea.form-control{height:auto}.variation-only,.pricing-fields{display:none}.product-form-page.is-single .pricing-fields{display:flex}.product-form-page.is-variation .variation-only{display:block}.product-form-page.is-variation hr.variation-only{display:block}.product-form-page.is-variation:not(.has-variation-selection) .variation-types-picker{display:none}.product-form-page.is-variation .variation-details{display:none}.product-form-page.is-variation.has-variation-types .variation-details,.product-form-page.is-variation.has-variation-types .pricing-fields{display:flex}.variation-label{display:none}.product-form-page.is-variation .single-label{display:none}.product-form-page.is-variation .variation-label{display:inline}.multi-picker{position:relative}.multi-picker-toggle{background:#fff;text-align:left;display:flex;align-items:center;justify-content:space-between;color:#6c757d;cursor:pointer}.multi-picker-chevron{font-size:12px;color:#adb5bd}.multi-picker-menu{display:none;position:absolute;z-index:20;top:calc(100% + 3px);left:0;right:0;max-height:190px;overflow-y:auto;background:#fff;border:1px solid #dce1e6;border-radius:4px;box-shadow:0 4px 12px rgba(0,0,0,.12);padding:6px}.multi-picker.open .multi-picker-menu{display:block}.multi-picker-menu label{display:flex;align-items:center;gap:8px;padding:8px 10px;margin:0;border-radius:3px;cursor:pointer;font-size:.82rem}.multi-picker-menu label:hover{background:#f1f3f5}.multi-picker-menu input{margin:0}.picker-values{display:none}.multi-picker-label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;padding-right:8px}</style>
<script>
(function () {
    const page = document.querySelector('.product-form-page');
    const form = page.querySelector('form');
    const type = document.getElementById('product_type');
    const variationPicker = document.querySelector('[data-picker="variations"]');
    const variationSelect = document.getElementById('variation_ids');
    const typeSelect = document.getElementById('variation_types');
    const typePicker = document.querySelector('[data-picker="types"]');
    const typeMenu = document.getElementById('variation-types-menu');
    const selectedTypes = @json(is_array($variationTypes) ? $variationTypes : [$variationTypes]);
    const variationFields = page.querySelectorAll('.variation-only input:not(.picker-values), .variation-only select:not(.picker-values)');
    const singleFields = page.querySelectorAll('.single-input');

    function setMode() {
        const isVariation = type.value === 'variation';
        const isSingle = type.value === 'single';
        const hasTypes = typeMenu.querySelectorAll('input[type="checkbox"]:checked').length > 0;
        page.classList.toggle('is-variation', isVariation);
        page.classList.toggle('is-single', isSingle);
        page.classList.toggle('has-variation-types', hasTypes);
        variationFields.forEach((field) => field.disabled = !isVariation);
        singleFields.forEach((field) => field.disabled = isVariation);
    }

    function selectedVariationChecks() {
        return Array.from(variationPicker.querySelectorAll('input[type="checkbox"]:checked'));
    }

    function updatePickerLabel(picker, values, placeholder) {
        picker.querySelector('.multi-picker-label').textContent = values.length ? values.join(', ') : placeholder;
    }

    function syncVariationPicker() {
        const checks = selectedVariationChecks();
        page.classList.toggle('has-variation-selection', checks.length > 0);
        variationSelect.innerHTML = '';
        checks.forEach((check) => variationSelect.add(new Option(check.parentElement.textContent.trim(), check.value, true, true)));
        updatePickerLabel(variationPicker, checks.map((check) => check.parentElement.textContent.trim()), 'Choose Variation');
    }

    function loadVariationTypes() {
        const types = selectedVariationChecks().reduce((allTypes, option) => {
            const optionTypes = option.dataset.types ? JSON.parse(option.dataset.types) : [];
            return allTypes.concat(optionTypes);
        }, []).filter((variationType, index, allTypes) => allTypes.indexOf(variationType) === index);
        const previouslySelected = selectedTypes.length ? selectedTypes : Array.from(typeSelect.selectedOptions).map((option) => option.value);
        typeMenu.innerHTML = '';
        typeSelect.innerHTML = '';
        types.forEach((variationType) => {
            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = variationType;
            checkbox.checked = previouslySelected.includes(variationType);
            label.append(checkbox, document.createTextNode(' ' + variationType));
            typeMenu.appendChild(label);
        });
        syncTypePicker();
    }

    function syncTypePicker() {
        const checks = Array.from(typeMenu.querySelectorAll('input[type="checkbox"]:checked'));
        typeSelect.innerHTML = '';
        checks.forEach((check) => typeSelect.add(new Option(check.value, check.value, true, true)));
        updatePickerLabel(typePicker, checks.map((check) => check.value), 'Choose Variation Types');
        page.classList.toggle('has-variation-types', checks.length > 0);
    }

    type.addEventListener('change', setMode);
    variationPicker.addEventListener('change', function () { syncVariationPicker(); loadVariationTypes(); setMode(); });
    typeMenu.addEventListener('change', function () { syncTypePicker(); setMode(); });
    page.querySelectorAll('.multi-picker-toggle').forEach((toggle) => toggle.addEventListener('click', function () {
        const picker = this.parentElement;
        page.querySelectorAll('.multi-picker.open').forEach((openPicker) => { if (openPicker !== picker) openPicker.classList.remove('open'); });
        picker.classList.toggle('open');
    }));
    document.addEventListener('click', (event) => { if (!event.target.closest('.multi-picker')) page.querySelectorAll('.multi-picker.open').forEach((picker) => picker.classList.remove('open')); });
    syncVariationPicker();
    loadVariationTypes();
    setMode();
    form.addEventListener('submit', function () {
        const isVariation = type.value === 'variation';
        const cost = page.querySelector(isVariation ? '[name="product_cost"]' : '[name="single_product_cost"]');
        const price = page.querySelector(isVariation ? '[name="product_price"]' : '[name="single_product_price"]');
        syncVariationPicker();
        syncTypePicker();
        document.getElementById('buying_price').value = cost.value;
        document.getElementById('selling_price').value = price.value;
        document.getElementById('stock').value = page.querySelector('[name="add_product_quantity"]').value;
    });
    document.getElementById('barcode_scanner').addEventListener('change', function () { document.getElementById('code').value = this.value; });
}());
</script>