<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $model;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = Setting::get('openai_api_key');
        $this->model = Setting::get('openai_model', 'openrouter/anthropic/claude-sonnet-4');
        $this->apiUrl = 'https://oi-server.onrender.com/chat/completions';
    }

    /**
     * Generate review suggestions using AI
     */
    public function generateReviewSuggestions(Business $business, int $rating, array $keywords = [], int $count = 3): array
    {
        if (empty($this->apiKey) && !$this->isCustomEndpoint()) {
            throw new \Exception('OpenAI API key not configured');
        }

        $prompt = $this->buildReviewPrompt($business, $rating, $keywords);

        try {
            $response = $this->makeRequest([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates authentic, unique, and positive review suggestions for businesses. Each review should be different in style, length, and focus. Never repeat the same review or use similar phrases.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 800,
                'temperature' => 0.9, // Higher temperature for more variety
            ]);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'API request failed');
            }

            $content = $response['data']['choices'][0]['message']['content'] ?? '';
            
            return $this->parseReviewSuggestions($content, $count);
            
        } catch (\Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            throw new \Exception('Unable to generate review suggestions');
        }
    }

    /**
     * Test the OpenAI connection
     */
    public function testConnection(): array
    {
        try {
            $response = $this->makeRequest([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Respond with "Connection successful" if you receive this message.'
                    ]
                ],
                'max_tokens' => 50,
            ]);

            return [
                'success' => $response['success'],
                'message' => $response['success'] ? 'Connection successful' : ($response['error'] ?? 'Connection failed'),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build the review generation prompt
     */
    protected function buildReviewPrompt(Business $business, int $rating, array $keywords): string
    {
        $keywordText = !empty($keywords) ? implode(', ', $keywords) : '';
        
        $prompt = "Generate {$rating} unique, authentic review suggestions for the following business:\n\n";
        $prompt .= "Business Name: {$business->business_name}\n";
        $prompt .= "Business Type: {$business->category}\n";
        $prompt .= "Location: {$business->city}, {$business->state}\n";
        $prompt .= "Description: " . ($business->description ?: 'General business') . "\n";
        $prompt .= "Rating: {$rating} stars\n";
        
        if ($keywordText) {
            $prompt .= "Focus Keywords: {$keywordText}\n";
        }
        
        $prompt .= "\nRequirements:\n";
        $prompt .= "- Generate exactly 3 different review suggestions\n";
        $prompt .= "- Each review should be 30-100 words long\n";
        $prompt .= "- Make each review unique in style, tone, and focus\n";
        $prompt .= "- Include specific details that customers might mention\n";
        $prompt .= "- Use natural, conversational language\n";
        $prompt .= "- Avoid repetitive phrases or similar structures\n";
        $prompt .= "- Make reviews sound genuine and authentic\n";
        
        if ($keywordText) {
            $prompt .= "- Naturally incorporate the focus keywords where appropriate\n";
        }
        
        $prompt .= "\nFormat each review on a separate line starting with 'Review:' followed by the review text.\n";
        
        return $prompt;
    }

    /**
     * Parse review suggestions from AI response
     */
    protected function parseReviewSuggestions(string $content, int $count): array
    {
        $suggestions = [];
        
        // Split by lines and look for review patterns
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for lines starting with "Review:" or numbered reviews
            if (preg_match('/^(?:Review:|(?:\d+[\.\)])\s*)/i', $line)) {
                $review = preg_replace('/^(?:Review:|(?:\d+[\.\)])\s*)/i', '', $line);
                $review = trim($review);
                
                if (!empty($review) && strlen($review) > 20) {
                    $suggestions[] = $review;
                }
            } elseif (strlen($line) > 30 && count($suggestions) < $count) {
                // If it looks like a review (reasonable length), include it
                $suggestions[] = $line;
            }
        }
        
        // If we couldn't parse properly, try splitting by double newlines
        if (count($suggestions) < 2) {
            $paragraphs = explode("\n\n", $content);
            $suggestions = [];
            
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (strlen($paragraph) > 30) {
                    $suggestions[] = $paragraph;
                }
            }
        }
        
        // Ensure we have at least some suggestions
        if (empty($suggestions)) {
            $suggestions = [
                "Great experience at {$business->business_name}! The service was excellent and I would definitely recommend it to others.",
                "Very satisfied with my visit. The staff was friendly and professional. Will be coming back soon!",
                "Outstanding service and quality. {$business->business_name} exceeded my expectations in every way."
            ];
        }
        
        // Limit to requested count
        return array_slice($suggestions, 0, $count);
    }

    /**
     * Make API request to OpenAI or custom endpoint
     */
    protected function makeRequest(array $data): array
    {
        try {
            $headers = [];
            
            if ($this->isCustomEndpoint()) {
                // Use custom endpoint with fixed headers
                $headers = [
                    'customerId' => 'cus_SYf6ZtHOnN1bET',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer xxx',
                ];
            } else {
                // Use standard OpenAI API
                $headers = [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ];
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($this->apiUrl, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if using custom endpoint
     */
    protected function isCustomEndpoint(): bool
    {
        return $this->apiUrl === 'https://oi-server.onrender.com/chat/completions';
    }

    /**
     * Generate a personalized welcome message
     */
    public function generateWelcomeMessage(Business $business): string
    {
        try {
            $response = $this->makeRequest([
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Generate a warm, welcoming message for a business review page. Keep it under 50 words and make it personal.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Business: {$business->business_name} in {$business->city}. Type: {$business->category}"
                    ]
                ],
                'max_tokens' => 100,
                'temperature' => 0.7,
            ]);

            if ($response['success']) {
                return $response['data']['choices'][0]['message']['content'] ?? 
                       "Welcome! We'd love to hear about your experience with {$business->business_name}.";
            }
        } catch (\Exception $e) {
            // Fallback message
        }

        return "Welcome! We'd love to hear about your experience with {$business->business_name}.";
    }

    /**
     * Get model information
     */
    public function getModelInfo(): array
    {
        return [
            'model' => $this->model,
            'endpoint' => $this->apiUrl,
            'is_custom_endpoint' => $this->isCustomEndpoint(),
            'api_key_configured' => !empty($this->apiKey) || $this->isCustomEndpoint(),
        ];
    }
}