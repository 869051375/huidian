<?php
namespace backend\models;

use common\models\Administrator;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * PasswordForm
 */
class pagenation extends Model{
    //数组动态分页
    public  function  pagenation($data,$page,$page_size){
        $total=count($data);

        $provider = new \yii\data\ArrayDataProvider([
            'allModels' =>  $data,

            'pagination' => [
                'pageSize' => $page_size,               //分页大小
                'page' => $page,               //当前页大小
            ],
        ]);
        return $data=['total'=>$total,'page'=>$page,'data'=>$provider->getModels()];
    }

    //分页返回的data封装
    public  function  data($total,$page,$data){
        return $datalist=[
            'total'=>$total,
            'page'=>$page,
            'data'=>$data,
        ];
    }
}