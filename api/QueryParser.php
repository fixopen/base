<?php

class QueryParser
{

    private static $pathPrefix = '/api/';

    private static function parseParams($queryString)
    {
        $result = array('filter' => '', 'offset' => -1, 'count' => -1, 'orderBy' => '');
        $items = explode('&', $queryString);
        foreach ($items as $index => $param) {
            $pair = explode('=', $param);
            if (count($pair) == 2) {
                switch ($pair[0]) {
                    case 'filter':
                        $result['filter'] = urldecode($pair[1]);
                        break;
                    case 'offset':
                        $result['offset'] = (int)$pair[1];
                        break;
                    case 'count':
                        $result['count'] = (int)$pair[1];
                        break;
                    case 'orderBy':
                        $result['orderBy'] = urldecode($pair[1]);
                        break;
                    default:
                        break;
                }
            }
        }
        //print_r($result);
        return $result;
    }

    public static function ParseQuery()
    {
        //print_r($_SERVER);
        $path = substr($_SERVER['REQUEST_URI'], strlen(self::$pathPrefix));
        $paths = explode('/', $path);
        $lastIndex = count($paths) - 1;
        $questionMarkPos = strpos($paths[$lastIndex], '?');
        if ($questionMarkPos) {
            $paths[$lastIndex] = substr($paths[$lastIndex], 0, $questionMarkPos);
        }
        $params = self::parseParams($_SERVER['QUERY_STRING']);
        $body = '';
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'POST':
                $body = file_get_contents('php://input');
                break;
            case 'PUT':
                $body = file_get_contents('php://input');
                break;
            case 'PATCH':
                $body = file_get_contents('php://input');
                break;
        }
        //print_r($_SESSION);
        return array(
            'method' => $method,
            'paths' => $paths,
            'params' => $params,
            'cookies' => $_COOKIE,
            //'sessionInfo' => $_SESSION,
            'headers' => apache_request_headers(),
            //'headers' => http_get_request_headers(),
            'body' => $body,
            'temp' => array(),
            'response' => array('code' => 200, 'headers' => array(), 'cookies' => array(), 'body' => '')
        );
    }

}

