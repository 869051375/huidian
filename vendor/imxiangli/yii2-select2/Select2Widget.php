<?php

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/7/20
 * Time: 17:12
 */

namespace imxiangli\select2;

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\InputWidget;

class Select2Widget extends InputWidget
{
	public $serverUrl = null;
	public $selectedItem = [];
	public $itemsName = 'items';
	public $nameField = 'name';
	public $searchKeywordName = 'keyword';
	public $pageName = 'page';
	public $pageCountField = 'page_count';
	public $cache = true;
	public $loading = '正在加载...';
	public $placeholder = '请选择';
	public $placeholderId = null;
	public $data = null;
	public $minimumInputLength = 0;
	public $static = false;
	public $language = 'zh-CN';
	public $width = null;
	public $is_person_select = '0';//不可传布尔型,1代表使用自定义人员下拉框,0为默认

	/** @var JsExpression */
	public $eventSelect = null;
	/** @var JsExpression */
	public $eventOpening = null;

	public function run()
	{
		$this->registerClientScript();
		if ($this->hasModel()) {
			return Html::activeDropDownList($this->model, $this->attribute, $this->selectedItem, $this->options);
		} else {
			return Html::dropDownList($this->name, $this->value, $this->selectedItem, $this->options);
		}
	}

	protected function registerClientScript()
	{
		$width = (null !== $this->width) ? ('width: "' . $this->width . '",') : '';
		Select2Asset::register($this->view)->js[] = 'js/i18n/' . $this->language . '.js';
		$placeholder = null;
		if (null !== $this->placeholder) {
			$placeholder = "placeholder: '{$this->placeholder}',";
			if (null !== $this->placeholderId) {
				$placeholderId = $this->placeholderId;

				if (null !== $this->placeholderId && StringHelper::countWords($this->placeholderId) <= 0) {
					$placeholderId = "''";
				}
				$placeholder = "placeholder: {id: {$placeholderId}, text: '{$this->placeholder}'},";
			}
		}

		$eventJsSelect = '';
		if ($this->eventSelect instanceof JsExpression) {
			$eventJsSelect = $this->eventSelect->expression;
		}
        $eventJsOpening = '';
        if ($this->eventOpening instanceof JsExpression) {
            $eventJsOpening = $this->eventOpening->expression;
        }

		if ($this->static) {
			$script = "$(function(){
					$('#{$this->options['id']}').select2({
						{$placeholder}
						{$width}
						language: 'zh-CN'
					}).on('select2:select', function(env){
						{$eventJsSelect}
					});
				});";
		} else {
			$data = '';
			$is_person_select = $this->is_person_select;
			$click_select = 'qq';
			if('{$this->is_person_select}' == '1'){
				$click_select = 'click_select';
			}
			if ($this->serverUrl !== null) {
				$data = "ajax: {
					url: function(){return getServerUrl()},
					dataType: 'json',
					delay: 250,
					data: function (params) {
					  return {
						{$this->searchKeywordName}: params.term, // search term
						{$this->pageName}: params.page
					  };
					},
					processResults: function (data, params) {
						var list = [];
						var is_person_select = false;
						params.page = params.page || 1;
						//判断是否是自定义人员下拉框
						if ('{$this->is_person_select}' == '1') {
							list = data.{$this->itemsName};
							var newArr = [];
							for (var keyItem in list) {
								var groupObj = {
									name: keyItem,
									children: []
								};
								list[keyItem].forEach(function(item){
									var opationObj = {
										// id: parseInt(Object.keys(item)[0]),
										// name: Object.values(item)[0].split('|')[0]
									};
									for(var prop in item) {
										opationObj.id = parseInt(prop);
										opationObj.name = item[prop].split('|')[0];
									}
									groupObj.children.push(opationObj);
								});
								newArr.push(groupObj);
							}
							list = newArr;
						}else{
							list = data.{$this->itemsName};
						}
						if(params.page <= 1)
						{	
							if ('{$this->is_person_select}' != '1') {
								list.unshift({{$this->nameField}: '{$this->placeholder}', id: '{$this->placeholderId}'});
							}
						}
						return {
							results: list,
							pagination: {
								more: params.page < data.{$this->pageCountField}
							}
						};
					},
					cache: " . ($this->cache ? 'true' : 'false') . "
				  },";
			} else if (!is_array($this->data)) {
				$data = 'data: ' . json_encode($this->data) . ',';
			}
			$script = "$(function(){
			var serverUrl = '" . Url::to($this->serverUrl) . "';
			function getServerUrl(){
			    return serverUrl;
			}
			
			$('#{$this->options['id']}').select2({
				language: 'zh-CN',
				{$placeholder}
			 	{$data}
				escapeMarkup: function (markup) { return markup; },
			 	minimumInputLength: {$this->minimumInputLength},
				{$width}
				templateResult: function (repo) {
					if (repo.loading) return '{$this->loading}';
					var markup = repo.{$this->nameField};
					return markup;
				},
				templateSelection: function (repo) {
				  	return repo.{$this->nameField} || repo.text;
				},
				dropdownCssClass: 'click-select'
			}).on('select2:select', function(env){
				{$eventJsSelect}
			}).on('select2:opening', function(env){
				{$eventJsOpening}
			}).on('select2:open', function(){
				if('{$this->is_person_select}' == '1'){
					$('.click-content').remove();
					var letterItems = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
					var clickCon = $('<div class=\'click-content\'></div>');
					letterItems.forEach(function(item,index) {
						clickCon.append('<div data-check-letter=' + index + ' class=\'letter-item\'>' + item + '</div>');
					});
					$(clickCon).insertBefore('.select2-results');
					$('.letter-item').on('click',function(e){
						$(this).addClass('active').siblings().removeClass('active');
						var clickIndex = parseInt($(this).attr('data-check-letter'));
						var top = 0;
						[].slice.call($('.select2-results__options[role=\'tree\'] li[role=\'group\']')).forEach(function (item, index) {
							if (index < clickIndex) {
								top += parseInt($(item).css('height'));
							}
						});
						$('.select2-results__options[role=\'tree\']').scrollTop(top);
					});
				}
			});
		});";
		}
		$this->view->registerJs($script, View::POS_READY);
	}
}
