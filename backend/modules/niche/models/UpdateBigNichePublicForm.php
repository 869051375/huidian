<?php

namespace backend\modules\niche\models;

use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;



/**
 *
 * @SWG\Definition(required={}, @SWG\Xml(name="UpdateBigNichePublicForm"))
 */
class UpdateBigNichePublicForm extends Model
{
    /**
     * 公海id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 个人24小时内从商机大公海中提取的商机最大限制数量为
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $big_public_extract_max_sum;

    /**
     * 商机公海中的商机（）工作日不进行提取，将自动回收至商机大公海
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $big_public_not_extract;

    public function rules()
    {
        return [
            [['id','big_public_extract_max_sum','big_public_not_extract'], 'required'],
            [['id','big_public_extract_max_sum','big_public_not_extract'], 'integer'],
            ['id', 'validateNichePublic'],
        ];
    }

    public function validateNichePublic()
    {
        $model = new NichePublic();
        $niche = $model::find()->where(['id'=>$this->id])->one();
        if(empty($niche)){
            return $this->addError('id','此公海不存在');
        }
        if($niche->type != 1){
            return $this->addError('id','当前公海不是大公海');
        }
        return true;
    }

    public function save()
    {
        /** @var NichePublic $model */
        $model = NichePublic::find()->where(['id'=>$this->id])->one();
        $model->big_public_extract_max_sum = $this->big_public_extract_max_sum;
        $model->big_public_not_extract = $this->big_public_not_extract;
        $model->updated_at = time();
        return $model->save(false);
    }

}