<?php

namespace backend\models;

use common\models\CrmCustomer;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Class OrderUserSearch
 * @package backend\models
 */

class OrderUserSearch extends Model
{
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'required','message' => '对不起，您输入的有误！请精确输入客户名称或联系方式！'],
            [['keyword'], 'string'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    /**
     * @return array|null|string
     */
    public function search()
    {
        if (!$this->validate()) return 2;
        $data = User::find()->select('u.id,c.name,u.phone,u.source_name,u.created_at,u.last_login')->alias('u')
//        ->leftJoin("(SELECT id,phone,name FROM ".CrmCustomer::tableName()." WHERE phone = '".$this->keyword."' OR name = '".$this->keyword."') as c" , "c.id = u.customer_id")
            ->leftJoin(['c'=>CrmCustomer::tableName()],'u.id=c.user_id')
        ->where(['is_vest' => User::VEST_NO])
        ->andWhere(['or', ['u.phone' => $this->keyword], ['c.name' => $this->keyword],['c.phone' => $this->keyword]])
        ->all();
        if(empty($data)) return null;
        /**@var $item User**/
        $users = [];
        foreach($data as $i => $item)
        {
            $users[$i]['id'] = $item->id;
            $users[$i]['name'] = $item->name;
            $users[$i]['phone'] = $item->phone;
            $users[$i]['source_name'] = $item->customer->getSourceName() ? $item->customer->getSourceName() : '--';
            $users[$i]['created_at'] = $item->isRegister() ? Yii::$app->formatter->asDatetime($item->created_at) : '未注册';
            $users[$i]['last_login'] = empty($item->last_login)? '--' :Yii::$app->formatter->asDatetime($item->last_login);
        }
        return $users;
    }
}
