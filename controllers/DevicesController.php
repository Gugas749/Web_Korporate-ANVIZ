<?php

namespace app\controllers;

use app\models\Dept;
use Yii;
use yii\web\Controller;

class DevicesController extends Controller
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
                    return Yii::$app->response->redirect(['site/login']);                },
            ],
        ];
    }

    public function actionIndex()
    {
        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');
        $detailDevice = null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://127.0.0.1:7236/devices/stats");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        curl_close($ch);

        $devices = json_decode($response, true);

        return $this->render('index', [
            'devices' => $devices,
            'search' => $search,
            'detailDevice' => $detailDevice,
        ]);
    }
}