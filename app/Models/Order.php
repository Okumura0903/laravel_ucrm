<?php

namespace App\Models;

use App\Models\Scopes\Subtotal as ScopesSubtotal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Models\Scopes\Subtotal;
// グローバルスコープ用のモデル
class Order extends Model
{
    use HasFactory;

    protected static function booted(){
        static::addGlobalScope(new ScopesSubtotal);
    }
}
