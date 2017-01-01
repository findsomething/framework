<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/12/8
 * Time: 23:40
 */
namespace FSth\Framework\Controller;

use Phalcon\Http\Response;

class BaseController extends \Phalcon\Mvc\Controller
{
    public function getQuery()
    {
        $get = ($this->request->get()) ? $this->request->get() : array();
        $get += $_GET;
        return $get;
    }

    public function getPost()
    {
        $post = ($this->request->getJsonRawBody(true)) ? $this->request->getJsonRawBody(true) : array();
        $post += $_POST;
        return $post;
    }

    public function toResponse(array $params)
    {
        $response = new Response();
        $response->setStatusCode(200);
        return $response->setJsonContent($params);
    }

    public function toJsonp(array $params)
    {
        $get = $this->getQuery();
        $callback = !empty($get['callback']) ? $get['callback'] : '';
        echo $callback . "(" . json_encode($params) . ")";
    }
}