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

    // ── AJAX: update department name ──────────────────────────
    public function actionUpdate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id   = Yii::$app->request->get('id');
        $name = trim(Yii::$app->request->get('name', ''));

        if (!$id || $name === '') {
            return ['success' => false, 'error' => 'Dados inválidos.'];
        }

        $dept = Dept::findOne($id);
        if (!$dept) {
            return ['success' => false, 'error' => 'Departamento não encontrado.'];
        }

        $dept->DeptName = $name;

        if ($dept->save()) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => implode(', ', $dept->getFirstErrors())];
    }

    // ── AJAX: create new department ───────────────────────────
    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->get('name', ''));

        if ($name === '') {
            return ['success' => false, 'error' => 'Nome inválido.'];
        }

        $dept           = new Dept();
        $dept->DeptName = $name;
        $dept->SupDeptid = 0; // adjust default if your schema requires it

        if ($dept->save()) {
            return ['success' => true, 'id' => $dept->Deptid];
        }

        return ['success' => false, 'error' => implode(', ', $dept->getFirstErrors())];
    }
}