<?php 
namespace backend\models;

use yii\base\Model;
// use common\models\CluePublic;//线索公海
// use common\models\CustomerPublic;//客户公海


class CrmSynchronizationSeaForm extends Model
{

	 //线索公海
	 public $clue_public_id; 
	 public $clue_public_name;
	 //客户公海 
	 public $customer_public_id;
	 public $customer_public_name;


   public function attributeLabels()
   {
      return [
          'clue_public_id' => '注册用户数据（未下单）同步分配',
          'customer_public_id' => '注册用户数据（已下单）同步分配',
      ];
   }


   public function rules()
   {
      return [
          [['clue_public_id', 'customer_public_id'], 'integer', 'message' => '数据不能为空'],
      ];
    }

    //获取线索公海信息
   public function GetClueSea()
   {
    return $cluePublic=CluePublic::find()->select('id, name')->where(['=','status',1])->all();
   }


    //获取客户公海信息
   public function GetCustomerSea()
   {
    return $customerPublic=CustomerPublic::find()->select('id, name')->where(['=','status',1])->all();
   }


    //入库
   public function Create($data)
   {    
      $things=Yii::$app->db->beginTransaction();
        try{
           
            $things->commit();

        }catch(Exception $e){
            $things->rollBack();

        }
     
   }




}
