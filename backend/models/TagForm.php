<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\Tag;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class TagForm extends Model
{
    public $company_id;
    public $type;
    public $name;
    public $color;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var array
     */
    public $full_names;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['name', 'color', 'type'], 'required', 'on' => ['insert']],
            ['type', 'in', 'range' => [Tag::TAG_CUSTOMER, Tag::TAG_OPPORTUNITY]],
            [['company_id', 'type'], 'integer'],
            [['name', 'color'], 'string', 'max' => 6],
            ['company_id', 'validateCompanyId'],
        ];
    }

    public function validateCompanyId()
    {
//        /** @var Administrator $administrator */
//        $administrator = \Yii::$app->user->identity;
//        $this->customer = CustomerTag::find()->where(['customer_id' => $this->customer_id, 'tag_id' => $this->tag_id]);
//        if(null == $this->customer)
//        {
//            $this->addError('id', '客户不存在');
//        }
//        else if($this->customer->is_receive)
//        {
//            $this->addError('id', '该客户已经被转入，不能进行该操作');
//        }
//        else if($this->customer->administrator_id != $administrator->id)
//        {
//            $this->addError('id', '您不能转入其他人的客户');
//        }
    }

    public function attributeLabels()
    {
         return [
             'name' => '标签名称',
             'color' => '标签颜色',
         ];
    }

    /**
     * 新增标签
     * @return bool
     */
    public function save()
    {
        $model = new Tag();
        $model->load($this->attributes, '');
        if(!$this->validate()) return false;
         /** @var Administrator $administrator */
         $administrator = \Yii::$app->user->identity;
         $model->company_id =  $administrator->company_id;
         $model->type = $this->type;
        return $model->save(false);
    }

    /**
     * @return bool
     * @throws Exception
     */

    public function update()
    {
        if(!$this->validate())
        {
            return false;
        }

        else
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                foreach($this->full_names as $name)
                {
                    /** @var Tag $model */
                    $model = Tag::find()->where(['id' => $name['id']])->one();

                    $model->name = $name['full_name'];
                    $model->save(false);
                }
                $t->commit();
                return true;
            }
            catch (Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
    }

    public function validateCompanyFullNames($full_names)
    {
        foreach($full_names as $name)
        {
            $len = mb_strlen($name['full_name'],Yii::$app->charset);

            if($len > 6)
            {
                $this->addError('full_name', '标签名称只能包含至多6个字符。');
                return false;
            }
        }
        return true;
    }

    public function validateCompany($post)
    {
        foreach($post['param'] as $name)
        {
            $len = mb_strlen($name['full_name'],Yii::$app->charset);
            /** @var Tag $model */
            $model = Tag::find()->where(['id' => $name['id']])->one();
            /** @var Administrator $administrator */
            $administrator = \Yii::$app->user->identity;
            if($model->company_id != $administrator->company_id){
                return '您没有权限修改非自己的标签';
            }
            if($len > 6)
            {
               return '标签名称只能包含至多6个字符。';
            }
        }
    }
}