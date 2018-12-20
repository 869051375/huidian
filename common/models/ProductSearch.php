<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\Query;

class ProductSearch extends Model
{
    public $keyword;

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @var Query
     */
    public $query;

    public function rules()
    {
        return [
            [['keyword'], 'filter', 'filter' => 'trim'],
            [['keyword'], 'default', 'value' => '公司注册']
        ];
    }

    public function formName()
    {
        return '';
    }

    public function search($pageSize = 20)
    {
        $this->validate();
        $keyword = $this->keyword;
        $keyword = substr($keyword, 0, 560);
        // echo "<pre>";
        // print_r( $keyword);die;

        $fields = array('name', 'spec_name', 'alias', 'keywords');
        $scores = array(5, 3, 3, 2);
        $params = [];
        $clause = [];
        $score = [];
        for($j=0;$j<count($fields);$j++)
        {
            $clause[] = ' (`'.$fields[$j].'` LIKE :k'.$j.') ';
            $score[] = ' IF(LOCATE(:lk'.$j.', `'.$fields[$j].'`), '.$scores[$j].', 0)';
            if($fields[$j] == 'keywords')
            {
                $params[':k'.$j] = '%'.$keyword.'%';
                $params[':lk'.$j] = '%'.$keyword.'%';
            }
            else
            {
                $params[':k'.$j] = '%'.$keyword.'%';
                $params[':lk'.$j] = '%'.$keyword.'%';
            }
        }

        $sql = "SELECT *, (".implode("+",$score).") AS score FROM ".Product::tableName()." WHERE (".implode(" OR ",$clause).") AND `status`='1' AND `is_show_list`='1' ORDER BY score DESC";

        $this->pagination = new Pagination([
            'totalCount' => Yii::$app->db->createCommand($sql, $params)->query()->count(),
        ]);
        $this->pagination->setPageSize($pageSize);
        $this->pagination->validatePage = false;

        $this->query = Product::findBySql($sql." LIMIT ".$this->pagination->limit." OFFSET ".$this->pagination->offset, $params);

        return $this->query;
    }
}
