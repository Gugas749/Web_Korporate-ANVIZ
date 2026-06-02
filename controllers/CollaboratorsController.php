<?php

namespace app\controllers;

use app\models\Dept;
use app\models\User;
use app\models\Userinfo;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class CollaboratorsController extends Controller
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
        $query = Userinfo::find()
            ->where(['!=', 'Deptid', -1])// para filtrar os user que foram SOFT DELETED
            ->orderBy(['Name' => SORT_ASC]);
        $detailUser = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'Name', $search]);
        }
        $users = $query->all();

        if($detail){
            $detailUser = Userinfo::findOne($detail);
        }

        return $this->render('index', [
            'users' => $users,
            'search' => $search,
            'detailUser' => $detailUser,
        ]);
    }
}