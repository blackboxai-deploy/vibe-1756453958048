<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'business_limit',
        'review_limit_per_month',
        'analytics_retention_days',
        'features',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'business_limit' => 'integer',
            'review_limit_per_month' => 'integer',
            'analytics_retention_days' => 'integer',
            'features' => 'array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(UserSubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>=', now());
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    public function getPricePerDayAttribute()
    {
        $days = $this->billing_cycle === 'yearly' ? 365 : 30;
        return round($this->price / $days, 2);
    }

    public function getBusinessLimitTextAttribute()
    {
        return $this->business_limit === -1 ? 'Unlimited' : $this->business_limit;
    }

    public function getReviewLimitTextAttribute()
    {
        return $this->review_limit_per_month === -1 ? 'Unlimited' : number_format($this->review_limit_per_month);
    }

    public function getAnalyticsRetentionTextAttribute()
    {
        if ($this->analytics_retention_days >= 365) {
            return round($this->analytics_retention_days / 365) . ' year(s)';
        }
        
        return $this->analytics_retention_days . ' days';
    }

    public function getBillingCycleTextAttribute()
    {
        return ucfirst($this->billing_cycle);
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->billing_cycle !== 'yearly') {
            return 0;
        }

        // Calculate yearly discount (assuming 10 months price for 12 months)
        $monthlyEquivalent = $this->price / 10;
        $monthlyPrice = $monthlyEquivalent * 12;
        
        if ($monthlyPrice > $this->price) {
            return round((($monthlyPrice - $this->price) / $monthlyPrice) * 100);
        }

        return 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('price', 'asc');
    }

    public function scopeMonthly($query)
    {
        return $query->where('billing_cycle', 'monthly');
    }

    public function scopeYearly($query)
    {
        return $query->where('billing_cycle', 'yearly');
    }

    // Helper methods
    public function isUnlimitedBusinesses()
    {
        return $this->business_limit === -1;
    }

    public function isUnlimitedReviews()
    {
        return $this->review_limit_per_month === -1;
    }

    public function hasFeature($feature)
    {
        return in_array($feature, $this->features ?? []);
    }

    public function canCreateBusiness($currentBusinessCount)
    {
        if ($this->isUnlimitedBusinesses()) {
            return true;
        }

        return $currentBusinessCount < $this->business_limit;
    }

    public function getRemainingBusinessSlots($currentBusinessCount)
    {
        if ($this->isUnlimitedBusinesses()) {
            return -1; // Unlimited
        }

        return max(0, $this->business_limit - $currentBusinessCount);
    }

    public function canSubmitReview($monthlyReviewCount)
    {
        if ($this->isUnlimitedReviews()) {
            return true;
        }

        return $monthlyReviewCount < $this->review_limit_per_month;
    }

    public function getRemainingReviewSlots($monthlyReviewCount)
    {
        if ($this->isUnlimitedReviews()) {
            return -1; // Unlimited
        }

        return max(0, $this->review_limit_per_month - $monthlyReviewCount);
    }

    public function getFeaturesList()
    {
        return $this->features ?? [];
    }

    public function getFeaturesHtml()
    {
        $features = $this->getFeaturesList();
        $html = '<ul class="space-y-2">';
        
        foreach ($features as $feature) {
            $html .= '<li class="flex items-center text-sm text-gray-600">';
            $html .= '<i class="fas fa-check text-green-500 mr-2"></i>';
            $html .= htmlspecialchars($feature);
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    public function getPlanBadgeClass()
    {
        if ($this->is_popular) {
            return 'bg-gradient-to-r from-purple-500 to-pink-500 text-white';
        }

        return match(strtolower($this->name)) {
            'starter', 'basic' => 'bg-blue-100 text-blue-800',
            'professional', 'pro' => 'bg-green-100 text-green-800',
            'enterprise', 'premium' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPlanBorderClass()
    {
        if ($this->is_popular) {
            return 'border-2 border-purple-500';
        }

        return 'border border-gray-200';
    }

    // Static methods
    public static function getPopularPlan()
    {
        return static::active()->popular()->first();
    }

    public static function getCheapestPlan()
    {
        return static::active()->orderBy('price')->first();
    }

    public static function getMostExpensivePlan()
    {
        return static::active()->orderByDesc('price')->first();
    }

    public static function getFreePlan()
    {
        return static::active()->where('price', 0)->first();
    }
}