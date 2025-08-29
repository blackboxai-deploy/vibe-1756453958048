<?php

// Simple PHP server for Laravel Review SAAS
// This script simulates Laravel functionality for demo purposes

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string
$path = strtok($requestUri, '?');

// Basic routing
switch ($path) {
    case '/':
        echo renderHomePage();
        break;
        
    case '/health':
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'timestamp' => date('c'),
            'environment' => 'demo',
            'message' => 'Laravel Review SAAS is running successfully!'
        ]);
        break;
        
    default:
        // Check if it's a review page
        if (preg_match('#^/review/([a-zA-Z0-9-]+)$#', $path, $matches)) {
            echo renderReviewPage($matches[1]);
        } else {
            http_response_code(404);
            echo render404Page();
        }
        break;
}

function renderHomePage() {
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Review SAAS - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                        Streamline Your <span class="text-yellow-300">Review</span> Management
                    </h1>
                    <p class="text-xl lg:text-2xl text-blue-100 mb-8 leading-relaxed">
                        Collect, manage, and analyze customer reviews with our comprehensive SAAS platform. 
                        Boost your online reputation and drive more business.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/review/demo-business" 
                           class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                            Try Demo Review Page
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="/health" 
                           class="border-2 border-white hover:bg-white hover:text-gray-900 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                            API Health Check
                            <i class="fas fa-heartbeat ml-2"></i>
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white rounded-lg shadow-2xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Review Dashboard Preview</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-store text-blue-600"></i>
                                    <span class="text-gray-700">Active Businesses</span>
                                </div>
                                <span class="text-2xl font-bold text-blue-600">150+</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-star text-yellow-500"></i>
                                    <span class="text-gray-700">Reviews Collected</span>
                                </div>
                                <span class="text-2xl font-bold text-yellow-600">5,240+</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-chart-line text-green-600"></i>
                                    <span class="text-gray-700">Average Rating</span>
                                </div>
                                <span class="text-2xl font-bold text-green-600">4.8/5</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-6 -right-6 bg-yellow-400 text-gray-900 rounded-full p-4">
                        <i class="fas fa-star text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                    Key Features
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Everything you need to manage reviews effectively
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-8 text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-qrcode text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">QR Code Generation</h3>
                    <p class="text-gray-600">
                        Generate unique QR codes for each business location
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-robot text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">AI-Powered Suggestions</h3>
                    <p class="text-gray-600">
                        Help customers write better reviews with AI suggestions
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 text-center">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-route text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Smart Review Routing</h3>
                    <p class="text-gray-600">
                        Automatically route high ratings to Google Reviews
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-bold mb-6">
                Laravel Review SAAS is Running!
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Your comprehensive review management platform is successfully deployed and ready to use.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/review/demo-business" 
                   class="bg-white hover:bg-gray-100 text-gray-900 font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                    Try Review Form
                    <i class="fas fa-star ml-2"></i>
                </a>
                <a href="/health" 
                   class="border-2 border-white hover:bg-white hover:text-gray-900 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                    Check API Health
                    <i class="fas fa-heartbeat ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; 2024 Laravel Review SAAS. Built with Laravel 11, Tailwind CSS, and AI Integration.</p>
        </div>
    </footer>
</body>
</html>';
}

function renderReviewPage($slug) {
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review - ' . htmlspecialchars($slug) . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen py-8">
        <!-- Header -->
        <div class="max-w-2xl mx-auto px-4 mb-8 text-center">
            <div class="mb-6">
                <div class="h-20 w-20 mx-auto rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                    DB
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Demo Business</h1>
            <p class="text-gray-600 mb-4">Mumbai, Maharashtra, India</p>
            <p class="text-gray-600 max-w-lg mx-auto">Experience our review collection system with AI-powered suggestions and smart routing.</p>
        </div>

        <!-- Review Form -->
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Share Your Experience</h2>
                        <p class="text-gray-600">Your feedback helps us serve you better</p>
                    </div>

                    <form id="reviewForm" onsubmit="handleReviewSubmit(event)">
                        <!-- Rating Section -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-4 text-center">
                                How would you rate your experience?
                            </label>
                            <div class="flex justify-center space-x-2 mb-4" id="starRating">
                                <i class="star fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400" data-rating="1"></i>
                                <i class="star fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400" data-rating="2"></i>
                                <i class="star fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400" data-rating="3"></i>
                                <i class="star fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400" data-rating="4"></i>
                                <i class="star fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400" data-rating="5"></i>
                            </div>
                            <input type="hidden" id="ratingInput" value="" required>
                            <div id="ratingError" class="text-red-600 text-sm mt-2 hidden text-center">Please select a rating</div>
                        </div>

                        <!-- Review Text Section -->
                        <div class="mb-6">
                            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">
                                Tell us about your experience
                            </label>
                            <textarea name="review_text" id="review_text" rows="5" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    placeholder="Share your thoughts about your experience..."
                                    required></textarea>
                        </div>

                        <!-- Customer Information -->
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
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-md transition-colors">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedRating = 0;
        const stars = document.querySelectorAll(".star");
        const ratingInput = document.getElementById("ratingInput");

        stars.forEach(star => {
            star.addEventListener("click", function() {
                selectedRating = parseInt(this.dataset.rating);
                ratingInput.value = selectedRating;
                updateStarRating(selectedRating);
                document.getElementById("ratingError").classList.add("hidden");
            });

            star.addEventListener("mouseover", function() {
                const hoverRating = parseInt(this.dataset.rating);
                updateStarRating(hoverRating);
            });
        });

        document.getElementById("starRating").addEventListener("mouseleave", function() {
            updateStarRating(selectedRating);
        });

        function updateStarRating(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add("text-yellow-400");
                    star.classList.remove("text-gray-300");
                } else {
                    star.classList.remove("text-yellow-400");
                    star.classList.add("text-gray-300");
                }
            });
        }

        function handleReviewSubmit(event) {
            event.preventDefault();
            
            if (!selectedRating) {
                document.getElementById("ratingError").classList.remove("hidden");
                return;
            }

            const reviewText = document.getElementById("review_text").value;
            const customerName = document.getElementById("customer_name").value || "Anonymous";

            if (selectedRating >= 4) {
                // High rating - show redirect modal
                Swal.fire({
                    icon: "success",
                    title: "Thank you for your review!",
                    text: "We appreciate your positive feedback. You will be redirected to leave a review on Google.",
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: "Go to Google Reviews",
                    cancelButtonText: "Close"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open("https://www.google.com/search?q=write+a+review", "_blank");
                    }
                });
            } else {
                // Low rating - show thank you
                Swal.fire({
                    icon: "heart",
                    title: "Thank you for your feedback!",
                    text: "We appreciate your input and will work to improve our service.",
                    confirmButtonText: "Close"
                });
            }

            // Reset form
            document.getElementById("reviewForm").reset();
            selectedRating = 0;
            ratingInput.value = "";
            updateStarRating(0);
        }
    </script>
</body>
</html>';
}

function render404Page() {
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <div class="mb-8">
                <i class="fas fa-exclamation-triangle text-6xl text-yellow-500"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">404 - Page Not Found</h1>
            <p class="text-gray-600 mb-8">The page you are looking for does not exist.</p>
            <a href="/" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                <i class="fas fa-home mr-2"></i> Go Home
            </a>
        </div>
    </div>
</body>
</html>';
}
?>