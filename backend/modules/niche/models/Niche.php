<?php

namespace backend\modules\niche\models;


/**
 * 用于商机详情（包含商机商品列表）
 * @SWG\Definition(required={}, @SWG\Xml(name="Niche"))
 */
class Niche extends NicheList
{
    /**
     * @SWG\Property()
     *
     * @var NicheProduct[]
     */
    public $products;

    /**
     * @SWG\Property()
     *
     * @var NicheCustomers[]
     */
    public $customers;

    /**
     * @SWG\Property()
     *
     * @var NicheContacts[]
     */
    public $contacts;

    /**
     * @SWG\Property()
     *
     * @var NicheUsers[]
     */
    public $users;

    /**
     * @SWG\Property()
     *
     * @var NicheLabels[]
     */
    public $labels;

}
