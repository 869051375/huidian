<?php

namespace backend\modules\niche\models;


use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\OpportunityAssignDepartment;
use common\utils\BC;
use Yii;

/**
 * 新增新商机时使用该类创建商机的商品
 * @SWG\Definition(required={"product_id", "qty", "price"}, @SWG\Xml(name="CreateNicheProductForm"))
 */

class CreateNicheProductForm extends NicheProductForm
{


    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;

    /**
     * 商机id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $niche_id;

    /**
     * 商品id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $product_id;

    /**
     * 商品名称
     * @SWG\Property(example = "测试名称")
     * @var string
     */
    public $product_name;

    /**
     * 省份id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $province_id;

    /**
     * 城市id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $city_id;

    /**
     * 区县id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $district_id;

    /**
     * 省份名称
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $province_name;

    /**
     * 城市名称
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $city_name;

    /**
     * 区县名称
     * @SWG\Property(example = "海淀区")
     * @var string
     */
    public $district_name;

    /**
     * 服务区域
     * @SWG\Property(example = "澳门")
     * @var string
     */
    public $service_area;

    /**
     * 数量
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $qty;

    /**
     * 原价
     * @SWG\Property(example = 1000.00)
     * @var string
     */
    public $original_price;

    /**
     * 商品销售单价
     * @SWG\Property(example = 999.99)
     * @var string
     */
    public $price;

    /**
     * 总价
     * @SWG\Property(example = 999.99)
     * @var string
     */
    public $amount;

    /**
     * 商品一级分类
     * @SWG\Property(example = "1")
     * @var string
     */
    public $category_id;

    /**
     * 商品二级分类
     * @SWG\Property(example = "1")
     * @var string
     */
    public $top_category_id;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id','category_id', 'top_category_id','product_id', 'province_id', 'city_id', 'district_id', 'qty'], 'integer'],
            [['original_price', 'price', 'amount'], 'number'],
            [['price'], 'required'],
            [['product_name', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['service_area'], 'string', 'max' => 6],
        ];
    }


    public function getOpportunityAssignDepartment()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
//        $administrator->getTreeDepartmentId();
        $department = OpportunityAssignDepartment::find()->select('department_id')->where(['product_id' => $this->product_id])->andWhere(['company_id' => $administrator->company_id])->one();
        if (!empty($department))
        {
            /** @var CrmDepartment $department_one */
            $department_one = CrmDepartment::find()->where(['id'=>$department])->one();
            $data = $department_one->getTreeDepartmentId(true);
            return $data;
        }
        return [];
    }

    public function getOpportunityAssignDepartmentOne()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
//        $administrator->getTreeDepartmentId();
        return OpportunityAssignDepartment::find()->select('department_id')->where(['product_id' => $this->product_id])->andWhere(['company_id' => $administrator->company_id])->one();

    }




    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }


    /**
     * @param \common\models\Niche $niche
     * @return bool
     */
    public function save($niche)
    {
        $nicheProduct = new \common\models\NicheProduct();
        $nicheProduct->load($this->attributes, '');
        $nicheProduct->niche_id = $niche->id;
        $nicheProduct->amount = BC::mul($nicheProduct->qty, $nicheProduct->price);
        // todo 设置商品名称、城市名称等冗余字段
        return $nicheProduct->save(false);
    }
}
