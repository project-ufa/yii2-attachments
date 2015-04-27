<?php

namespace nemmo\attachments\components;

use kartik\file\FileInput;
use nemmo\attachments\models\UploadForm;
use nemmo\attachments\ModuleTrait;
use yii\bootstrap\Widget;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: Алимжан
 * Date: 13.02.2015
 * Time: 21:18
 */
class AttachmentsInput extends Widget
{
    use ModuleTrait;

    public $id = 'file-input';

    public $model;

    public $pluginOptions = [];

    public $options = [];

    public $tag = 'default';

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        FileHelper::removeDirectory($this->getModule()->getUserDirPath()); // Delete all uploaded files in past

        $this->pluginOptions = array_replace($this->pluginOptions, [
            'uploadUrl' => Url::toRoute(['/attachments/file/upload', 'tag' => $this->tag]),
            'deleteUrl' => Url::toRoute(['/attachments/file/delete', 'tag' => $this->tag]),
            'initialPreview' => $this->model->isNewRecord ? [] : $this->model->getInitialPreview(),
            'initialPreviewConfig' => $this->model->isNewRecord ? [] : $this->model->getInitialPreviewConfig(),
            'uploadAsync' => false
        ]);

        $this->options = array_replace($this->options, [
            'id' => $this->id,
            //'multiple' => true
        ]);
    }

    public function run()
    {
        $fileinput = FileInput::widget([
            'model' => new UploadForm(),
            'attribute' => 'file[]',
            'options' => $this->options,
            'pluginOptions' => $this->pluginOptions
        ]);

        return Html::tag('div', $fileinput, ['class' => 'form-group']);
    }
}