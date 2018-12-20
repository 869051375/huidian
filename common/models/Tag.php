<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $type
 * @property string $name
 * @property string $color
 */
class Tag extends \yii\db\ActiveRecord
{
    const TAG_CUSTOMER = 0;         //客户标签
    const TAG_OPPORTUNITY = 1;      //商机标签
    const TAG_CLUE = 2;             //线索标签


    /**
     * @var Tag
     */
    public $tag;

    /**
     * @var Tag
     */
    public $tag_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id','id', 'type'], 'integer'],
            [['name', 'color'], 'string', 'max' => 6],
            [['name', 'color'], 'required','on' => 'add'],//必填
            [['id','name'], 'required','on'=>'update'],//必填
            [[ 'color'], 'match', 'pattern' => '/^[a-z\d]*$/i','message'=>'color编码错误'],
            ['id','validateId','on'=>'update'],
            ['name','validateName','on'=>'add'],
        ];
    }

    public function validateId()
    {
        $this->tag = Tag::findOne($this->id);
        if($this->tag == null)
        {
            $this->addError('id','找不到指定的标签');
        }

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        if($identity->company_id != $this->tag->company_id) {
            $this->addError('id','您没有权限修改非自己的标签');
        }
    }

    public function validateName()
    {
        $this->tag_name = Tag::find()->where(['name' => $this->name,'company_id'=>$this->company_id]) -> one();
        if($this->tag_name != null)
        {
            $this->addError('name','不得重复添加');
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '标签ID',
            'company_id' => 'Company ID',
            'type' => 'Type',
            'name' => '标签名称',
            'color' => '标签颜色',
        ];
    }

    //获取列表
    public function getList()
    {
        $query = Tag::find()->where(['type'=>Tag::TAG_CUSTOMER]);
        if($this->company_id != 0 || $this->company_id !=''){
            $query -> andWhere(['company_id' => $this->company_id]);
        }
        $rs = $query ->all();
        return  $rs;
    }

    //获取线索列表
    public function getClueList(){
        $query = Tag::find()->where(['type'=>Tag::TAG_CLUE]);
        if($this->company_id != 0 || $this->company_id !=''){
            $query -> andWhere(['company_id' => $this->company_id]);
        }
        $rs = $query ->all();
        return  $rs;
    }

    //修改
    public function change($data)
    {
        foreach ($data as $item){
            $tag = Tag::find()->where(['id'=>$item['id']])->one();
            if ($tag){
                $tag->color = $item['color'];
                $tag->name = $item['name'];
                $tag->save();
            }
        }
        return true;
    }

    //新增
    public function inserts()
    {
        if (!$this->validate()){
            return false;
        }
        return $this->save(false);
    }

    public function select()
    {
       var_dump($this->company_id);die;

    }

}
