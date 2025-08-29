<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFormattedValueAttribute()
    {
        return $this->formatValue($this->value, $this->type);
    }

    // Helper methods
    public function formatValue($value, $type)
    {
        return match($type) {
            'boolean' => $value ? 'Yes' : 'No',
            'json' => json_decode($value, true),
            'integer' => (int) $value,
            'decimal', 'float' => (float) $value,
            default => $value
        };
    }

    public function getRawValue()
    {
        return $this->formatValue($this->value, $this->type);
    }

    // Static methods for easy access
    public static function get($key, $default = null, $useCache = true)
    {
        if ($useCache) {
            return Cache::remember("setting_{$key}", 3600, function() use ($key, $default) {
                return static::getValue($key, $default);
            });
        }

        return static::getValue($key, $default);
    }

    protected static function getValue($key, $default)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->formatValue($setting->value, $setting->type);
    }

    public static function set($key, $value, $type = 'string', $description = null, $isPublic = false)
    {
        // Clear cache
        Cache::forget("setting_{$key}");

        // Convert value based on type
        $formattedValue = match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            'integer' => (string) (int) $value,
            'decimal', 'float' => (string) (float) $value,
            default => (string) $value
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $formattedValue,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }

    public static function forget($key)
    {
        Cache::forget("setting_{$key}");
        return static::where('key', $key)->delete();
    }

    public static function getMultiple(array $keys, $useCache = true)
    {
        $settings = [];
        
        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                $key = $default;
                $default = null;
            }
            
            $settings[$key] = static::get($key, $default, $useCache);
        }

        return $settings;
    }

    public static function getPublicSettings($useCache = true)
    {
        if ($useCache) {
            return Cache::remember('public_settings', 3600, function() {
                return static::getPublicSettingsData();
            });
        }

        return static::getPublicSettingsData();
    }

    protected static function getPublicSettingsData()
    {
        return static::public()
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->formatted_value];
            })
            ->toArray();
    }

    public static function clearCache()
    {
        $settings = static::all();
        
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }

        Cache::forget('public_settings');
    }

    // Predefined setting getters for common settings
    public static function getOpenAIKey()
    {
        return static::get('openai_api_key');
    }

    public static function getOpenAIModel()
    {
        return static::get('openai_model', 'gpt-3.5-turbo');
    }

    public static function isReviewSuggestionsEnabled()
    {
        return static::get('review_suggestions_enabled', false);
    }

    public static function getRazorpayKey()
    {
        return static::get('razorpay_key_id');
    }

    public static function getRazorpaySecret()
    {
        return static::get('razorpay_key_secret');
    }

    public static function getPhonePeMerchantId()
    {
        return static::get('phonepe_merchant_id');
    }

    public static function getPhonePeSaltKey()
    {
        return static::get('phonepe_salt_key');
    }

    public static function getPaytmMerchantId()
    {
        return static::get('paytm_merchant_id');
    }

    public static function getPaytmMerchantKey()
    {
        return static::get('paytm_merchant_key');
    }

    public static function getUPIId()
    {
        return static::get('upi_id');
    }

    public static function getUPIMerchantName()
    {
        return static::get('upi_merchant_name', 'Review SAAS');
    }

    public static function getAppLogo()
    {
        return static::get('app_logo');
    }

    public static function getCompanyName()
    {
        return static::get('company_name', 'Review SAAS');
    }

    public static function getSupportEmail()
    {
        return static::get('support_email', 'support@reviewsaas.com');
    }

    public static function getTermsUrl()
    {
        return static::get('terms_url');
    }

    public static function getPrivacyUrl()
    {
        return static::get('privacy_url');
    }

    public static function getMinReviewLength()
    {
        return static::get('min_review_length', 10);
    }

    public static function getMaxReviewLength()
    {
        return static::get('max_review_length', 500);
    }

    public static function isAutoApproveReviews()
    {
        return static::get('auto_approve_reviews', false);
    }

    public static function getRedirectDelay()
    {
        return static::get('redirect_delay', 3);
    }

    // Setting validation
    public static function validateKey($key)
    {
        // Only allow alphanumeric characters, underscores, and dots
        return preg_match('/^[a-zA-Z0-9_.]+$/', $key);
    }

    public static function validateType($type)
    {
        return in_array($type, ['string', 'json', 'boolean', 'integer', 'decimal', 'float']);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('public_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('public_settings');
        });
    }
}