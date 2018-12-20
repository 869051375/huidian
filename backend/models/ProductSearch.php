<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/3
 * Time: 下午1:17
 */

namespace backend\models;

use common\models\Company;
use common\models\CrmDepartment;
use common\models\Flow;
use common\models\Product;
use common\models\ProductCategory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class ProductSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property CrmDepartment $department
 * @property Flow $flow
 * @package backend\models
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 */
class ProductSearch extends Model
{
    const SHOW_TYPE_HOME = 1;
    const SHOW_TYPE_HOT = 2;

    public $top_category_id;
    public $category_id;
    public $flow_id;
    public $department_id;
    public $status;
    public $show_type;
    public $keyword;
    public $company_id;
    public $platform_id;

    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['top_category_id', 'category_id', 'flow_id', 'department_id', 'status', 'show_type', 'company_id','platform_id'], 'integer'],
            ['show_type', 'in', 'range' => [self::SHOW_TYPE_HOME, self::SHOW_TYPE_HOT]],
            ['keyword', 'safe'],
        ];
    }

    public static function getShowTypeList()
    {
        return [self::SHOW_TYPE_HOME => '首页', self::SHOW_TYPE_HOT => '热门'];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'top_category_id' => '类目',
            'flow_id' => '流程',
            'status' => '状态',
            'show_type' => '显示类型',
            'keyword' => '关键词',
            'company_id' => '商机部门',
            'platform_id' => '商品来源',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string $status
     *
     * @return ActiveDataProvider
     */
    public function search($params, $status = null)
    {
        $query = Product::find()->alias('p');;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'p.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'p.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'p.flow_id' => $this->flow_id <= 0 ? null : $this->flow_id,
            'p.status' => $this->status,
            'p.platform_id' => $this->platform_id,
        ]);

        if($this->company_id > 0)
        {
            $query->innerJoinWith(['opportunityAssignDepartments o']);
            $query->andFilterWhere(['o.company_id' => $this->company_id]);
        }
        if($this->department_id > 0)
        {
            $query->innerJoinWith(['opportunityAssignDepartments o']);
            $query->andFilterWhere(['o.department_id' => $this->department_id]);
        }
        if(!empty($this->show_type))
        {
            if($this->show_type == self::SHOW_TYPE_HOME)
            {
                $query->andFilterWhere(['or', ['p.is_home' => 1], ['p.is_home_nav' => 1]]);
            }
            else
            {
                $query->andFilterWhere(['p.is_hot' => 1]);
            }
        }
        if(null !== $status)
        {
            if($status == Product::PACKAGE_ACTIVE)
            {
                $query->andWhere(['p.is_package' => Product::PACKAGE_ACTIVE]);
            }
            else
            {
                $query->andWhere(['p.is_package' => Product::PACKAGE_DISABLED]);
            }
        }
        $query->andFilterWhere(['or', ['like', 'p.alias', $this->keyword], ['like', 'p.alias', $this->keyword], ['like', 'p.spec_name', $this->keyword]]);

        return $dataProvider;
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }
    public function getFlow()
    {
        return Flow::find()->where(['id'=> $this->flow_id])->one();
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id'=> $this->department_id])->one();
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()
            ->where(['id' => $this->department_id, 'company_id' => $this->company_id])->one();
    }
}