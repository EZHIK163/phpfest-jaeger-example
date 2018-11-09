<?php

namespace app\controllers;

use GuzzleHttp\Exception\GuzzleException;
use yii\web\Controller;

class ModuleCController extends Controller
{
        public function actionApiThree() {
        //Начальное конфигурирование
        $config = \Jaeger\Config::getInstance();
        $config->gen128bit();
        $_SERVER['UBER-TRACE-ID'] = $_SERVER['HTTP_UBER_TRACE_ID'];
        try {
            //Установка общего имени для трассировки
            $clientTrace = $config->initTrace('Module C');

            $injectTarget = [];
            //Извлекаем контекст из $_SERVER, для уведомления о создании Span F
            $spanContext = $clientTrace->extract(\OpenTracing\Formats\TEXT_MAP, $_SERVER);
            //Старт Span F
            $clientSpanF = $clientTrace->startSpan('Span F', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $clientTrace->inject($clientSpanF->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'http://phpfest.jaeger.ru/index.php/module-b/api-four';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url,['headers' => $injectTarget]);

            //Установка тега для Span F
            $clientSpanF->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span F
            $clientSpanF->log(['message' => "Span F ". $method .' '. $url .' end !']);
            //Завершение Span F
            $clientSpanF->finish();

            $config->flush();

            } catch (\Exception $e) {

            } catch (GuzzleException $e) {

        }
        }
}
