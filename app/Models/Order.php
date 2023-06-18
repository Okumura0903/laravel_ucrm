<?php

namespace App\Models;

use App\Models\Scopes\Subtotal as ScopesSubtotal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
//use Models\Scopes\Subtotal;
// スコープ用のモデル
class Order extends Model
{
    use HasFactory;

    // グローバルスコープ
    protected static function booted(){
        static::addGlobalScope(new ScopesSubtotal);
    }

    //ローカルスコープ
    public function scopeBetweenDate($query,$startDate=null,$endDate=null){
        if(is_null($startDate) && is_null($endDate)){
            return $query;
        }
        if(!is_null($startDate) && is_null($endDate)){
            return $query->where('created_at','>=',$startDate);
        }
        if(is_null($startDate) && !is_null($endDate)){
            $endDate1=Carbon::parse($endDate)->addDays(1);//終了日は+1日しないと取れない
            return $query->where('created_at','<=',$endDate1);
        }
        if(!is_null($startDate) && !is_null($endDate)){
            $endDate1=Carbon::parse($endDate)->addDays(1);//終了日は+1日しないと取れない
            return $query->where('created_at','>=',$startDate)
                ->where('created_at','<=',$endDate1);
        }
    }
}
