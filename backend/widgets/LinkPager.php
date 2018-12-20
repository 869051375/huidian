<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/17
 * Time: 下午3:56
 */

namespace backend\widgets;


class LinkPager extends \yii\widgets\LinkPager
{
    public $firstPageLabel = '&laquo;';
    public $prevPageLabel = '&lsaquo;';
    public $nextPageLabel = '&rsaquo;';
    public $lastPageLabel = '&raquo;';
    public $options = ['class' => 'pagination pull-right'];
    public $totalTemplate = '<div class="pagination pull-right page-total-count" style="padding: 4px 10px;">总数：%s条</div>';

    public function run()
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        $buttons = $this->renderPageButtons();
        if($this->totalTemplate)
        {
            $buttons = sprintf($this->totalTemplate, $this->pagination->totalCount).$buttons;
        }
        echo $buttons;
    }
}