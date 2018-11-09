<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionApiOne() {
        //Начальное конфигурирование
        $config_tracer = \Jaeger\Config::getInstance();
        $config_tracer->gen128bit();

        //Установка общего имени для трассировки
        $tracer = $config_tracer->initTrace('Name trace in Module A', '0.0.0.0:6831');
        //Извлекаем контекст из $_SERVER, для уведомления о создании Span A
        $spanContext = $tracer->extract(\OpenTracing\Formats\TEXT_MAP, $_SERVER);
        //Старт Span A
        $serverSpan = $tracer->startSpan('Span A', ['child_of' => $spanContext]);
        //Вносим контекст в $_SERvER
        $tracer->inject($serverSpan->getContext(), \OpenTracing\Formats\TEXT_MAP, $_SERVER);
        //Массив, который будет содержать headers для вложенных POST/GET запросов
        $injectTarget = [];
        //Извлекаем контекст из $_SERVER, для уведомления о создании Span B
        $spanContext = $tracer->extract(\OpenTracing\Formats\TEXT_MAP, $_SERVER);
        //Старт Span B
        $clientSapn1 = $tracer->startSpan('Span B', ['child_of' => $spanContext]);
        //Вносим контекст в $injectTarget
        $tracer->inject($clientSapn1->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

        $header = ['UBER-TRACE-ID:'.$injectTarget['UBER-TRACE-ID']];
        $response  = \Flygo\MicroService\WifiAuth::post("wifi_personal_area/testJaeger/v1/", [], $header);

        $clientSapn1->finish();

        $injectTarget = [];
        //Старт Span E
        $clientSpanE = $tracer->startSpan('Span E', ['child_of' => $spanContext]);
        //Вносим контекст в $injectTarget
        $tracer->inject($clientSpanE->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

        $method = 'GET';
        $url = 'https://github.com/';
        $client = new \GuzzleHttp\Client();
        $client->request($method, $url,['headers' => $injectTarget]);

        //Установка тега для Span E
        $clientSpanE->setTags(['http.status_code' => 200
            , 'http.method' => 'GET', 'http.url' => $url]);
        //Запись в лог информации о Span E
        $clientSpanE->log(['message' => "Span C ". $method .' '. $url .' end !']);
        //Завершение Span D
        $clientSpanE->finish();

        $serverSpan->finish();
        $config_tracer->flush();
    }

    public function actionApiTwo() {
        //Начальное конфигурирование
        $config = Config::getInstance();
        $config->gen128bit();

        try {
            //Установка общего имени для трассировки
            $clientTrace = $config->initTrace('Name trace in Module B');

            $injectTarget = [];
            //Извлекаем контекст из $_SERVER, для уведомления о создании Span C
            $spanContext = $clientTrace->extract(\OpenTracing\Formats\TEXT_MAP, $_SERVER);
            //Старт Span C
            $clientSpanC = $clientTrace->startSpan('Span C', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $clientTrace->inject($clientSpanC->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'https://github.com/';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url,['headers' => $injectTarget]);

            //Установка тега для Span C
            $clientSpanC->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span C
            $clientSpanC->log(['message' => "Span C ". $method .' '. $url .' end !']);
            //Завершение Span C
            $clientSpanC->finish();

            $injectTarget = [];
            //Старт Span D
            $clientSpanD = $clientTrace->startSpan('Span D', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $clientTrace->inject($clientSpanD->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'https://github.com/';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url,['headers' => $injectTarget]);

            //Установка тега для Span D
            $clientSpanD->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span D
            $clientSpanD->log(['message' => "Span C ". $method .' '. $url .' end !']);
            //Завершение Span D
            $clientSpanD->finish();

            $config->flush();

        } catch (\Exception $e) {
        }
    }
}
