<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_new_custom_field".
 *
 * @property string $id 自增id
 * @property int $administrator_id 人员ID
 * @property string $field 字段
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property string $type 类型 (1:我的个人客户；2：我的企业客户；3：公海个人客户；4：公海企业客户)
 */
class CustomerNewCustomField extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_new_custom_field';
    }

    public $field_all;
    const TYPE_1 = 1;   //我的个人客户
    const TYPE_2 = 2;   //我的企业客户
//    const TYPE_3 = 3;   //公海个人客户
//    const TYPE_4 = 4;   //公海企业客户
    const TYPE_10 = 10;  //获取默认列表


    public static $customer_field1 = [
        ['field' => 'company_name', 'field_name' => '姓名', 'sort' => '4', 'status' => 1, 'is_update' => 0],
        ['field' => 'phone', 'field_name' => '联系电话', 'sort' => '5', 'status' => 1, 'is_update' => 0],
        ['field' => 'username', 'field_name' => '负责人', 'sort' => '6', 'status' => 1, 'is_update' => 1],
        ['field' => 'next_record', 'field_name' => '下次跟进时间', 'sort' => '7', 'status' => 1, 'is_update' => 1],
        ['field' => 'last_operation_creator_name', 'field_name' => '最后维护人', 'sort' => '8', 'status' => 1, 'is_update' => 1],
        ['field' => 'operation_time', 'field_name' => '最后维护时间', 'sort' => '9', 'status' => 1, 'is_update' => 1],
        ['field' => 'created_at', 'field_name' => '创建时间', 'sort' => '10', 'status' => 1, 'is_update' => 1],
        ['field' => 'source_name', 'field_name' => '来源', 'sort' => '11', 'status' => 1, 'is_update' => 1],
        ['field' => 'channel_name', 'field_name' => '来源渠道', 'sort' => '12', 'status' => 1, 'is_update' => 1],
        ['field' => 'level', 'field_name' => '客户级别', 'sort' => '13', 'status' => 1, 'is_update' => 1],
        ['field' => 'tag_name', 'field_name' => '标签', 'sort' => '14', 'status' => 1, 'is_update' => 1],
        ['field' => 'yichengjiao', 'field_name' => '赢单商机数', 'sort' => '15', 'status' => 1, 'is_update' => 1],
        ['field' => 'yishibai', 'field_name' => '输单商机数', 'sort' => '18', 'status' => 1, 'is_update' => 1],
        ['field' => 'weichengjiao', 'field_name' => '未成交商机数', 'sort' => '19', 'status' => 1, 'is_update' => 1],
        ['field' => 'daitiqu', 'field_name' => '待提取商机数', 'sort' => '20', 'status' => 1, 'is_update' => 1],
        ['field' => 'order_yifukuan', 'field_name' => '已付款订单数', 'sort' => '21', 'status' => 1, 'is_update' => 1],
        ['field' => 'order_weifukuan', 'field_name' => '未付款订单数', 'sort' => '22', 'status' => 1, 'is_update' => 1],
        ['field' => 'customer_number', 'field_name' => '客户编号', 'sort' => '23', 'status' => 1, 'is_update' => 1],
        ['field' => 'get_way', 'field_name' => '获取方式', 'sort' => '24', 'status' => 1, 'is_update' => 1],
    ];
    public static $customer_field2 = [
        ['field' => 'company_name', 'field_name' => '客户名称', 'sort' => '4', 'status' => 1, 'is_update' => 0],
        ['field' => 'phone', 'field_name' => '联系电话', 'sort' => '5', 'status' => 1, 'is_update' => 0],
        ['field' => 'username', 'field_name' => '负责人', 'sort' => '6', 'status' => 1, 'is_update' => 1],
        ['field' => 'next_record', 'field_name' => '下次跟进时间', 'sort' => '7', 'status' => 1, 'is_update' => 1],
        ['field' => 'last_operation_creator_name', 'field_name' => '最后维护人', 'sort' => '8', 'status' => 1, 'is_update' => 1],
        ['field' => 'operation_time', 'field_name' => '最后维护时间', 'sort' => '9', 'status' => 1, 'is_update' => 1],
        ['field' => 'created_at', 'field_name' => '创建时间', 'sort' => '10', 'status' => 1, 'is_update' => 1],
        ['field' => 'source_name', 'field_name' => '来源', 'sort' => '11', 'status' => 1, 'is_update' => 1],
        ['field' => 'channel_name', 'field_name' => '来源渠道', 'sort' => '12', 'status' => 1, 'is_update' => 1],
        ['field' => 'level', 'field_name' => '客户级别', 'sort' => '13', 'status' => 1, 'is_update' => 1],
        ['field' => 'tag_name', 'field_name' => '标签', 'sort' => '14', 'status' => 1, 'is_update' => 1],
        ['field' => 'yichengjiao', 'field_name' => '赢单商机数', 'sort' => '15', 'status' => 1, 'is_update' => 1],
        ['field' => 'yishibai', 'field_name' => '输单商机数', 'sort' => '18', 'status' => 1, 'is_update' => 1],
        ['field' => 'weichengjiao', 'field_name' => '未成交商机数', 'sort' => '19', 'status' => 1, 'is_update' => 1],
        ['field' => 'daitiqu', 'field_name' => '待提取商机数', 'sort' => '20', 'status' => 1, 'is_update' => 1],
        ['field' => 'order_yifukuan', 'field_name' => '已付款订单数', 'sort' => '21', 'status' => 1, 'is_update' => 1],
        ['field' => 'order_weifukuan', 'field_name' => '未付款订单数', 'sort' => '22', 'status' => 1, 'is_update' => 1],
        ['field' => 'customer_number', 'field_name' => '客户编号', 'sort' => '23', 'status' => 1, 'is_update' => 1],
        ['field' => 'get_way', 'field_name' => '获取方式', 'sort' => '24', 'status' => 1, 'is_update' => 1],
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['administrator_id', 'created_at', 'updated_at', 'type'], 'integer'],
            [['type'], 'required'],
            [['field'], 'validateField'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'administrator_id' => 'Administrator ID',
            'field' => 'Field',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'type' => 'Type',
        ];
    }

    public function getCustomerField()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        $rs = CustomerNewCustomField::find()->where(['administrator_id' => $administrator->id, 'type' => $this->type])->one();

        if ($rs == null) {
            if ($this->type == self::TYPE_1) {
                $rs = self::$customer_field1;
            } else if ($this->type == self::TYPE_2) {
                $rs = self::$customer_field2;
            } else {
                $rs = [];
            }
        } else {
            $rs = json_decode($rs['field'], true);
        }

        return $rs;
    }

    public function customerFieldUpdate()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        /** @var CustomerNewCustomField $model */
        $model = CustomerNewCustomField::find()->where(['administrator_id' => $administrator->id, 'type' => $this->type])->one();

        if ($model == null) {
            $model = New CustomerNewCustomField();
            $model->created_at = time();
        } else {
            $model->updated_at = time();
        }

        if($this -> field == null){
            if($this -> type == self::TYPE_1){
                $model ->field = json_encode(self::$customer_field1);
            }else{
                $model ->field = json_encode(self::$customer_field2);
            }
        }else{
            $model->field = json_encode($this->field);

        }
        $model->administrator_id = $administrator->id;
        $model->type = $this->type;


        return $model->save(false);
    }

    public function validateField()
    {
        if (!in_array($this->type, [self::TYPE_1, self::TYPE_2])) {
            return $this->addError('type', '提交的字段有误');
        }

        if ($this->type == self::TYPE_1) {
            $field = array_column(self::$customer_field1, 'field');
        } elseif ($this->type == self::TYPE_2) {
            $field = array_column(self::$customer_field2, 'field');
        }

        if($this ->type == self::TYPE_1){
            $this->field_all = self::$customer_field1;
        }else if($this ->type == self::TYPE_2){
            $this->field_all = self::$customer_field2;
        }

        if (is_array($this->field)) {
            $field_new = array_column($this->field, 'field');

            //先判断长度是否相等，不相等直接抛错
            if (count($field_new) != count($field)) {
                return $this->addError('field', '提交的字段有误');
            }

            //再判断提交的字段是否全部一致，不一致直接抛错
            for ($i = 0; $i < count($field_new); $i++) {
                if (!in_array($field_new[$i], $field)) {
                    return $this->addError('field', '提交的字段有误');
                }
            }

            //不可移动的字段如果被移动，直接抛错
            foreach ($this->field_all as $k => $v) {
                if ($v['is_update'] == 0) {
                    //在判断不可以移动的是否被移动
                    if ($this->field[$k] != $this->field_all[$k]) {
                        return $this->addError('field', '提交的字段有误');
                    }
                }
            }

        } else {
            $this->field = $this->field_all;
        }
        return true;
    }
}
