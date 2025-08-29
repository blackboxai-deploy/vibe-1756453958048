@extends('layouts.app')

@section('title', 'Review SAAS - Streamline Your Online Reputation Management')

@section('content')
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
                    <a href="{{ route('register') }}" 
                       class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                        Get Started Free
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <a href="#features" 
                       class="border-2 border-white hover:bg-white hover:text-gray-900 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                        Learn More
                        <i class="fas fa-play ml-2"></i>
                    </a>
                </div>
            </div>
            <div class="relative">
                <img src="https://placehold.co/600x400?text=Review+Management+Dashboard" 
                     alt="Review Management Dashboard" 
                     class="rounded-lg shadow-2xl">
                <div class="absolute -top-6 -right-6 bg-yellow-400 text-gray-900 rounded-full p-4">
                    <i class="fas fa-star text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                    {{ number_format($stats['total_businesses']) }}+
                </div>
                <div class="text-gray-600">Active Businesses</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                    {{ number_format($stats['total_reviews']) }}+
                </div>
                <div class="text-gray-600">Reviews Collected</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                    {{ number_format($stats['average_rating'], 1) }}/5
                </div>
                <div class="text-gray-600">Average Rating</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                    99.9%
                </div>
                <div class="text-gray-600">Uptime</div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="bg-gray-50 py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                Everything You Need to Manage Reviews
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Our comprehensive platform provides all the tools you need to collect, 
                manage, and leverage customer feedback for business growth.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-qrcode text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">QR Code Generation</h3>
                <p class="text-gray-600">
                    Generate unique QR codes for each business location. Customers can easily 
                    access review forms by scanning the code.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-robot text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">AI-Powered Suggestions</h3>
                <p class="text-gray-600">
                    Help customers write better reviews with AI-generated suggestions 
                    tailored to your business and keywords.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-route text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Smart Review Routing</h3>
                <p class="text-gray-600">
                    Automatically route 4-5 star reviews to Google while keeping 
                    1-3 star reviews for internal improvement.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-yellow-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-chart-bar text-2xl text-yellow-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Advanced Analytics</h3>
                <p class="text-gray-600">
                    Track device usage, location data, browser information, and 
                    detailed analytics for all your review activities.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-red-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-credit-card text-2xl text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Multiple Payment Gateways</h3>
                <p class="text-gray-600">
                    Support for Razorpay, PhonePe, Paytm, and UPI payments with 
                    secure subscription management.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-white rounded-xl p-8 shadow-soft hover:shadow-medium transition-shadow">
                <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mb-6">
                    <i class="fas fa-shield-alt text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Multi-Tenant Architecture</h3>
                <p class="text-gray-600">
                    Secure multi-tenant system with role-based access control for 
                    master admins and business users.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Section -->
@if($plans->count() > 0)
<div class="bg-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                Choose Your Plan
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Flexible pricing options to fit businesses of all sizes. 
                Start free and scale as you grow.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 3) }} gap-8">
            @foreach($plans as $plan)
            <div class="bg-white rounded-xl shadow-soft hover:shadow-medium transition-shadow border {{ $plan->is_popular ? 'border-purple-500 relative' : 'border-gray-200' }}">
                @if($plan->is_popular)
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-purple-500 text-white px-6 py-2 rounded-full text-sm font-medium">
                        Most Popular
                    </span>
                </div>
                @endif
                
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-600 mb-6">{{ $plan->description }}</p>
                    
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">{{ $plan->formatted_price }}</span>
                        <span class="text-gray-600">/ {{ $plan->billing_cycle }}</span>
                    </div>

                    <div class="space-y-3 mb-8">
                        @foreach($plan->getFeaturesList() as $feature)
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>

                    <a href="{{ route('register') }}" 
                       class="w-full {{ $plan->is_popular ? 'bg-purple-600 hover:bg-purple-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold py-3 px-6 rounded-lg text-center transition-colors inline-block">
                        Get Started
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Testimonials Section -->
@if($recentReviews->count() > 0)
<div class="bg-gray-50 py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                What Our Customers Say
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Join thousands of businesses already using our platform to improve their online reputation.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($recentReviews->take(6) as $review)
            <div class="bg-white rounded-xl p-6 shadow-soft">
                <div class="flex items-center mb-4">
                    <img src="{{ $review->customer_avatar }}" alt="{{ $review->customer_name }}" 
                         class="h-10 w-10 rounded-full mr-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $review->customer_name ?: 'Anonymous Customer' }}</h4>
                        <div class="flex text-yellow-400 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">"{{ $review->getTruncatedReview(120) }}"</p>
                <p class="text-sm text-gray-500">{{ $review->business->business_name }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- CTA Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl lg:text-4xl font-bold mb-6">
            Ready to Transform Your Review Management?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            Join thousands of businesses already using our platform to collect, manage, and leverage customer reviews.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" 
               class="bg-white hover:bg-gray-100 text-gray-900 font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                Start Your Free Trial
                <i class="fas fa-rocket ml-2"></i>
            </a>
            <a href="{{ route('login') }}" 
               class="border-2 border-white hover:bg-white hover:text-gray-900 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors inline-flex items-center justify-center">
                Sign In
                <i class="fas fa-sign-in-alt ml-2"></i>
            </a>
        </div>
    </div>
</div>
@endsection