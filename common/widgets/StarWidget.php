<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/11
 * Time: 下午1:14
 */

namespace common\widgets;


use common\utils\BC;
use yii\base\Widget;

class StarWidget extends Widget
{
    public $score = 5.00;
    public $maxScore = 5;

    public function run()
    {
        $floorScore = floor($this->score);

        for($i = 1; $i < $this->maxScore+1; $i++)
        {
            if($i <= $floorScore)
            {
                echo '<li class="evaluate-score-star1"></li>';
            }
            elseif($this->score > $i-1)
            {
                $decimalPart = BC::sub($this->score, $i-1);
                if($decimalPart < 0.31) // 三分之一颗星
                {
                    echo '<li class="evaluate-score-star4"></li>';
                }
                else if($decimalPart < 0.51) // 半颗星
                {
                    echo '<li class="evaluate-score-star2"></li>';
                }
                else // 0.5以上的，三分之二颗星
                {
                    echo '<li class="evaluate-score-star5"></li>';
                }
            }
            else
            {
                echo '<li class="evaluate-score-star3"></li>';
            }
        }
    }
}