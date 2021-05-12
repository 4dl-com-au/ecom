<?php

namespace App;
use Illuminate\Http\Request;
use App\Model\Products;
use DarryCart;
use App\Model\Option;
use App\Model\OptionValues;

class Cart{
    public function add($user_id, $product, Request $request){
        $options = $request->options ? $request->options : [];
        $product = Products::where('id', $product)->first();
        $price = 0;
        if (!empty($options)) {
            $getprice = $this->getOptionsAttr($options, 'prices');
            foreach ($getprice as $key => $value) {
                $price = $price + ($value);
            }
        }else{
            $price = (!empty($product->salePrice) ? $product->salePrice : $product->price);
        }

        DarryCart::session($user_id)->add([
            'id' => md5("product_id.{$product->id}:options." . serialize(array_filter($options))),
            'name' => $product->title,
            'price' => $price,
            'quantity' => $request->quantity,
            'attributes' => [
                'options' => $options,
            ],
            'associatedModel' => $product
        ]);

        if ($request->ajax()) {
            return ['status' => 'success', 'cart_count' => count($this->getAll($user_id)), 'response' => 'Added to cart'];
        }
    }

    public function update($user, $id, Request $request){
        DarryCart::session($user)->update($id, [
            'quantity' => [
                'value' => $request->quantity,
                'relative' => false
            ]
        ]);

        if ($request->ajax()) {
            return ['status' => 'success', 'response' => 'Quantity Changed'];
        }
    }

    public function product_price($id, $options){

    }

    public static function total($user_id, $type = 'price'){
        $items = DarryCart::session($user_id)->getContent();
        $price = 0;
        $quantity = 0;

        foreach ($items as $key => $item) {
            $price = $price + ($item->price * $item->quantity);
            $quantity = $quantity + $item->quantity;
        }

        if ($type == 'quantity') {
            return $quantity;
        }

        return $price;
    }

    public function getAll($user_id){
        $items = DarryCart::session($user_id)->getContent();
        return $items;
    }

    public static function getOptionsAttr($options, $type = 'name'){
        $option_name = [];
        $option_seprator = '';
        $option_values = [];
        $options_prices = [];

        $html = '';
        $options_and_values = '';

        foreach ($options as $key => $value) {
            $option = Option::where('id', $key)->first();
            $option_name[] = $option->name;
            $option_seprator = ': ';

            $html .= '<br>' . '<b>'.$option->name.'</b>' . ' - ';
            $options_and_values .= $option->name . ' - ';

            if (is_array($value)) {
                foreach ($value as $keys => $item) {
                    $option_values_list = OptionValues::where('id', $item)->first();
                    $html .= $option_values_list->label . ', ';

                    $options_prices[$option_values_list->id] = $option_values_list->price;
                    $options_and_values .= $option_values_list->label . ', ';
                }
            }else{
                $option_values_list = OptionValues::where('id', $value)->first();
                $html .= $option_values_list->label . ', ';
                $options_prices[$option_values_list->id] = $option_values_list->price;
                $options_and_values .= $option_values_list->label . ', ';
            }
        }

        if ($type == 'name') {
            return $html;
        }elseif ($type == 'prices') {
            return $options_prices;
        }elseif ($type == 'name_string') {
            return $options_and_values;
        }elseif ($type == 'total_price') {
            $price = 0;
            foreach ($options_prices as $key => $value) {
                $price = $price + $value;
            }

            return $price;
        }

        #return implode('<br>', $option_name) . $option_seprator . implode(', ', $option_values);
    }

    public function remove($user, $id){
        DarryCart::session($user)->remove($id);

        return back();
    }
}
