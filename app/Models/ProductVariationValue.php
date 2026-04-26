<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariationValue extends Model
{
    protected $guarded = [];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variant_id');
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class);
    }
}
