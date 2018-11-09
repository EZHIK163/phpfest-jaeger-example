<?php

namespace app\controllers;

use GuzzleHttp\Exception\GuzzleException;
use yii\web\Controller;

class ModuleBController extends Controller
{
        public function actionApiTwo() {
        //Начальное конфигурирование
        $config = \Jaeger\Config::getInstance();
        $config->gen128bit();
        //Финт ушами, который позволяет связывать спаны
        $_SERVER['UBER-TRACE-ID'] = $_SERVER['HTTP_UBER_TRACE_ID'];
        try {
            //Установка общего имени для трассировки
            $clientTrace = $config->initTrace('Module B');

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

            } catch (GuzzleException $e) {

        }
        }

    public function actionApiFour() {
        //Начальное конфигурирование
        $config = \Jaeger\Config::getInstance();
        $config->gen128bit();

        $_SERVER['UBER-TRACE-ID'] = $_SERVER['HTTP_UBER_TRACE_ID'];
        try {
            //Установка общего имени для трассировки
            $clientTrace = $config->initTrace('Module B');

            $injectTarget = [];
            //Извлекаем контекст из $_SERVER, для уведомления о создании Span G
            $spanContext = $clientTrace->extract(\OpenTracing\Formats\TEXT_MAP, $_SERVER);
            //Старт Span G
            $clientSpanG = $clientTrace->startSpan('Span G', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $clientTrace->inject($clientSpanG->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'https://github.com/';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url,['headers' => $injectTarget]);

            //Установка тега для Span G
            $clientSpanG->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span G
            $clientSpanG->log(['message' => "Span G ". $method .' '. $url .' end !']);
            //Завершение Span G
            $clientSpanG->finish();

            $injectTarget = [];
            //Старт Span H
            $clientSpanH = $clientTrace->startSpan('Span H', ['child_of' => $spanContext]);
            //Вносим контекст в $injectTarget
            $clientTrace->inject($clientSpanH->spanContext, \OpenTracing\Formats\TEXT_MAP, $injectTarget);

            $method = 'GET';
            $url = 'https://github.com/';
            $client = new \GuzzleHttp\Client();
            $client->request($method, $url,['headers' => $injectTarget]);

            //Установка тега для Span H
            $clientSpanH->setTags(['http.status_code' => 200
                , 'http.method' => 'GET', 'http.url' => $url]);
            //Запись в лог информации о Span H
            $clientSpanH->log(['message' => "Span H ". $method .' '. $url .' end !']);
            //Завершение Span H
            $clientSpanH->finish();

            $config->flush();

        } catch (\Exception $e) {

        } catch (GuzzleException $e) {

        }
    }
}
