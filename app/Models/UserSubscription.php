<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_id',
        'status',
        'amount',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'payment_method',
        'payment_id',
        'payment_details',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'next_billing_date' => 'date',
            'payment_details' => 'array',
            'cancelled_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'expired' => 'bg-gray-100 text-gray-800',
            'inactive' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    public function getPaymentMethodTextAttribute()
    {
        return match($this->payment_method) {
            'razorpay' => 'Razorpay',
            'phonepe' => 'PhonePe',
            'paytm' => 'Paytm',
            'upi' => 'UPI',
            default => 'Unknown'
        };
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->status !== 'active' || !$this->ends_at) {
            return 0;
        }

        return max(0, Carbon::parse($this->ends_at)->diffInDays(now(), false));
    }

    public function getDaysUsedAttribute()
    {
        if (!$this->starts_at) {
            return 0;
        }

        $startDate = Carbon::parse($this->starts_at);
        $currentDate = now();
        
        if ($this->ends_at && $currentDate->isAfter($this->ends_at)) {
            $currentDate = Carbon::parse($this->ends_at);
        }

        return max(0, $startDate->diffInDays($currentDate));
    }

    public function getTotalDaysAttribute()
    {
        if (!$this->starts_at || !$this->ends_at) {
            return 0;
        }

        return Carbon::parse($this->starts_at)->diffInDays($this->ends_at);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_days <= 0) {
            return 0;
        }

        return min(100, round(($this->days_used / $this->total_days) * 100, 1));
    }

    public function getBillingCycleTextAttribute()
    {
        return ucfirst($this->billing_cycle);
    }

    public function getNextBillingAmountAttribute()
    {
        if ($this->status !== 'active' || !$this->plan) {
            return 0;
        }

        return $this->plan->price;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        $expiryDate = now()->addDays($days);
        
        return $query->where('status', 'active')
                    ->whereBetween('ends_at', [now(), $expiryDate]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    public function scopeByPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at >= now();
    }

    public function isExpired()
    {
        return $this->status === 'active' && $this->ends_at < now();
    }

    public function isExpiringSoon($days = 7)
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->ends_at <= now()->addDays($days);
    }

    public function canRenew()
    {
        return in_array($this->status, ['active', 'expired']) && $this->plan;
    }

    public function canCancel()
    {
        return $this->status === 'active';
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    public function expire()
    {
        $this->update([
            'status' => 'expired'
        ]);
    }

    public function renew($newEndDate = null, $amount = null)
    {
        $newEndDate = $newEndDate ?: $this->calculateNewEndDate();
        $amount = $amount ?: $this->plan->price;

        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $newEndDate,
            'next_billing_date' => $this->calculateNextBillingDate($newEndDate),
            'amount' => $amount,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    public function calculateNewEndDate()
    {
        if (!$this->plan) {
            return now()->addMonth();
        }

        return $this->plan->billing_cycle === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();
    }

    public function calculateNextBillingDate($endDate = null)
    {
        $endDate = $endDate ?: $this->ends_at;
        
        if (!$endDate || !$this->plan) {
            return null;
        }

        // Set next billing date to end date for automatic renewal
        return $endDate;
    }

    public function getUsageStats()
    {
        if (!$this->isActive()) {
            return [
                'business_count' => 0,
                'review_count' => 0,
                'can_create_business' => false,
                'can_submit_review' => false,
            ];
        }

        $businessCount = $this->user->businesses()->count();
        $monthlyReviewCount = $this->user->businesses()
            ->with(['reviews' => function ($query) {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            }])
            ->get()
            ->sum(function ($business) {
                return $business->reviews->count();
            });

        return [
            'business_count' => $businessCount,
            'review_count' => $monthlyReviewCount,
            'business_limit' => $this->plan->business_limit,
            'review_limit' => $this->plan->review_limit_per_month,
            'can_create_business' => $this->plan->canCreateBusiness($businessCount),
            'can_submit_review' => $this->plan->canSubmitReview($monthlyReviewCount),
            'remaining_business_slots' => $this->plan->getRemainingBusinessSlots($businessCount),
            'remaining_review_slots' => $this->plan->getRemainingReviewSlots($monthlyReviewCount),
        ];
    }

    // Static methods
    public static function createSubscription($userId, $planId, $paymentDetails = [])
    {
        $plan = Plan::find($planId);
        if (!$plan) {
            throw new \Exception('Plan not found');
        }

        $startDate = now();
        $endDate = $plan->billing_cycle === 'yearly' 
            ? $startDate->copy()->addYear() 
            : $startDate->copy()->addMonth();

        return static::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'subscription_id' => 'SUB_' . time() . '_' . $userId,
            'status' => 'pending',
            'amount' => $plan->price,
            'billing_cycle' => $plan->billing_cycle,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'next_billing_date' => $endDate,
            'payment_method' => $paymentDetails['method'] ?? null,
            'payment_id' => $paymentDetails['payment_id'] ?? null,
            'payment_details' => $paymentDetails,
        ]);
    }
}