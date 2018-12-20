<?php

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/7/20
 * Time: 17:12
 */

namespace imxiangli\ckeditor;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

class CKEditorWidget extends InputWidget
{
	public $clientOptions = [];

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		$this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
		parent::init();
	}

	public function run()
	{
		$this->registerClientScript();
		if ($this->hasModel()) {
			return Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id]);
		} else {
			return Html::textarea($this->id, $this->value, ['id' => $this->id]);
		}
	}

	protected function registerClientScript()
	{
		CKEditorAsset::register($this->view);
		$clientOptions = Json::encode($this->clientOptions);
		$script = "jQuery('#" . $this->id . "').ckeditor({$clientOptions});";
		$this->view->registerJs($script, View::POS_READY);
	}
}