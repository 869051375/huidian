<?php

namespace backend\models;

use common\models\DailyStatistics;
use yii\base\Model;

/**
 * Class TransactionSearch
 * @package backend\models
 */

class TransactionSearch extends Model
{

    public $starting_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['starting_time', 'end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '开始时间不能大于结束时间！');
        }
    }


    public function formName()
    {
        return '';
    }



    public function attributeLabels()
    {
        return [
            'starting_time' => '开始时间',
            'end_time' => '结束时间',
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

    public function search($params,$status = 2)
    {
        $this->load($params);
        if(!$this->validate())return null;
        $query = DailyStatistics::find();
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time);
        if(empty($this->starting_time) && empty($this->end_time))
        {
            $start_time = null;
            $closure_time = null;
            if($status==1)
            {   //昨天
                $start_time = mktime(0, 0 , 0,date("m"),date("d")-1,date("Y"));
                $closure_time = mktime(23,59,59,date("m"),date("d")-1,date("Y"));
            }elseif ($status==2)
            {   //本周
                $start_time = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
                $closure_time = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
            }elseif ($status==3)
            {   //本月
                $start_time = mktime(0, 0 , 0,date("m"),1,date("Y"));
                $closure_time = mktime(23,59,59,date("m") ,date("t"),date("Y"));
            }elseif ($status==4)
            {   //本年
                $start_time = mktime(0, 0 , 0,1,1,date("Y"));
                $closure_time = mktime(23,59,59,12,31,date("Y"));
            }
        }
        //时间
        $query->andWhere('date >= :start_time', [':start_time' => $start_time]);
        $query->andWhere('date <= :end_time', [':end_time' => $closure_time]);
        $data =  $query->orderBy(['date'=>SORT_ASC])->asArray()->all();
        return $data;
    }

    //上一周（昨天，月，年）的数据
    public function beforeData($status = 2)
    {
        $start_time = null;
        $closure_time = null;
        if($status==1)
        {   //昨天
            $start_time = mktime(0, 0 , 0,date("m"),date("d")-2,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),date("d")-2,date("Y"));
        }elseif ($status==2)
        {   //上一周
            $start_time = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"));
        }elseif ($status==3)
        {   //上一月
            $start_time = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),0,date("Y"));
        }elseif ($status==4)
        {   //上一年
            $start_time = mktime(0, 0 , 0,1,1,date("Y")-1);
            $closure_time = mktime(23,59,59,12,31,date("Y")-1);
        }
        return  DailyStatistics::find()
                ->andWhere('date >= :start_time', [':start_time' => $start_time])
                ->andWhere('date <= :end_time', [':end_time' => $closure_time])
                ->orderBy(['date'=>SORT_DESC])
                ->asArray()
                ->all();
    }
    
}
