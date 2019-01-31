<?php
namespace app\index\controller;

use QL\QueryList;
use GuzzleHttp\Client;
use Cache\Adapter\Chain\Chain\ChainCachePoo;

class Img extends Base{
    public function getBg(){

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11'
        ];

        $client = new Client();
        $res = $client->request('GET','http://sso.jwc.whut.edu.cn/Certification/toIndex.do',[],$headers);
        $html = (string)$res->getBody();

        $data = QueryList::html($html)->find('h3')->texts();
        dump($data);
    }
}
