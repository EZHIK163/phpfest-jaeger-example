<?php

namespace app\controllers;

use GuzzleHttp\Exception\GuzzleException;
use yii\web\Controller;

class ModuleAController extends Controller
{
    public function actionApiOne() {

        try {
            //Начальное конфигурирование
            $config_tracer = \Jaeger\Config::getInstance();
            $config_tracer->gen128bit();

            //Установка общего имени для трассировки
            $tracer = $config_tracer->initTrace('Module A', '0.0.0.0:6831');
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
            $clientSpanB = $tracer->startSpan('Span B', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $tracer->inject($clientSpanB->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'http://phpfest.jaeger.ru/index.php/module-b/api-two';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url, ['headers' => $injectTarget]);

            //Установка тега для Span B
            $clientSpanB->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span B
            $clientSpanB->log(['message' => "Span B ". $method .' '. $url .' end !']);
            $clientSpanB->finish();

            $injectTarget = [];
            //Старт Span E
            $clientSpanE = $tracer->startSpan('Span E', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $tracer->inject($clientSpanE->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'http://phpfest.jaeger.ru/index.php/module-c/api-three';
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

            echo 'Success';
            exit;

        } catch (GuzzleException $e) {

        } catch (\Exception $e) {

        }
    }
}
