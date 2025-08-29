@extends('customer.layouts.app')

@section('title', __('Categories') . ' - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ __('Categories') }}</h1>
    </div>
</section>
@endsection

@section('content')
<!-- Filter Bar -->
<div class="filter-bar">
    <div class="filter-left">
        <form method="GET" action="{{ route('customer.categories') }}" class="search-form">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('Search categories...') }}"
                       class="search-input">
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                <button type="submit" class="search-btn">{{ __('Search') }}</button>
            </div>
        </form>
    </div>
    
    <div class="filter-right">
        <span class="showing-text">
            @if(request('search'))
                {{ __('Found') }} {{ $categories->count() }} {{ __('categories for') }} "{{ request('search') }}"
            @else
                {{ __('Showing') }} 1-{{ $categories->count() }} {{ __('of') }} {{ $categories->count() }} {{ __('categories') }}
            @endif
        </span>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <label>{{ __('Show') }}</label>
            <select class="show-select">
                <option>16</option>
                <option>32</option>
                <option>48</option>
            </select>
            
            <label>{{ __('Sort by') }}</label>
            <select class="sort-select" onchange="this.form.submit()" form="sort-form">
                <option value="" {{ !request('sort') ? 'selected' : '' }}>{{ __('Default') }}</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('Name A-Z') }}</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>{{ __('Name Z-A') }}</option>
                <option value="products_asc" {{ request('sort') == 'products_asc' ? 'selected' : '' }}>{{ __('Products Count (Low-High)') }}</option>
                <option value="products_desc" {{ request('sort') == 'products_desc' ? 'selected' : '' }}>{{ __('Products Count (High-Low)') }}</option>
            </select>
            
            <form id="sort-form" method="GET" action="{{ route('customer.categories') }}" style="display: none;">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <input type="hidden" name="sort" id="sort-input">
            </form>
        </div>
    </div>
</div>

<!-- Categories Grid -->
<div class="categories-grid">
    @forelse($categories as $category)
    <div class="category-card">
        <div class="category-image">
            <img src="{{ asset($category->image ?? 'images/default-category.svg') }}" alt="{{ $category->name }}">
            <div class="category-overlay">
                <a href="{{ route('customer.products', $category->category_id) }}" class="view-products-btn">
                    {{ __('View Products') }}
                </a>
            </div>
        </div>
        <div class="category-info">
            <h3>{{ $category->name }}</h3>
            <p class="category-desc">{{ $category->description }}</p>
            <p class="products-count">{{ $category->products_count }} {{ __('products') }}</p>
        </div>
    </div>
    @empty
    <div class="no-categories">
        <p>{{ __('No categories available at the moment.') }}</p>
    </div>
    @endforelse
</div>

@if($categories->count() > 12)
<!-- Pagination -->
<div class="pagination-wrapper">
    <div class="pagination">
        <button class="page-btn">1</button>
        <button class="page-btn active">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">Next</button>
    </div>
</div>
@endif
<div style="margin-top: 20px; display: flex; justify-content: center;">
    {{ $categories->links('pagination.pagination') }}
</div>
@endsection

@push('styles')
<style>
/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(285px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.category-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.category-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.category-card:hover .category-image img {
    transform: scale(1.1);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.category-card:hover .category-overlay {
    opacity: 1;
}

.view-products-btn {
    background: #B88E2F;
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    transition: background 0.3s;
}

.view-products-btn:hover {
    background: #A67F2A;
}

.category-info {
    padding: 20px;
}

.category-info h3 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.category-desc {
    color: #666;
    font-size: 14px;
    margin-bottom: 12px;
    line-height: 1.5;
}

.products-count {
    color: #B88E2F;
    font-weight: 500;
    font-size: 14px;
}

.no-categories {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-size: 18px;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 40px;
}

.pagination {
    display: flex;
    gap: 8px;
}

.page-btn {
    padding: 12px 18px;
    border: 1px solid #ddd;
    background: #F9F1E7;
    color: #333;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.3s;
}

.page-btn:hover,
.page-btn.active {
    background: #B88E2F;
    color: white;
    border-color: #B88E2F;
}

/* Filter Bar */
.filter-bar {
    background: #F9F1E7;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    border-radius: 10px;
    margin: 20px 0;
}

.filter-left {
    display: flex;
    gap: 20px;
    align-items: center;
    flex: 1;
}

.search-form {
    flex: 1;
    max-width: 350px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 48px;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #666;
    z-index: 2;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 0 15px 0 40px;
    font-size: 14px;
    border-radius: 8px 0 0 8px;
    height: 100%;
    box-sizing: border-box;
}

.search-input::placeholder {
    color: #999;
}

.search-btn {
    background: #B88E2F;
    color: white;
    border: none;
    padding: 0 24px;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s;
    height: 100%;
    min-width: 85px;
}

.search-btn:hover {
    background: #A67F2A;
}

.filter-right {
    display: flex;
    gap: 20px;
    align-items: center;
}

.showing-text {
    color: #666;
    font-size: 14px;
}

.show-select, .sort-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
}
</style>
@endpush

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sort functionality
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const sortInput = document.getElementById('sort-input');
            if (sortInput) {
                sortInput.value = sortValue;
                document.getElementById('sort-form').submit();
            }
        });
    }

    // Search form auto-submit on Enter
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});
</script>
@endsection
