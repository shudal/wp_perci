<?php
namespace app\index\controller;

use QL\QueryList;
use GuzzleHttp\Client;
use Cache\Adapter\Chain\Chain\ChainCachePoo;
use GuzzleHttp\Cookie\CookieJar;

class Img extends Base{
    public function getImg(){
        $url = 'https://wall.alphacoders.com/by_resolution.php';

        $w=1920;$h=1080;
        $headers = [
            'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding'=>'gzip, deflate, br',
            'Accept-Language'=>'zh-CN,zh;q=0.9,en;q=0.8',
            'Host' =>'wall.alphacoders.com',
            'Upgrade-Insecure-Requests'=>'1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11'
        ];

        $client = new Client();

        $res = $client->request('GET',$url,['w'=>$w,'h'=>$h],$headers);

        $wa_session = substr($res->getHeader('Set-Cookie')[0],11);
        $wa_session = explode(';',$wa_session)[0];

        $ca=[
            'Sorting'                   =>  'rating',
            'AlphaCodersView'           =>  'simple',
            '_cmpQcif3pcsupported'      =>  '1',
            'SortingSearch'             =>  'relevance',
            'ResolutionFilter'          =>  $w.'x'.$h,
            'ResolutionEquals'          =>  '%3E%3D',
            '_gat'                      =>  '1'
        ];
        $ca['__cfduid'] = 'd972991cc7b10aabe8bf21bb66c708b9b1548897908';
        $ca['_ga']      = 'GA1.2.252472304.1548897910';
        $ca['_gid']     = 'GA1.2.1336430807.1548897910';
        $ca['wa_session'] = $wa_session;

        $cookieJar = CookieJar::fromArray($ca,$url);

        $html = (string)$res->getBody();

        $ql = QueryList::html($html);
        $data = $ql->find("img")->map(function($item){
            return $item->alt;
        });
        $data = $data->all();
        
        dump($data);
        for ($i=0;  $i<count($data); $i ++){
            if(empty($data)) break;
            if(strpos('ID',$data[$i]) == false)
                unset($data[$i]);
                $i --;
        }
        dump($data);
    }
}
