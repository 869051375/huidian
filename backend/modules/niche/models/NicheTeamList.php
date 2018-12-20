<?php

namespace backend\modules\niche\models;


use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\NicheTeam;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheTeamList"))
 */
class NicheTeamList extends Model
{

    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;


    public function rules()
    {
        return [
            [['niche_id'], 'required'],
            [['niche_id'],'integer']
        ];

    }


    public function queryNicheTeam($administrator)
    {
        $list = NicheTeam::find()->where(['niche_id'=>$this->niche_id])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        $niche = \common\models\Niche::find()->where(['id'=>$this->niche_id])->one();
        $data = [
            'id'=>0,
            'niche_id'=>$this->niche_id,
            'administrator_id' => isset($niche->administrator_id)? $niche->administrator_id : '',
            'administrator_name' => isset($niche->administrator_name) ?$niche->administrator_name :'',
            'is_update' => 1,
            'sort' => '1',
            'create_at' => isset($niche->created_at)?date('Y-m-d',$niche->created_at):"",
            'updated_at' => date('Y-m-d',time()),
        ];
        if(!empty($list)){
            array_unshift($list,$data);
        }else{
            $list[0] = $data;
        }
        foreach($list as $key=>$value){
            $admin = Administrator::find()->where(['id'=>$value['administrator_id']])->asArray()->one();
            $res =  Administrator::getParentDepartmentId($admin['department_id']);
            $department = CrmDepartment::find()->where(['in','id',$res])->asArray()->all();
            $department_name = '';
            foreach ($department as $kk=>$vv){
                $department_name .= $vv['name'].'/';
            }
            $list[$key]['department_name'] = substr($department_name,0,strlen($department_name)-1);

        }
        $re[] = $list;
        if($administrator->id == $niche->administrator_id){
            $re['is_self'] = 1;
        }else{
            $re['is_self'] = 0;
        }

        return $re;
    }


}