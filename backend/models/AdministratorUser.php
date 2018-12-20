<?php
namespace backend\models;

use common\models\Administrator;
use yii\web\User;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/20
 * Time: 下午6:02
 */

class AdministratorUser extends User
{
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        /** @var Administrator $administrator */
        $administrator = $this->identity;
        if ($administrator && $administrator->isRoot()) return true;
        return parent::can($permissionName, $params, $allowCaching);
    }
}