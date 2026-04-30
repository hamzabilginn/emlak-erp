<?php
namespace App\Models;

class DealModel extends BaseModel {
    protected string $table = 'deals';
    protected array $fillable = [
        'tenant_id', 'property_id', 'customer_id', 
        'deal_type', 'price', 'commission_earned', 'deposit_taken'
    ];
}
