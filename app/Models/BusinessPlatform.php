<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'review_platform_id',
        'business_link',
        'review_link',
        'additional_data',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'additional_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function reviewPlatform()
    {
        return $this->belongsTo(ReviewPlatform::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForPlatform($query, $platformId)
    {
        return $query->where('review_platform_id', $platformId);
    }

    // Helper methods
    public function isGoogle()
    {
        return $this->reviewPlatform && $this->reviewPlatform->isGoogle();
    }

    public function getPlatformName()
    {
        return $this->reviewPlatform ? $this->reviewPlatform->name : 'Unknown';
    }

    public function getPlatformColor()
    {
        return $this->reviewPlatform ? $this->reviewPlatform->color : '#000000';
    }

    public function getPlatformIcon()
    {
        return $this->reviewPlatform ? $this->reviewPlatform->icon_url : null;
    }

    public function getAdditionalDataValue($key, $default = null)
    {
        return $this->additional_data[$key] ?? $default;
    }

    public function setAdditionalDataValue($key, $value)
    {
        $data = $this->additional_data ?? [];
        $data[$key] = $value;
        $this->additional_data = $data;
        $this->save();
    }

    public function validateLinks()
    {
        $errors = [];

        if ($this->business_link && !filter_var($this->business_link, FILTER_VALIDATE_URL)) {
            $errors[] = 'Business link is not a valid URL';
        }

        if ($this->review_link && !filter_var($this->review_link, FILTER_VALIDATE_URL)) {
            $errors[] = 'Review link is not a valid URL';
        }

        return $errors;
    }

    public function isValidated()
    {
        return empty($this->validateLinks());
    }
}