<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>=', now());
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class, 'approved_by');
    }

    // Accessors & Mutators
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        
        return "https://placehold.co/100x100?text=" . urlencode(substr($this->name, 0, 1));
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode
        ]);
        
        return implode(', ', $parts);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMasterAdmin($query)
    {
        return $query->where('role', 'master_admin');
    }

    public function scopeUserAdmin($query)
    {
        return $query->where('role', 'user_admin');
    }

    // Helper methods
    public function isMasterAdmin()
    {
        return $this->role === 'master_admin';
    }

    public function isUserAdmin()
    {
        return $this->role === 'user_admin';
    }

    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }

    public function canCreateBusiness()
    {
        if ($this->isMasterAdmin()) {
            return true;
        }

        $subscription = $this->activeSubscription;
        if (!$subscription) {
            return false;
        }

        $plan = $subscription->plan;
        if ($plan->business_limit === -1) {
            return true; // Unlimited
        }

        return $this->businesses()->count() < $plan->business_limit;
    }

    public function getRemainingBusinessSlots()
    {
        if ($this->isMasterAdmin()) {
            return -1; // Unlimited
        }

        $subscription = $this->activeSubscription;
        if (!$subscription) {
            return 0;
        }

        $plan = $subscription->plan;
        if ($plan->business_limit === -1) {
            return -1; // Unlimited
        }

        return max(0, $plan->business_limit - $this->businesses()->count());
    }
}