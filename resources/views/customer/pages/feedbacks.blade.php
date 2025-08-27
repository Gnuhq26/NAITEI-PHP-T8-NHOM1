@extends('customer.layouts.app')

@section('title', __('Product Feedbacks') . ' - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ __('Product Feedbacks') }}</h1>
        <div class="breadcrumb">
            <a href="{{ route('customer.categories') }}">{{ __('Home') }}</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('customer.categories') }}">{{ __('Shop') }}</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('customer.products', $product->category_id) }}">{{ __($product->category->name) }}</a>
            <i class="fas fa-chevron-right"></i>
            <span>{{ __('Feedbacks') }}</span>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="feedback-container">
    <div class="product-info-section">
        <div class="product-summary">
            <img src="{{ asset($product->image ?? 'images/default-product.svg') }}" alt="{{ $product->name }}" class="product-image">
            <div class="product-details">
                <h2>{{ $product->name }}</h2>
                <p class="product-description">{{ $product->description }}</p>
                <p class="product-price">{{ number_format($product->price, 0, '.', ',') }} VND</p>
            </div>
        </div>
    </div>

    <!-- Feedback Stats -->
    <div class="feedback-stats">
        <div class="stats-item">
            <span class="stats-number">{{ $feedbacks->total() }}</span>
            <span class="stats-label">{{ __('Total Reviews') }}</span>
        </div>
        <div class="stats-item">
            <span class="stats-number">
                @if($feedbacks->total() > 0)
                {{-- average rating rounded to 1 decimal places --}}
                    {{ round($averageRating, 1) }}
                @else
                    0
                @endif
            </span>
            <span class="stats-label">{{ __('Average Rating') }}</span>
        </div>
    </div>

    <!-- Feedback Form -->
    @auth
    <div class="feedback-form-container" id="feedbackForm" style="display: none;">
        <div class="feedback-form">
            <h3 id="formTitle">{{ __('Write Your Review') }}</h3>
            <form action="{{ route('customer.feedbacks.store', $product->product_id) }}" method="POST">
                @csrf
                <input type="hidden" name="feedback_id" id="feedbackId" value="">
                
                <div class="form-group">
                    <label for="rating">{{ __('Rating') }}</label>
                    <div class="rating-input">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" required>
                            <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>
                    @error('rating')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="comment">{{ __('Your Review') }}</label>
                    <textarea name="comment" id="comment" rows="4" placeholder="{{ __('Share your thoughts about this product...') }}" required>{{ old('comment') }}</textarea>
                    @error('comment')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelFeedback">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-primary" id="submitButton">{{ __('Submit Review') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <!-- Feedbacks List -->
    <div class="feedbacks-section">
        <div class="feedbacks-header">
            <h3>{{ __('Customer Reviews') }}</h3>
            @auth
                <button class="btn-primary" id="toggleFeedbackForm">
                    <i class="fas fa-plus"></i>
                    {{ __('Write a Review') }}
                </button>
            @endauth
        </div>
        
        @if($feedbacks->count() > 0)
            <div class="feedbacks-list">
                @foreach($feedbacks as $feedback)
                <div class="feedback-item">
                    <div class="feedback-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-details">
                                <h4>{{ $feedback->user->name }}
                                    @if($feedback->created_at != $feedback->updated_at)
                                        <span class="edited-badge">({{ __('edited') }})</span>
                                    @endif
                                </h4>
                                <span class="feedback-date">{{ __($feedback->created_at->translatedFormat('M d, Y')) }}</span>
                            </div>
                        </div>
                        <div class="feedback-actions">
                            <div class="feedback-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $feedback->rating ? 'active' : '' }}"></i>
                                @endfor
                            </div>
                            @auth
                                @if($feedback->user_id == auth()->id())
                                    <div class="feedback-menu">
                                        <button class="menu-toggle" onclick="toggleMenu({{ $feedback->feedback_id }})">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="menu-dropdown" id="menu-{{ $feedback->feedback_id }}">
                                            <button class="menu-item" onclick="editFeedback({{ $feedback->feedback_id }}, {{ json_encode($feedback->comment) }}, {{ $feedback->rating }})">
                                                <i class="fas fa-edit"></i>
                                                {{ __('Edit') }}
                                            </button>
                                            <button class="menu-item delete" onclick="deleteFeedback({{ $feedback->feedback_id }})">
                                                <i class="fas fa-trash"></i>
                                                {{ __('Delete') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="feedback-content">
                        <p>{{ $feedback->comment }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $feedbacks->links() }}
            </div>
        @else
            <div class="no-feedbacks">
                <i class="fas fa-comments"></i>
                <h4>{{ __('No reviews yet') }}</h4>
                <p>{{ __('Be the first to review this product!') }}</p>
            </div>
        @endif
    </div>
</div>

@include('customer.components.modals', [
    'modalId' => 'deleteModal',
    'title' => 'Confirm Delete',
    'message' => 'Are you sure you want to delete this feedback?',
    'confirmText' => 'Delete',
    'cancelText' => 'Cancel'
])
@endsection

@push('styles')
<style>
.feedback-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.product-info-section {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.product-summary {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

.product-image {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.product-details h2 {
    font-size: 24px;
    color: #3A3A3A;
    margin-bottom: 10px;
}

.product-description {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.6;
}

.product-price {
    font-size: 20px;
    font-weight: 600;
    color: #B88E2F;
}

.feedback-stats {
    display: flex;
    gap: 30px;
    justify-content: center;
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.stats-item {
    text-align: center;
}

.stats-number {
    display: block;
    font-size: 32px;
    font-weight: 700;
    color: #B88E2F;
    margin-bottom: 5px;
}

.stats-label {
    color: #666;
    font-size: 14px;
}

.feedbacks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.feedbacks-header h3 {
    margin: 0;
    color: #3A3A3A;
}

.login-message {
    color: #666;
    margin: 0;
}

.login-message a {
    color: #B88E2F;
    text-decoration: none;
}

.btn-primary {
    background: #B88E2F;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #A67F2A;
}

.btn-secondary {
    background: #ccc;
    color: #333;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-secondary:hover {
    background: #bbb;
}

.feedback-form-container {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.feedback-form h3 {
    margin-bottom: 25px;
    color: #3A3A3A;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #3A3A3A;
}

.rating-input {
    display: flex;
    gap: 5px;
    margin-bottom: 5px;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 24px;
    color: #ddd;
    transition: color 0.2s;
}

textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
}

textarea:focus {
    outline: none;
    border-color: #B88E2F;
}

.error-message {
    color: #e74c3c;
    font-size: 14px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.feedbacks-section {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.feedbacks-section h3 {
    margin-bottom: 25px;
    color: #3A3A3A;
}

.feedback-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.feedback-menu {
    position: relative;
}

.menu-toggle {
    background: none;
    border: none;
    color: #666;
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: all 0.3s;
}

.menu-toggle:hover {
    background: #f8f9fa;
    color: #333;
}

.menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 120px;
    z-index: 1000;
    display: none;
}

.menu-dropdown.show {
    display: block;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 10px 15px;
    border: none;
    background: none;
    text-align: left;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    transition: background 0.3s;
}

.menu-item:hover {
    background: #f8f9fa;
}

.menu-item.delete {
    color: #dc3545;
}

.menu-item.delete:hover {
    background: #f8f9fa;
    color: #c82333;
}

.menu-item:first-child {
    border-radius: 6px 6px 0 0;
}

.menu-item:last-child {
    border-radius: 0 0 6px 6px;
}

.btn-edit {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-edit:hover {
    background: #e9ecef;
    color: #333;
}

.edited-badge {
    font-size: 12px;
    color: #888;
    font-weight: normal;
    font-style: italic;
}

.feedback-item {
    padding: 25px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.feedback-item:last-child {
    border-bottom: none;
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    font-size: 40px;
    color: #B88E2F;
}

.user-details h4 {
    margin: 0;
    color: #3A3A3A;
    font-size: 16px;
}

.feedback-date {
    color: #666;
    font-size: 14px;
}

.feedback-rating {
    display: flex;
    gap: 2px;
}

.feedback-rating .fas.fa-star {
    color: #ddd;
    font-size: 16px;
}

.feedback-rating .fas.fa-star.active {
    color: #FFD700;
}

.feedback-content {
    color: #666;
    line-height: 1.6;
}

.no-feedbacks {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-feedbacks i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-feedbacks h4 {
    margin-bottom: 10px;
    color: #3A3A3A;
}

.pagination-wrapper {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .product-summary {
        flex-direction: column;
        gap: 20px;
    }
    
    .product-image {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
    }
    
    .feedback-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .feedback-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
@endpush

@section('scripts')
<script>
const translations = {
    edit_review_title: "{{ __('Edit Your Review') }}",
    update_review_button: "{{ __('Update Review') }}",
    add_review_title: "{{ __('Add Your Review') }}",
    submit_review_button: "{{ __('Submit Review') }}",
};

function t(key) {
    return translations[key] || key;
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFeedbackForm');
    const feedbackForm = document.getElementById('feedbackForm');
    const cancelButton = document.getElementById('cancelFeedback');
    const formTitle = document.getElementById('formTitle');
    const submitButton = document.getElementById('submitButton');
    const feedbackIdInput = document.getElementById('feedbackId');
    const commentTextarea = document.getElementById('comment');
    
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            // Reset form for new feedback
            resetForm();
            showForm();
        });
    }
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            hideForm();
        });
    }
    
    // Rating interaction
    const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    ratingLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
        
        label.addEventListener('click', function() {
            ratingInputs[index].checked = true;
        });
    });
    
    document.querySelector('.rating-input').addEventListener('mouseleave', function() {
        updateStarDisplay();
    });
    
    function highlightStars(rating) {
        ratingLabels.forEach((label, i) => {
            if (i < rating) {
                label.style.color = '#FFD700';
            } else {
                label.style.color = '#ddd';
            }
        });
    }
    
    function updateStarDisplay() {
        const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
        if (checkedInput) {
            const checkedIndex = Array.from(ratingInputs).indexOf(checkedInput);
            highlightStars(checkedIndex + 1);
        } else {
            resetStars();
        }
    }
    
    function resetStars() {
        ratingLabels.forEach(label => {
            label.style.color = '#ddd';
        });
    }
    
    function showForm() {
        feedbackForm.style.display = 'block';
        if (toggleButton) {
            toggleButton.style.display = 'none';
        }
        updateStarDisplay();
    }
    
    function hideForm() {
        feedbackForm.style.display = 'none';
        if (toggleButton) {
            toggleButton.style.display = 'inline-flex';
        }
    }
    
    function resetForm() {
        formTitle.textContent = t('add_review_title');
        submitButton.textContent = t('submit_review_button');
        feedbackIdInput.value = '';
        commentTextarea.value = '';
        ratingInputs.forEach(input => input.checked = false);
        resetStars();
    }
    
    window.editFeedback = function(feedbackId, comment, rating) {
        formTitle.textContent = t('edit_review_title');
        submitButton.textContent = t('update_review_button');
        feedbackIdInput.value = feedbackId;
        commentTextarea.value = comment;
        
        ratingInputs.forEach(input => input.checked = false);
        if (rating >= 1 && rating <= 5) {
            ratingInputs[rating - 1].checked = true;
        }
        
        showForm();
        closeAllMenus();
    };
    
    // toggling menu
    window.toggleMenu = function(feedbackId) {
        const menu = document.getElementById('menu-' + feedbackId);
        const isCurrentlyVisible = menu.classList.contains('show');
        
        closeAllMenus();
        
        if (!isCurrentlyVisible) {
            menu.classList.add('show');
        }
    };
    
    // deleting feedback function
    window.deleteFeedback = function(feedbackId) {
        document.getElementById('deleteModal').style.display = 'flex';
        
        // confirm button
        const confirmBtn = document.getElementById('deleteModal_confirm');

        // delete route
        const deleteRoute = "{{ route('customer.feedbacks.destroy', ':id') }}";
        confirmBtn.onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteRoute.replace(':id', feedbackId);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);
            
            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        };
        
        closeAllMenus();
    };
    
    function closeAllMenus() {
        document.querySelectorAll('.menu-dropdown').forEach(menu => {
            menu.classList.remove('show');
        });
    }
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.feedback-menu')) {
            closeAllMenus();
        }
    });
    
    updateStarDisplay();
});
</script>
@endsection
