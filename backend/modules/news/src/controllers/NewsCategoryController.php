<?php

namespace milkyway\news\controllers;

use milkyway\news\models\NewsCategoryLanguage;
use milkyway\news\models\table\NewsCategoryTable;
use yii\db\Exception;
use Yii;
use yii\db\Transaction;
use yii\helpers\Html;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use milkyway\news\NewsModule;
use backend\components\MyController;
use milkyway\news\models\NewsCategory;
use milkyway\news\models\search\NewsCategorySearch;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * NewsCategoryController implements the CRUD actions for NewsCategory model.
 */
class NewsCategoryController extends MyController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all NewsCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NewsCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single NewsCategory model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new NewsCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new NewsCategory();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
            if ($model->validate() && $model->save() && $model->saveNewsCategoryLanguage()) {
                $transaction->commit();
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-view', [
                    'title' => 'Thông báo',
                    'text' => 'Tạo mới thành công',
                    'type' => 'success'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $transaction->rollBack();
                $errors = Html::tag('p', 'Tạo mới thất bại');
                foreach ($model->getErrors() as $error) {
                    $errors .= Html::tag('p', $error[0]);
                }
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-form', [
                    'title' => 'Thông báo',
                    'text' => $errors,
                    'type' => 'warning'
                ]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing NewsCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setNewsCategoryLanguage();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->save() && $model->saveNewsCategoryLanguage()) {
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-view', [
                    'title' => 'Thông báo',
                    'text' => 'Cập nhật thành công',
                    'type' => 'success'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $errors = Html::tag('p', 'Cập nhật thất bại');
                foreach ($model->getErrors() as $error) {
                    $errors .= Html::tag('p', $error[0]);
                }
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-form', [
                    'title' => 'Thông báo',
                    'text' => $errors,
                    'type' => 'warning'
                ]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionValidate($id = null)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = new NewsCategory();
            if ($id !== null) $model = $this->findModel($id);
            $model->setNewsCategoryLanguage();
            if ($model->load(Yii::$app->request->post())) {
                return ActiveForm::validate($model);
            }
        }
    }

    public function actionGetSlug($id = null)
    {
        if (Yii::$app->request->isAjax) {
            $name = Yii::$app->request->post('name');
            $language = Yii::$app->request->post('language', false);
            if ($language === true) $model = new NewsCategoryLanguage();
            else $model = new NewsCategory();
            if ($id !== null) {
                if ($language === true) $model = NewsCategoryLanguage::find()->where(['news_category_id' => $id, 'language' => $language])->one();
                else $model = $this->findModel($id);
            }
            $model->name = $name;
            $model->validate();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'code' => 200,
                'data' => $model->slug
            ];
        }
    }

    /**
     * Deletes an existing NewsCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-index', [
                    'title' => 'Thông báo',
                    'text' => 'Xoá thành công',
                    'type' => 'success'
                ]);
            } else {
                $errors = Html::tag('p', 'Xoá thất bại');
                foreach ($model->getErrors() as $error) {
                    $errors .= Html::tag('p', $error[0]);
                }
                Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-index', [
                    'title' => 'Thông báo',
                    'text' => $errors,
                    'type' => 'warning'
                ]);
            }
        } catch (Exception $ex) {
            Yii::$app->session->setFlash('toastr-' . $model->toastr_key . '-index', [
                'title' => 'Thông báo',
                'text' => Html::tag('p', 'Xoá thất bại: ' . $ex->getMessage()),
                'type' => 'warning'
            ]);
        }
        return $this->redirect(['index']);
    }

    public function actionChangeValue()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->post('id');
            $val = Yii::$app->request->post('val');
            $field = Yii::$app->request->post('field');
            $model = NewsCategory::getById($id);
            if ($model === null || !$model->canGetProperty($field)) return [
                'code' => 404,
                'msg' => NewsModule::t('news', 'Không tìm thấy dữ liệu')
            ];
            try {
                $model->setAttribute($field, $val);
                if ($model->validate() && $model->save()) return [
                    'code' => 200,
                    'msg' => NewsModule::t('news', 'Cập nhật thành công')
                ];
                else {
                    $error = '';
                    foreach ($model->getErrors() as $err) {
                        $error .= $err[0] . '<br/>';
                    }
                    return [
                        'code' => 400,
                        'msg' => NewsModule::t('news', 'Cập nhật thất bại') . ': <br/>' . $error
                    ];
                }
            } catch (Exception $ex) {
                return [
                    'code' => 400,
                    'msg' => NewsModule::t('news', 'Có lỗi xảy ra')
                ];
            }
        }
        return [
            'code' => 403,
            'msg' => NewsModule::t('news', 'Không có quyền truy cập')
        ];
    }

    /**
     * Finds the NewsCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NewsCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */


    protected function findModel($id)
    {
        if (($model = NewsCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('news', 'The requested page does not exist.'));
    }
}
