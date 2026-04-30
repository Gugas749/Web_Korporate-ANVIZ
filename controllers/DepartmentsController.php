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

        //$this->teste();

        return $this->render('index', [
            'departments' => $departments,
            'search' => $search,
            'detailDept' => $detailDept,
        ]);
    }

    public function actionGetDeptDetail($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $r = Dept::find()->where(['Deptid' => $id])->one();

        if (!$r) {
            return ['error' => 'Department not found'];
        }

        return [
            'Deptid' => $r->Deptid,
            'DeptName' => $r->DeptName,
            'SupDeptid' => $r->SupDeptid,
        ];
    }

    public function actionGetUsersAffiliated($id){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $items = Userinfo::find()
            ->where(['=', 'Deptid', $id])
            ->orderBy(['Name' => SORT_ASC])->all();

        return array_map(function ($item) {
            return [
                'Userid' => $item->Userid,
                'Username' => $item->Name,
            ];
        }, $items);
    }

    public function teste(){
        $ch = curl_init("http://127.0.0.1:5165/test");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // REMOVE CERTIFICATE
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if ($response === false) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "HTTP CODE: " . $httpCode . "<br>";
            var_dump($response);
        }

        curl_close($ch);
        die();
    }
}