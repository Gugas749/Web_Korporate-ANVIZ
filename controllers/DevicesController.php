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

        $ch = curl_init();
        $base = Yii::$app->params['anvizApiUrl'] ?? 'https://localhost:7236';
        curl_setopt($ch, CURLOPT_URL, $base . '/devices/stats');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TODO: Remove in production
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // TODO: Remove in production

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // null = API unreachable; empty array = no devices
        $devices = ($response && $httpCode === 200)
            ? (json_decode($response, true) ?? [])
            : null;

        // Client-side search filter
        if ($devices && $search) {
            $q       = strtolower($search);
            $devices = array_filter($devices, function ($d) use ($q) {
                return str_contains(strtolower((string)($d['id'] ?? '')), $q)
                    || str_contains(strtolower($d['ipAddress'] ?? ''), $q);
            });
            $devices = array_values($devices);
        }

        return $this->render('index', [
            'devices'      => $devices,
            'search'       => $search,
            'detailDevice' => null,
        ]);
    }
}