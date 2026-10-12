<div class="row product-grid">
    @forelse($products as $product)
        <div class="col-12 mb-2">
            <div class="pos-product-row">
                <div class="pos-product-thumb">
                    <img src="{{ \App\Models\Product::imageUrl($product->image) }}"
                        alt="{{ $product->name }}">
                    <span class="pos-stock-badge {{ $product->stock > 10 ? 'in' : 'low' }}"
                        title="Stock: {{ $product->stock }}">{{ $product->stock }}</span>
                </div>

                <div class="pos-product-info">
                    <h6 class="pos-product-name" title="{{ $product->name }}">{{ $product->name }}</h6>
                    <div class="pos-product-price text-primary">
                        {{ number_format($product->selling_price) }}
                    </div>
                </div>

                <form class="add-to-cart-form" onsubmit="addToCart(event)">
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="name" value="{{ $product->name }}">
                    <input type="hidden" name="price" value="{{ $product->selling_price }}">
                    <input type="hidden" name="code" value="{{ $product->code }}">
                    <input type="hidden" name="image"
                        value="{{ \App\Models\Product::imageUrl($product->image) }}">
                    <button type="submit" class="btn btn-primary btn-sm pos-add-btn" title="Add to cart">
                        <x-heroicon-o-plus class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <x-heroicon-o-information-circle class="w-6 h-6 mx-auto mb-2" />
                @if (request()->filled('search'))
                    No products found.
                @else
                    Search by product name or category to view products.
                @endif
            </div>
        </div>
    @endforelse
</div>

<div class="row mt-3">
    <div class="col-12 d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>