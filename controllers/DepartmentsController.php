<?php

namespace app\controllers;

use app\models\Dept;
use app\models\User;
use app\models\Userinfo;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class DepartmentsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'except' => ['error'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');

        // o where server para filtar os user que foram SOFT DELETED
        $query = Dept::find()
            ->orderBy(['DeptName' => SORT_ASC]);
        $detailDept = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'DeptName', $search]);
        }
        $departments = $query->all();

        if($detail){
            $detailDept = Dept::findOne($detail);
        }

        return $this->render('index', [
            'departments' => $departments,
            'search' => $search,
            'detailDept' => $detailDept,
        ]);
    }
}