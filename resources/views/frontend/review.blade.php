<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Leave a Review - {{ $business->business_name }}</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Share your experience with {{ $business->business_name }} in {{ $business->city }}, {{ $business->state }}">
    <meta name="keywords" content="review, {{ $business->business_name }}, {{ $business->city }}, customer feedback">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Leave a Review - {{ $business->business_name }}">
    <meta property="og:description" content="Share your experience with {{ $business->business_name }}">
    <meta property="og:image" content="{{ $business->logo_url }}">
    <meta property="og:type" content="website">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')

    <style>
        .star-rating {
            display: flex;
            gap: 0.25rem;
        }
        
        .star-rating .star {
            font-size: 2rem;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .star-rating .star:hover,
        .star-rating .star.active {
            color: #fbbf24;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .suggestion-card {
            transition: all 0.2s ease;
        }
        
        .suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div id="app" class="min-h-screen py-8">
        <!-- Header -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="text-center">
                <div class="mb-6">
                    <img src="{{ $business->logo_url }}" alt="{{ $business->business_name }}" 
                         class="h-20 w-20 mx-auto rounded-full object-cover shadow-lg">
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $business->business_name }}</h1>
                <p class="text-gray-600 mb-4">{{ $business->full_address }}</p>
                
                @if($business->description)
                <p class="text-gray-600 max-w-lg mx-auto">{{ $business->description }}</p>
                @endif
            </div>
        </div>

        <!-- Review Statistics -->
        @if($reviewStats['total_reviews'] > 0)
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center">
                    <div class="flex items-center justify-center mb-2">
                        <div class="flex text-yellow-400 mr-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($reviewStats['average_rating']) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($reviewStats['average_rating'], 1) }}</span>
                    </div>
                    <p class="text-gray-600">Based on {{ $reviewStats['total_reviews'] }} reviews</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Review Form -->
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Share Your Experience</h2>
                        <p class="text-gray-600">Your feedback helps us serve you better</p>
                    </div>

                    <form id="reviewForm" method="POST" action="{{ route('review.store', $business->slug) }}">
                        @csrf
                        
                        <!-- Rating Section -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-4 text-center">
                                How would you rate your experience?
                            </label>
                            <div class="star-rating justify-center" id="starRating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="star fas fa-star" data-rating="{{ $i }}"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="" required>
                            <div id="ratingError" class="text-red-600 text-sm mt-2 hidden">Please select a rating</div>
                        </div>

                        <!-- Review Text Section -->
                        <div class="mb-6">
                            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">
                                Tell us about your experience
                            </label>
                            <textarea name="review_text" id="review_text" rows="5" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    placeholder="Share your thoughts about your experience..."
                                    required
                                    minlength="{{ $settings['min_review_length'] ?? 10 }}"
                                    maxlength="{{ $settings['max_review_length'] ?? 500 }}"></textarea>
                            <div class="text-sm text-gray-500 mt-1">
                                <span id="charCount">0</span> / {{ $settings['max_review_length'] ?? 500 }} characters
                            </div>
                        </div>

                        <!-- AI Suggestions (Hidden initially) -->
                        <div id="suggestionSection" class="mb-6 hidden">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Need inspiration?</h3>
                                <button type="button" id="getSuggestions" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-magic mr-1"></i> Get AI Suggestions
                                </button>
                            </div>
                            <div id="suggestionsList" class="space-y-3 hidden"></div>
                        </div>

                        <!-- Customer Information (Optional) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Your Name (Optional)
                                </label>
                                <input type="text" name="customer_name" id="customer_name" 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Your name">
                            </div>
                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email (Optional)
                                </label>
                                <input type="email" name="customer_email" id="customer_email" 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="your@email.com">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" id="submitBtn" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors inline-flex items-center">
                                <span id="submitText">Submit Review</span>
                                <div id="submitLoading" class="loading ml-2 hidden"></div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Reviews -->
        @if($recentReviews->count() > 0)
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Recent Reviews</h3>
            <div class="space-y-4">
                @foreach($recentReviews->take(5) as $review)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start space-x-4">
                        <img src="{{ $review->customer_avatar }}" alt="{{ $review->customer_name ?: 'Anonymous' }}" 
                             class="h-10 w-10 rounded-full">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">{{ $review->customer_name ?: 'Anonymous Customer' }}</h4>
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-gray-700">{{ $review->review_text }}</p>
                            <p class="text-sm text-gray-500 mt-2">{{ $review->time_ago }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Success/Redirect Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div id="modalContent"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    @vite('resources/js/app.js')
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Clipboard.js -->
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reviewForm = document.getElementById('reviewForm');
            const starRating = document.getElementById('starRating');
            const ratingInput = document.getElementById('ratingInput');
            const reviewText = document.getElementById('review_text');
            const charCount = document.getElementById('charCount');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            const suggestionSection = document.getElementById('suggestionSection');
            const getSuggestionsBtn = document.getElementById('getSuggestions');
            const suggestionsList = document.getElementById('suggestionsList');

            // Star rating functionality
            const stars = starRating.querySelectorAll('.star');
            let selectedRating = 0;

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.dataset.rating);
                    ratingInput.value = selectedRating;
                    updateStarRating(selectedRating);
                    
                    // Show suggestions section if rating is selected
                    if (selectedRating > 0) {
                        suggestionSection.classList.remove('hidden');
                        suggestionSection.classList.add('fade-in');
                    }
                    
                    // Hide rating error
                    document.getElementById('ratingError').classList.add('hidden');
                });

                star.addEventListener('mouseover', function() {
                    const hoverRating = parseInt(this.dataset.rating);
                    updateStarRating(hoverRating);
                });
            });

            starRating.addEventListener('mouseleave', function() {
                updateStarRating(selectedRating);
            });

            function updateStarRating(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            }

            // Character count
            reviewText.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count;
                
                if (count > {{ $settings['max_review_length'] ?? 500 }}) {
                    charCount.classList.add('text-red-600');
                } else {
                    charCount.classList.remove('text-red-600');
                }
            });

            // Get AI suggestions
            getSuggestionsBtn.addEventListener('click', async function() {
                if (!selectedRating) {
                    alert('Please select a rating first');
                    return;
                }

                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Getting suggestions...';
                this.disabled = true;

                try {
                    const response = await fetch(`{{ route('review.suggestions', $business->slug) }}?rating=${selectedRating}`);
                    const data = await response.json();

                    if (data.success) {
                        displaySuggestions(data.suggestions);
                    } else {
                        throw new Error(data.message || 'Failed to get suggestions');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Unable to get suggestions at the moment. Please try again.');
                } finally {
                    this.innerHTML = '<i class="fas fa-magic mr-1"></i> Get AI Suggestions';
                    this.disabled = false;
                }
            });

            function displaySuggestions(suggestions) {
                suggestionsList.innerHTML = '';
                suggestionsList.classList.remove('hidden');

                suggestions.forEach((suggestion, index) => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-card bg-gray-50 rounded-lg p-4 cursor-pointer border border-gray-200';
                    div.innerHTML = `
                        <div class="flex justify-between items-start">
                            <p class="text-gray-700 flex-1">${suggestion}</p>
                            <button type="button" class="ml-3 text-blue-600 hover:text-blue-800 text-sm use-suggestion" data-suggestion="${suggestion}">
                                Use This
                            </button>
                        </div>
                    `;
                    suggestionsList.appendChild(div);
                });

                // Add click handlers for suggestions
                document.querySelectorAll('.use-suggestion').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const suggestion = this.dataset.suggestion;
                        reviewText.value = suggestion;
                        reviewText.dispatchEvent(new Event('input')); // Update character count
                        suggestionsList.classList.add('hidden');
                        reviewText.focus();
                    });
                });
            }

            // Form submission
            reviewForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Validate rating
                if (!selectedRating) {
                    document.getElementById('ratingError').classList.remove('hidden');
                    return;
                }

                // Show loading
                submitBtn.disabled = true;
                submitText.textContent = 'Submitting...';
                submitLoading.classList.remove('hidden');

                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        handleSuccessResponse(data);
                    } else {
                        throw new Error(data.message || 'Failed to submit review');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to submit review. Please try again.',
                    });
                } finally {
                    // Reset loading state
                    submitBtn.disabled = false;
                    submitText.textContent = 'Submit Review';
                    submitLoading.classList.add('hidden');
                }
            });

            function handleSuccessResponse(data) {
                if (data.action === 'redirect') {
                    // High rating - redirect to external platform
                    showRedirectModal(data);
                } else {
                    // Low rating - show thank you
                    showThankYouModal(data.message);
                }
            }

            function showRedirectModal(data) {
                const modalContent = `
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Thank you for your review!</h3>
                        <p class="text-gray-600 mb-6">${data.message}</p>
                        <div class="mb-4">
                            <div id="countdown" class="text-2xl font-bold text-blue-600">3</div>
                            <p class="text-sm text-gray-500">Redirecting in seconds...</p>
                        </div>
                        <div class="flex space-x-3 justify-center">
                            <button onclick="window.location.href='${data.redirect_url}'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Go Now
                            </button>
                            <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded">
                                Cancel
                            </button>
                        </div>
                    </div>
                `;

                document.getElementById('modalContent').innerHTML = modalContent;
                document.getElementById('successModal').classList.remove('hidden');

                // Countdown timer
                let countdown = data.redirect_delay || 3;
                const countdownEl = document.getElementById('countdown');
                
                const timer = setInterval(() => {
                    countdown--;
                    countdownEl.textContent = countdown;
                    
                    if (countdown <= 0) {
                        clearInterval(timer);
                        window.location.href = data.redirect_url;
                    }
                }, 1000);
            }

            function showThankYouModal(message) {
                const modalContent = `
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <i class="fas fa-heart text-red-500 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Thank you!</h3>
                        <p class="text-gray-600 mb-6">${message}</p>
                        <button onclick="closeModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                            Close
                        </button>
                    </div>
                `;

                document.getElementById('modalContent').innerHTML = modalContent;
                document.getElementById('successModal').classList.remove('hidden');
            }

            // Global function to close modal
            window.closeModal = function() {
                document.getElementById('successModal').classList.add('hidden');
                reviewForm.reset();
                selectedRating = 0;
                ratingInput.value = '';
                updateStarRating(0);
                charCount.textContent = '0';
                suggestionSection.classList.add('hidden');
                suggestionsList.classList.add('hidden');
            };
        });
    </script>
</body>
</html>