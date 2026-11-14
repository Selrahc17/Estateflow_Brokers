<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    public const MAX_GALLERY_IMAGES = 10;

    public const TYPES = ['House and Lot', 'Condominium', 'Townhouse', 'Lot Only', 'Subdivision Lot Only', 'Farm Lot', 'Office Space', 'Warehouse', 'Villa', 'Apartment'];

    /** Types sold as land, which use lot fields instead of building fields. */
    public const LOT_TYPES = ['Lot Only', 'Subdivision Lot Only', 'Farm Lot'];

    public const SELLING_STAGES = [
        'pre_selling' => 'Pre-selling',
        'under_construction' => 'Under Construction',
        'ready_for_occupancy' => 'Ready for Occupancy',
    ];

    public const PAYMENT_OPTIONS = [
        'full_cash' => 'Full Cash',
        'spot_dp' => 'Spot Downpayment',
        'installment' => 'Installment',
        'in_house' => 'In-house Financing',
        'bank_financing' => 'Bank Financing',
        'pag_ibig' => 'Pag-IBIG Financing',
    ];

    public const TRANSFER_FEE_OPTIONS = [
        'excluded' => 'Not included in the price',
        'included' => 'Included in the price',
        'seller' => 'Shouldered by the seller (all-in)',
    ];

    public const AMENITY_CHOICES = [
        'Clubhouse', 'Swimming Pool', 'Gated Entrance', '24/7 Security', 'CCTV', 'Playground',
        'Basketball Court', 'Park / Open Space', 'Jogging Path', 'Function Hall', 'Fitness Gym',
        'Chapel', 'Commercial Area', 'Underground Utilities',
    ];

    public const SELLING_POINT_CHOICES = [
        'No house design restrictions', 'Flood-free', 'Along the main road', 'Cool climate',
        'Near schools', 'Near hospitals', 'Near malls', 'Ideal for investment', 'Ideal for Airbnb or rental',
        'Ideal for retirement', 'Ready for house construction', 'Title ready',
    ];

    public const UNIT_POSITIONS = ['Inner Unit', 'End Unit', 'Corner Unit'];

    public const MAX_LANDMARKS = 20;

    public const MAX_HOUSE_MODELS = 12;

    protected $fillable = [
        'broker_id', 'name', 'slug', 'description', 'address',
        'city', 'province', 'type', 'developer', 'developer_logo', 'latitude', 'longitude', 'price', 'agent_commission', 'valid_until', 'status',
        'bedrooms', 'bathrooms', 'floor_area', 'lot_area', 'frontage', 'stories', 'parking_slots',
        'featured_image', 'images', 'amenities', 'view_count',
        'selling_stage', 'reservation_fee', 'downpayment_percent', 'payment_options', 'installment_terms',
        'cash_discount_type', 'cash_discount', 'transfer_fee', 'transfer_fee_percent',
        'selling_points', 'landmarks', 'house_models', 'buyer_requirements',
    ];

    protected function casts(): array
    {
        return [
            'price'    => 'decimal:2',
            'agent_commission' => 'decimal:2',
            'valid_until' => 'date',
            'images'   => 'array',
            'amenities' => 'array',
            'reservation_fee' => 'decimal:2',
            'downpayment_percent' => 'decimal:2',
            'payment_options' => 'array',
            'cash_discount' => 'decimal:2',
            'transfer_fee_percent' => 'decimal:2',
            'selling_points' => 'array',
            'landmarks' => 'array',
            'house_models' => 'array',
        ];
    }

    /**
     * Every photo of the property, with the featured image first.
     *
     * @return array<int, string>
     */
    public function getGalleryImagesAttribute(): array
    {
        return array_values(array_unique(array_filter([$this->featured_image, ...($this->images ?? [])])));
    }

    /** 8.00 becomes "8", 2.50 becomes "2.5". */
    private static function trimNumber(float $number): string
    {
        return rtrim(rtrim(number_format($number, 2, '.', ','), '0'), '.');
    }

    /** "Pre-selling", "Ready for Occupancy", or null. */
    public function getSellingStageLabelAttribute(): ?string
    {
        return self::SELLING_STAGES[$this->selling_stage] ?? null;
    }

    /** @return array<int, string> */
    public function getPaymentOptionLabelsAttribute(): array
    {
        return array_values(array_intersect_key(self::PAYMENT_OPTIONS, array_flip($this->payment_options ?? [])));
    }

    public function hasCashDiscount(): bool
    {
        return in_array($this->cash_discount_type, ['percent', 'amount'], true) && (float) $this->cash_discount > 0;
    }

    /** "10%" or "₱100,000". */
    public function getCashDiscountLabelAttribute(): ?string
    {
        if (! $this->hasCashDiscount()) {
            return null;
        }

        return $this->cash_discount_type === 'percent'
            ? self::trimNumber((float) $this->cash_discount) . '%'
            : '₱' . number_format((float) $this->cash_discount, 0);
    }

    /** What a buyer pays in full cash after the discount, for the listing price or a house model's price. */
    public function cashPrice(float|string|null $price = null): ?float
    {
        $price = (float) ($price ?? $this->price);

        if ($price <= 0 || ! $this->hasCashDiscount()) {
            return null;
        }

        $discount = $this->cash_discount_type === 'percent'
            ? $price * (float) $this->cash_discount / 100
            : (float) $this->cash_discount;

        return max(0, $price - $discount);
    }

    public function downpaymentAmount(float|string|null $price = null): ?float
    {
        $price = (float) ($price ?? $this->price);

        return $price > 0 && (float) $this->downpayment_percent > 0 ? $price * (float) $this->downpayment_percent / 100 : null;
    }

    public function getDownpaymentPercentLabelAttribute(): ?string
    {
        return (float) $this->downpayment_percent > 0 ? self::trimNumber((float) $this->downpayment_percent) . '%' : null;
    }

    /** "Price is exclusive of the 8% transfer fee", "Transfer fee is included in the price", and so on. */
    public function getTransferFeeLabelAttribute(): ?string
    {
        $percent = (float) $this->transfer_fee_percent > 0 ? self::trimNumber((float) $this->transfer_fee_percent) . '% ' : '';

        return match ($this->transfer_fee) {
            'excluded' => "Price is exclusive of the {$percent}transfer fee",
            'included' => 'Transfer fee is included in the price',
            'seller' => 'All-in: the seller shoulders the transfer fee',
            default => null,
        };
    }

    public function hasPaymentTerms(): bool
    {
        return (float) $this->reservation_fee > 0 || (float) $this->downpayment_percent > 0 || ! empty($this->payment_options)
            || filled($this->installment_terms) || $this->hasCashDiscount() || filled($this->transfer_fee) || filled($this->buyer_requirements);
    }

    /** Lowest price among the house models, for listings without one price. */
    public function getLowestModelPriceAttribute(): ?float
    {
        $prices = array_filter(array_map(fn ($model) => (float) ($model['price'] ?? 0), $this->house_models ?? []));

        return $prices ? min($prices) : null;
    }

    /** A short summary of the selling terms, for the AI chat. */
    public function sellingTermsSummary(): ?string
    {
        $models = array_map(
            fn ($model) => trim(($model['name'] ?? '') . (! empty($model['price']) ? ' ₱' . number_format((float) $model['price'], 0) : '')),
            $this->house_models ?? []
        );

        return implode('; ', array_filter([
            $this->selling_stage_label,
            (float) $this->reservation_fee > 0 ? 'reservation ₱' . number_format((float) $this->reservation_fee, 0) : null,
            $this->downpayment_percent_label ? "{$this->downpayment_percent_label} downpayment" : null,
            $this->payment_option_labels ? 'payment: ' . implode(', ', $this->payment_option_labels) : null,
            $this->installment_terms ? "installment: {$this->installment_terms}" : null,
            $this->cash_discount_label ? "{$this->cash_discount_label} full cash discount" : null,
            $this->transfer_fee_label,
            $models ? 'models: ' . implode(', ', $models) : null,
            $this->amenities ? 'amenities: ' . implode(', ', $this->amenities) : null,
            $this->buyer_requirements ? 'requirements: ' . Str::squish($this->buyer_requirements) : null,
        ])) ?: null;
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    // A property can have many favorites
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // A property can have many inquiries
    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    // A property can have many site visits
    public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class);
    }
}