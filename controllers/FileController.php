<?php

namespace nemmo\attachments\controllers;

use nemmo\attachments\models\File;
use nemmo\attachments\models\UploadForm;
use nemmo\attachments\ModuleTrait;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;

class FileController extends Controller
{
    use ModuleTrait;

    public function actionUpload($tag='default')
    {
        \Yii::trace('actionUpload: request: '. print_r(\Yii::$app->request->bodyParams, true), 'debug');
        $model = new UploadForm();

        $model->file = UploadedFile::getInstances($model, 'file');

        if ($model->rules()[0]['maxFiles'] == 1) {
            $model->file = UploadedFile::getInstances($model, 'file')[0];
        }

        if ($model->file && $model->validate()) {
            $result['uploadedFiles'] = [];
            if($tag!='default') {
                $tagPath = $this->getModule()->getUserDirPath() . DIRECTORY_SEPARATOR . $tag;
                FileHelper::createDirectory($tagPath);
            } else {
                $tagPath = $this->getModule()->getUserDirPath();
            }
            if (is_array($model->file)) {
                foreach ($model->file as $file) {
                    $path = $tagPath . DIRECTORY_SEPARATOR . $file->name;
                    $file->saveAs($path);
                    $result['uploadedFiles'][] = $file->name;
                }
            } else {
                $path = $tagPath . DIRECTORY_SEPARATOR . $model->file->name;
                $model->file->saveAs($path);
            }
            return json_encode($result);
        } else {
            if(empty($model->file))
                $errors = [\Yii::t('app', 'Error: no files uploaded')];
            else
                $errors = $model->getErrors();

            \Yii::trace('actionUpload: error validate: '. print_r($errors, true), 'debug');
            return json_encode([
                'error' => $errors //$model->errors['file']
            ]);
        }
    }

    public function actionDownload($id)
    {
        \Yii::trace('actionDownload: request: '. print_r(\yii::$app->getRequest(), true), 'debug');
        $file = File::findOne(['id' => $id]);
        $filePath = $this->getModule()->getFilesDirPath($file->hash) . DIRECTORY_SEPARATOR . $file->hash . '.' . $file->type;

        return \Yii::$app->response->sendFile($filePath, "$file->name.$file->type");
    }

    public function actionDelete($id)
    {
        \Yii::trace('actionDelete: request: '. print_r(\yii::$app->getRequest(), true), 'debug');
        $this->getModule()->detachFile($id);

        if (\Yii::$app->request->isAjax) {
            return json_encode([]);
        } else {
            return $this->redirect(Url::previous());
        }
    }

    public function actionDownloadTemp($filename)
    {
        \Yii::trace('actionDownloadTemp: request: '. print_r(\yii::$app->getRequest(), true), 'debug');
        $filePath = $this->getModule()->getUserDirPath() . DIRECTORY_SEPARATOR . $filename;

        return \Yii::$app->response->sendFile($filePath, $filename);
    }

    public function actionDeleteTemp($filename)
    {
        \Yii::trace('actionDeleteTemp: request: '. print_r(\yii::$app->getRequest(), true), 'debug');
        $userTempDir = $this->getModule()->getUserDirPath();
        $filePath = $userTempDir . DIRECTORY_SEPARATOR . $filename;
        unlink($filePath);
        if (!sizeof(FileHelper::findFiles($userTempDir))) {
            rmdir($userTempDir);
        }

        if (\Yii::$app->request->isAjax) {
            return json_encode([]);
        } else {
            return $this->redirect(Url::previous());
        }
    }
}
