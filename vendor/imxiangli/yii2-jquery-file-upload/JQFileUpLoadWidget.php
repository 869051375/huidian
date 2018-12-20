<?php

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/7/20
 * Time: 17:12
 */

namespace imxiangli\upload;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\InputWidget;

class JQFileUpLoadWidget extends InputWidget
{
	public $serverUrl = null;
	public $buttonTitle = '选择文件';
	public $labelClass = 'btn btn-default btn-upload';
	public $icon = '<span class="fa fa-upload"></span>';
    public $formData = [];
	/** @var JsExpression */
	public $done = null;

	/** @var JsExpression */
	public $submit = null;

	public function init()
	{
		parent::init();
		if ($this->done === null) {
			$this->done = new JsExpression('function (e, data) {}');
		}
		if ($this->serverUrl === null) {
			throw new InvalidConfigException("Either 'serverUrl' property must be specified.");
		}
	}

	public function run()
	{
		$this->registerClientScript();
		if (isset($this->options['class'])) {
			$this->options['class'] .= ' sr-only';
		} else {
			$this->options['class'] = 'sr-only';
		}

		if ($this->hasModel()) {
			$this->options['name'] = $this->name;
			$input = Html::activeFileInput($this->model, $this->attribute, ArrayHelper::merge($this->options, [
				'data-url' => Url::to($this->serverUrl)
			]));
		} else {
			$input = Html::fileInput($this->name, null, ArrayHelper::merge($this->options, [
				'data-url' => Url::to($this->serverUrl)
			]));
		}
		return Html::label($input . $this->icon . ' ' . $this->buttonTitle, $this->options['id'], [
			'class' => $this->labelClass,
			'title' => $this->buttonTitle,
		]);
	}

	protected function registerClientScript()
	{
	    $submit = '';
	    if($this->submit)
        {
            $submit = ",submit: {$this->submit}";
        }
	    $formDate = Json::encode($this->formData, 336);
		JQueryFileUploadAsset::register($this->view);
		$script = "$('#{$this->options['id']}').fileupload({
        dataType: 'json',
        formData: {$formDate}, 
        done: {$this->done}
        {$submit}
});";
		$this->view->registerJs($script, View::POS_READY);
	}
}
