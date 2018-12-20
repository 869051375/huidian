<?php
namespace common\biztraits;
use common\models\Product;
use common\utils\BC;
use yii\helpers\Json;

trait PriceDetail
{
    public function addPriceDetail($item)
    {
        $list = $this->getPriceDetail();
        $key = md5(rand(1000,9000).time());
        $item = ['key' => $key, 'name' => $item['name'], 'price' => $item['price'], 'unit' => $item['unit'], 'tax_rate' => ($item['is_invoice'] ? $item['tax_rate'] : 0), 'is_invoice' => $item['is_invoice']];
        $list[] = $item;
        $this->setPriceDetail($list);
        return $item;
    }

    private function setPriceDetail($items)
    {
        $total = 0;
        $totalTax = 0;
        foreach ($items as $value){
            $total = BC::add($value['price'], $total);
            $tax = BC::div(BC::mul($value['tax_rate'], $value['price']), 100);
            $totalTax = BC::add($tax, $totalTax);
        }
        $this->price = $total;
        $this->tax = $totalTax;
        $this->price_detail = Json::encode(['details' => $items]);
    }

    public function removePriceDetail($key)
    {
        $list = $this->getPriceDetail();
        foreach($list as $index => $item)
        {
            if($item['key'] == $key)
            {
                unset($list[$index]);
            }
        }
        $this->setPriceDetail($list);
    }

    public function getPriceDetail()
    {
        if(empty($this->price_detail)) return [];
        $rs = Json::decode($this->price_detail);
        if(isset($rs['details'])) return $rs['details'];
        return [];
    }
}