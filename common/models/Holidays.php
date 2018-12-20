<?php

namespace common\models;

use DateInterval;
use DateTime;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%holidays}}".
 *
 * @property integer $year
 * @property string $holidays
 */
class Holidays extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%holidays}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year'], 'filter', 'filter' => 'trim'],
            [['year'], 'required'],
            [['year'], 'integer', 'min' => '1970', 'max' => date('Y')+5, 'tooBig' => '{attribute}不能大于{max}年。'],
            [['year'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'year' => '年份',
        ];
    }

    public function getDays()
    {
        return explode(',', trim($this->holidays, ','));
    }

    public function setDays($days)
    {
        if(!$days){
            $this->holidays = '';
        }
        else{
            $this->holidays = implode(',', $days);
        }
    }

    public static function getEndTimeByDays($days, $start_date = null)
    {
        $start_date = $start_date ? $start_date : date('Y-m-d H:00:00');
        $date = new DateTime($start_date);
        $hs = array();
        if($days > 0)
        {
            for($i = 0; $i < $days; )
            {
                $date->add(new DateInterval('P1D'));
                $year = $date->format('Y');
                if(!isset($hs[$year]))
                {
                    /** @var Holidays $h */
                    $h = Holidays::find()->where(['year' => $year])->one();
                    $hs[$year] = $h ? explode(',',$h->holidays) : array();
                }
                if(!in_array($date->format('Ymd'), $hs[$year]))
                {
                    $i++;
                }
            }
        }
        return $date->getTimestamp();
    }

    /**
     * 获得指定日期的上一个工作日
     * @param string $date 格式为：yyyy-MM-dd
     * @return int
     */
    public static function getPreWorkDay($date)
    {
        $dateObj = new DateTime($date);
        $hs = array();
        while(true)
        {
            $dateObj->sub(new DateInterval('P1D'));
            $year = $dateObj->format('Y');
            if(!isset($hs[$year]))
            {
                /** @var Holidays $h */
                $h = Holidays::find()->where(['year' => $year])->one();
                $hs[$year] = $h ? explode(',',$h->holidays) : array();
            }
            if(in_array($dateObj->format('Ymd'), $hs[$year]))
            {
                continue;
            }
            break;
        }
        return $dateObj->getTimestamp();
    }

    //工作日
    static function workDay($maxTime, $moveTime)
    {
        $day = date('Ymd', $maxTime);
        $year = (int)date('Y',$maxTime);//根据年份找到本年份对应的休息日
        $holidays = Holidays::findOne($year);
        //未设置工作日，不能抛商机
        if(null == $holidays)
        {
            return 0;
        }
        $days = $holidays->getDays();//获取当前年份设置的节假日
        unset($holidays);
        $time = self::effectiveTime($day,$moveTime,$days,$year);
        unset($day);
        unset($moveTime);
        unset($days);
        unset($year);
        if($time > 0)
        {
            $time = strtotime($time)+86399;//当天最大时间23:59:59
        }
        return $time;
    }

    /**
     * 求取从某日起经过一定天数后的日期,排除周六周日和节假日(后台设置)
     * examples:输入(2018-10-26,5,''),得到2018-11-02
     * @param string $start 开始日期
     * @param int $offset 经过天数
     * @param [] $exception 例外的节假日
     * @param int $year
     * @return false|string
     */
    static function effectiveTime($start='now', $offset=0, $exception=[], $year)
    {
        $startTime = strtotime($start);
        $tmpTime = $startTime + 24*3600;
        unset($startTime);
        $tmpDay = $start;
        while($offset > 0){
            $tmpDay = date('Ymd', $tmpTime);
            $newYear = date("Y", strtotime($tmpDay));//判断是否下一年，再根据年份找到本年份对应的休息日
            $bfd = false;//是否节假日
            if($year != $newYear)
            {
                $year = date('Y',strtotime($tmpDay));//根据年份找到本年份对应的休息日
                $holidays = Holidays::findOne($year);
                //未设置工作日，不能抛商机
                if(null == $holidays)
                {
                    return 0;
                }
                $exception = $holidays->getDays();//获取当前年份设置的节假日
                if(is_array($exception))
                {
                    $bfd = in_array($tmpDay,$exception);
                }
                else
                {
                    $bfd = ($exception == $tmpDay);
                }
            }
            else
            {
                if(is_array($exception))
                {
                    $bfd = in_array($tmpDay,$exception);
                }
                else
                {
                    $bfd = ($exception == $tmpDay);
                }
            }
            if(!$bfd){//不是周末和节假日
                $offset--;
            }
            $tmpTime += 24*3600;
        }
        unset($exception);
        unset($holidays);
        return $tmpDay;
    }
}
