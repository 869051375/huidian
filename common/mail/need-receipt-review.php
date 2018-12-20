<?php
/* @var $this yii\web\View */
/** @var \common\models\MessageRemind $messageRemind */

use common\models\BusinessSubject;
use common\utils\Decimal;
$pc_domain = rtrim(\common\models\Property::get('pc_domain'), '/');
$pc_domain = str_replace(['www', 'test', 'lix'], 'admin', $pc_domain);
$name = '';
$department = '';
if($messageRemind->administrator)
{
    $name = $messageRemind->administrator ? $messageRemind->administrator->name : '';
    $department = $messageRemind->administrator->department ? $messageRemind->administrator->department->name : '';
}
?>
<p><?= $messageRemind->message;?></p>
<br/><?= $messageRemind->created_at > 0 ? Yii::$app->formatter->asDatetime($messageRemind->created_at) : 0 ;?> 需由  <?= $department.'-'.$name;?><a href="<?= $pc_domain ?>">马上去处理！</a>





