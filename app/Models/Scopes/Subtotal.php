<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Subtotal implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        //
        // "id" => 1
        // "pivot_id" => 1
        // "subtotal" => 32000
        // "customer_name" => "喜嶋 直樹"
        // "item_bame" => "カラー"
        // "item_price" => 8000
        // "quantity" => 4
        // "status" => 1
        // "created_at" => "2017-02-04 08:06:43"
        // "updated_at" => "2023-06-17 17:56:46"

        $sql='select purchases.id as id
        ,items.id as item_id
        ,item_purchase.id as pivot_id
        ,items.price * item_purchase.quantity as subtotal
        ,customers.id as customer_id
        ,customers.name as customer_name
        ,items.name as item_bame
        ,items.price as item_price
        ,item_purchase.quantity
        ,purchases.status
        ,purchases.created_at
        ,purchases.updated_at
        from purchases
        left join item_purchase on purchases.id=item_purchase.purchase_id
        left join items on item_purchase.item_id=items.id
        left join customers on purchases.customer_id=customers.id
        ';
        $builder->fromSub($sql,'order_subtotlas');
    }
}
