<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2018/1/3
 * Time: 下午1:38
 */

namespace common\widgets;


use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class CKEditorWidget extends \imxiangli\ckeditor\CKEditorWidget
{
    public function init()
    {
        $this->clientOptions = ArrayHelper::merge([
            'extraPlugins' => 'justify,uploadimage,image2',
            'filebrowserImageUploadUrl' => Url::to(['site/upload-desc-image', '_t' => time()]),
            'toolbar' => [
                [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
                [ 'Scayt' ],
                [ 'Link', 'Unlink', 'Anchor' ],
                [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ],
                [ 'Maximize' ],
                [ 'Source' ],
                '/',
                [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ],
                [ 'Styles', 'Format' ],
                [ 'About' ],
            ],
        ], $this->clientOptions);
        parent::init();
    }
}