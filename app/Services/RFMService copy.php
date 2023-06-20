<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMService
{
    public static function rfm($subQuery,$rfmPrms){

        //RFM分析
        //購買ID毎にまとめる
        $subQuery=$subQuery
        ->groupBy('id')
        ->selectRaw('id,customer_id,customer_name,sum(subtotal) as totalPerPurchase,created_at');

        //会員毎にまとめて最終購入日、回数、合計金額を取得
        $subQuery=DB::table($subQuery)
        ->groupBy('customer_id')
        ->selectRaw('customer_id,customer_name,max(created_at) as recentDate,datediff(now(),max(created_at)) as recency,count(customer_id) as frequency,sum(totalPerPurchase) as monetary');

//$rfmPrms=[14,28,60,90,7,5,3,2,300000,200000,100000,30000];

        //会員毎のRFMランクを計算
        $subQuery=DB::table($subQuery)
        ->selectRaw('customer_id,customer_name,recentDate,recency,frequency,monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else 1 end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else 1 end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
            else 1 end as m
        ',$rfmPrms);

        //ランク毎の数を計算する
        $totals=DB::table($subQuery)->count();

        // $rCount=DB::table($subQuery)
        // ->groupBy('r')
        // ->selectRaw('r,count(r)')
        // ->orderBy('r','desc')
        // ->pluck('count(r)');//rは並べ替えようなのでいらない

        $rDatas= DB::table($subQuery)
        ->groupBy('r')
        ->selectRaw('r,count(r) as c')
        ->orderBy('r','desc')
        ->get();//[{"r":5,"count(r)":998},{"r":4,"count(r)":2}]  

        $rCount=[];
        foreach($rDatas as $rData){
            $rCount[5-$rData->r]=$rData->c;
        }
        for($i=0;$i<5;$i++){
            if(empty($rCount[$i])){
                $rCount[$i]=0;
            }
        }

        Log::debug("debug ログ1");

        $fDatas=DB::table($subQuery)
        ->groupBy('f')
        ->selectRaw('f,count(f) as c')
        ->orderBy('f','desc')
        ->get();

        $fCount=[];
        foreach($fDatas as $fData){
            $fCount[5-$fData->f]=$fData->c;
        }
        for($i=0;$i<5;$i++){
            if(empty($fCount[$i])){
                $fCount[$i]=0;
            }
        }


        Log::debug("debug ログ2");

        $mDatas=DB::table($subQuery)
        ->groupBy('m')
        ->selectRaw('m,count(m) as c')
        ->orderBy('m','desc')
        ->get();

        $mCount=[];
        foreach($mDatas as $mData){
            $mCount[5-$mData->m]=$mData->c;
        }
        for($i=0;$i<5;$i++){
            if(empty($mCount[$i])){
                $mCount[$i]=0;
            }
        }


            Log::debug("debug ログ3");


        //RとFで２次元で表示
        $data=DB::table($subQuery)
        ->groupBy('r')//concatで頭にr_とつける r_1,r_2,...
        ->selectRaw('concat("r_",r) as rRank,
            count(case when f = 5 then 1 end) as f_5,
            count(case when f = 4 then 1 end) as f_4,
            count(case when f = 3 then 1 end) as f_3,
            count(case when f = 2 then 1 end) as f_2,
            count(case when f = 1 then 1 end) as f_1
        ')
        ->orderBy('rRank','desc')->get();


        Log::debug($rCount);
        Log::debug($fCount);
        Log::debug($mCount);


        $eachCount=[];
        $rank=5;
        for($i=0;$i<5;$i++){
            Log::debug("debug ログ1");
            array_push($eachCount,[
                'rank'=>$rank,
                'r'=>$rCount[$i],
                'f'=>$fCount[$i],
                'm'=>$mCount[$i]
            ]);
            Log::debug("debug ログ2");

            $rank--;
        }
        Log::debug("debug ログ4");
        
        return [$data,$totals,$eachCount];
    }
}