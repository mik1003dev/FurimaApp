<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['item_id', 'category_code'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
