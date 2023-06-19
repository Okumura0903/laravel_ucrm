<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;

class DecileService
{
    public static function decile($subQuery){
        //購買ID毎にまとめる
        $subQuery=$subQuery
        ->groupBy('id')
        ->selectRaw('id,customer_id,customer_name,sum(subtotal) as totalPerPurchase');

        //会員ごとにまとめて購入金額順にソートする
        $subQuery=DB::table($subQuery)
        ->groupBy('customer_id')
        ->selectRaw('customer_id,customer_name,sum(totalPerPurchase) as total')
        ->orderBy('total','desc');

        //購入金額順に連番を振る
        DB::statement('set @row_num=0;');//SQL上で変数を定義
        $subQuery=DB::table($subQuery)
        ->selectRaw('@row_num:=@row_num+1 as row_num,customer_id,customer_name,total');//@row_num=1,2,3,...

        //全体の件数を数え、1/10の値や合計金額を取得
        $count=DB::table($subQuery)->count();
        $total=DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        $total=$total[0]->total;
        $decile=ceil($count/10);//10分の1の件数
        $bindValues=[];
        $tempValue=0;
        for($i=1;$i<=10;$i++){
            array_push($bindValues,1+$tempValue);
            $tempValue+=$decile;
            array_push($bindValues,1+$tempValue);
        }

        //グループ分け
        DB::statement('set @row_num=0;');//変数を0に戻す
        $subQuery=DB::table($subQuery)
        ->selectRaw('
            row_num,
            customer_id,
            customer_name,
            total,
            case
                when ? <=row_num and row_num < ? then 1
                when ? <=row_num and row_num < ? then 2
                when ? <=row_num and row_num < ? then 3
                when ? <=row_num and row_num < ? then 4
                when ? <=row_num and row_num < ? then 5
                when ? <=row_num and row_num < ? then 6
                when ? <=row_num and row_num < ? then 7
                when ? <=row_num and row_num < ? then 8
                when ? <=row_num and row_num < ? then 9
                when ? <=row_num and row_num < ? then 10
            end as decile
        ',$bindValues);//SelectRow第二引数にバインドしたい配列を入れる

        //グループ毎の合計・平均
        $subQuery=DB::table($subQuery)
        ->groupBy('decile')
        ->selectRaw('decile,round(avg(total)) as average,sum(total) as totalPerGroup');//round:四捨五入

        //構成比を出す
        DB::statement("set @total={$total};");//PHP側の$totalをSQL内の変数に入れる
        $data=DB::table($subQuery)
        ->selectRaw('decile,average,totalPerGroup,round(100*totalPerGroup/@total,1) as totalRatio')->get();

        $labels=$data->pluck('decile');//decile列のみの配列
        $totals=$data->pluck('totalPerGroup');
        return [$data,$labels,$totals];
    }

}