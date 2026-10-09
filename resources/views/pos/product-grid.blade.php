<div class="row product-grid">
    @forelse($products as $product)
        <div class="col-lg-6 col-md-4 col-sm-6 mb-3">
            <div class="product-card h-100 d-flex flex-column">
                <div class="image-container">
                    <img src="{{ \App\Models\Product::imageUrl($product->image) }}"
                        class="product-image" alt="{{ $product->name }}">

                    <span class="badge position-absolute shadow-sm"
                        style="top: 12px; right: 12px; font-size: 0.75rem; padding: 0.5em 0.8em; {{ $product->stock > 10 ? 'background-color: #10b981; color: white;' : 'background-color: #ef4444; color: white;' }}">
                        Stock: {{ $product->stock }}
                    </span>
                </div>

                <div class="p-3 d-flex flex-column flex-grow-1">
                    <h6 class="font-weight-bold text-dark text-truncate mb-2" title="{{ $product->name }}"
                        style="font-size: 0.95rem;">
                        {{ $product->name }}
                    </h6>

                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <h5 class="text-primary font-weight-bolder mb-0" style="font-size: 1.1rem;">
                            {{ number_format($product->selling_price) }}
                        </h5>

                        <form class="add-to-cart-form" onsubmit="addToCart(event)">
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <input type="hidden" name="name" value="{{ $product->name }}">
                            <input type="hidden" name="price" value="{{ $product->selling_price }}">
                            <input type="hidden" name="code" value="{{ $product->code }}">
                            <input type="hidden" name="image"
                                value="{{ \App\Models\Product::imageUrl($product->image) }}">
                            <button type="submit"
                                class="btn btn-primary btn-sm rounded-pill px-1 text-center shadow-sm d-flex align-items-center">
                                <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            </button>
                        </form>
                    </div>
                </div>
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
