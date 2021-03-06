<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    // 软删除
    use SoftDeletes;

    public function customer()
    {
        return $this->belongsTo(ManorOwner::class, 'customer_id', 'id');
    }
}
