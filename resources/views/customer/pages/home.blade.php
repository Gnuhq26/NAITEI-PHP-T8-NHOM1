@extends('customer.layouts.app')

@section('title', __('Home') . ' - Furniro')

@section('hero')
<section class="hero-section home-hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">{{ __('Welcome to Furniro') }}</h1>
            <p class="hero-subtitle">{{ __('Discover beautiful furniture that transforms your space into a home') }}</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $featuredCategories->count() }}+</span>
                    <span class="stat-label">{{ __('Categories') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">{{ __('Products') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label">{{ __('Happy Customers') }}</span>
                </div>
            </div>
            <div class="hero-actions">
                <a href="{{ route('customer.categories') }}" class="btn-outline">
                    <i class="fas fa-shopping-bag"></i>
                    {{ __('Shop Now') }}
                </a>
                <a href="{{ route('customer.about') }}" class="btn-outline">
                    {{ __('Learn More') }}
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('storage/home/hero-furniture.jpg') }}" alt="{{ __('Beautiful Furniture') }}" class="hero-img">
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="home-wrapper">
    <!-- Features Section -->
    <section class="features-section">
        <div class="section-card">
            <div class="section-header">
                <h2>{{ __('Why Choose Furniro?') }}</h2>
                <p>{{ __('We provide the best furniture experience with quality products and excellent service') }}</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>{{ __('Free Delivery') }}</h3>
                    <p>{{ __('Free shipping on orders over 2,000,000 VND') }}</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>{{ __('Premium Quality') }}</h3>
                    <p>{{ __('High-quality materials and craftsmanship') }}</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>{{ __('24/7 Support') }}</h3>
                    <p>{{ __('Always here to help with your furniture needs') }}</p>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3>{{ __('Easy Returns') }}</h3>
                    <p>{{ __('30-day return policy for your peace of mind') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="section-card">
            <div class="section-header">
                <h2>{{ __('Browse by Category') }}</h2>
                <p>{{ __('Find the perfect furniture for every room in your home') }}</p>
            </div>
            
            <div class="categories-grid">
                @foreach($featuredCategories as $category)
                <div class="category-card">
                    <a href="{{ route('customer.products', $category->category_id) }}" class="category-link">
                        <div class="category-image">
                            <img src="{{ asset($category->image ?? 'storage/images/categories/default.jpg') }}" 
                                 alt="{{ $category->name }}" 
                                 class="category-img">
                            <div class="category-overlay">
                                <span class="product-count">{{ $category->products_count }} {{ __('Products') }}</span>
                            </div>
                        </div>
                        <div class="category-info">
                            <h3>{{ $category->name }}</h3>
                            <p>{{ Str::limit($category->description, 60) }}</p>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
            
            <div class="section-footer">
                <a href="{{ route('customer.categories') }}" class="btn-primary">
                    {{ __('View All Categories') }}
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="section-card cta-card">
            <div class="cta-content">
                <div class="cta-text">
                    <h2>{{ __('Ready to Transform Your Home?') }}</h2>
                    <p>{{ __('Browse our complete collection and find the perfect furniture for your space. Quality, style, and comfort guaranteed.') }}</p>
                </div>
                <div class="cta-actions">
                    <a href="{{ route('customer.categories') }}" class="btn-primary btn-large">
                        <i class="fas fa-shopping-bag"></i>
                        {{ __('Start Shopping') }}
                    </a>
                </div>
            </div>
            <div class="cta-image">
                <img src="{{ asset('storage/home/cta-furniture.jpg') }}" alt="{{ __('Transform Your Home') }}" class="cta-img">
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
/* Home Hero Section */
.home-hero {
    background: linear-gradient(135deg, #B88E2F 0%, #D4A574 100%);
    min-height: 60vh;
    position: relative;
    overflow: hidden;
}

.home-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.home-hero .hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 20px;
    position: relative;
    z-index: 2;
}

.hero-text {
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 20px;
    line-height: 1.2;
    text-align: center;
}

.hero-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 40px;
    justify-content: center;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
}

.stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
}

.hero-actions {
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: center;
}

.btn-large {
    padding: 15px 30px;
    font-size: 1.1rem;
}

.btn-outline {
    background: transparent;
    color: #fff;
    border: 2px solid #fff;
    padding: 15px 30px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-outline:hover {
    background: #fff;
    color: #B88E2F;
}

.hero-image {
    position: relative;
}

.hero-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

/* Home Wrapper */
.home-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Section Headers */
.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.section-header p {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

/* Features Section */
.features-section {
    margin-bottom: 80px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.feature-item {
    text-align: center;
    padding: 30px 20px;
    border-radius: 12px;
    background: #f9f9f9;
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: #B88E2F;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.feature-icon i {
    font-size: 2rem;
    color: #fff;
}

.feature-item h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.feature-item p {
    color: #666;
    line-height: 1.6;
}

/* Categories Section */
.categories-section {
    margin-bottom: 80px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.category-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.category-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.category-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.category-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.category-card:hover .category-img {
    transform: scale(1.05);
}

.category-overlay {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(184, 142, 47, 0.9);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.category-info {
    padding: 20px;
}

.category-info h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.category-info p {
    color: #666;
    line-height: 1.5;
}

/* CTA Section */
.cta-section {
    margin-bottom: 40px;
}

.cta-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
}

.cta-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    align-items: center;
}

.cta-text h2 {
    font-size: 2.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.cta-text p {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 25px;
}

.cta-image {
    text-align: center;
}

.cta-img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 5px solid #B88E2F;
}

/* Section Footer */
.section-footer {
    text-align: center;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .home-hero .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 40px;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .hero-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .features-grid,
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .section-header h2 {
        font-size: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endpush
@endsection
