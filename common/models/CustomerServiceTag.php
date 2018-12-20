<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_service_tag}}".
 *
 * @property integer $id
 * @property integer $customer_service_id
 * @property string $tag
 * @property integer $count
 */
class CustomerServiceTag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_service_tag}}';
    }

    public static function addTag($customer_service_id, $tag)
    {
        /** @var CustomerServiceTag $ccTag */
        $ccTag = CustomerServiceTag::find()->where(['customer_service_id' => $customer_service_id, 'tag' => $tag])->one();
        if(null == $ccTag)
        {
            $ccTag = new CustomerServiceTag();
            $ccTag->customer_service_id = $customer_service_id;
            $ccTag->tag = $tag;
            $ccTag->count = 1;
        }
        else
        {
            $ccTag->count = $ccTag->count + 1;
        }
        $ccTag->save(false);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_service_id', 'count'], 'integer'],
            [['tag'], 'string', 'max' => 10],
            [['customer_service_id', 'tag'], 'unique', 'targetAttribute' => ['customer_service_id', 'tag'], 'message' => 'The combination of Customer Service ID and Tag has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_service_id' => 'Customer Service ID',
            'tag' => 'Tag',
            'count' => 'Count',
        ];
    }
}
