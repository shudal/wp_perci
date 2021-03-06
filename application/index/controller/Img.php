<?php
namespace app\index\controller;

use QL\QueryList;
use GuzzleHttp\Client;
use Cache\Adapter\Chain\Chain\ChainCachePoo;
use GuzzleHttp\Cookie\CookieJar;

class Img extends Base{
    public $wa_session;
    public $headers = [
        'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding'=>'gzip, deflate, br',
        'Accept-Language'=>'zh-CN,zh;q=0.9,en;q=0.8',
        'Host' =>'wall.alphacoders.com',
        'Upgrade-Insecure-Requests'=>'1',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11'

    ];
    public function getWa(){
        $url = 'https://wall.alphacoders.com/by_resolution.php';

        $client = new Client();

        $res = $client->request('GET',$url,['w'=>'1920','h'=>'1080'],$this->headers);

        $wa_session = substr($res->getHeader('Set-Cookie')[0],11);
        $wa_session = explode(';',$wa_session)[0];
    }
    public function getImgBySize($w=1920,$h=1080,$page=2){
        $this->getWa();

        $url = 'https://wall.alphacoders.com/by_resolution.php';

        $ca=[
            'Sorting'                   =>  'rating',
            'AlphaCodersView'           =>  'simple',
            '_cmpQcif3pcsupported'      =>  '1',
            'SortingSearch'             =>  'relevance',
            'ResolutionFilter'          =>  $w.'x'.$h,
            'ResolutionEquals'          =>  '%3E%3D',
            '_gat'                      =>  '1'
        ];
        //$ca['__cfduid'] = 'd972991cc7b10aabe8bf21bb66c708b9b'.time();
        //$ca['_ga']      = 'GA1.2.252472304.'.time();
        //$ca['_gid']     = 'GA1.2.1336430807.'.time();
        $ca['wa_session'] = $this->wa_session;

        $cookieJar = CookieJar::fromArray($ca,$url);

        $client = new Client(['cookies' => $cookieJar ]);

        $res = $client->request('GET',$url,['w'=>$w,'h'=>$h,'page'=>$page],$this->headers);

        $html = (string)$res->getBody();

        //得到所有img标签的alt属性
        $ql = QueryList::html($html);
        $data = $ql->find("img")->map(function($item){
            return $item->alt;
        });

        //过滤不要的img标签
        $data =  $data->filter(function($item){
            if($item == '') return false;
            return strpos($item,'HD Wallpaper | Background Image ID') !==false;
        });

        //得到所有图像的id
        $data = $data->map(function($item){
            return explode(':',$item)[1];
        });
        $data = $data->all();

        $imgs = [];
        $url = 'https://wall.alphacoders.com/big.php';
        foreach ($data as $d){
            $res = $client->request('GET',$url,['i'=>$d],$this->headers);
            dump($res);
            $html = (string)$res->getBody();

            $ql = QueryList::html($html);
            $the_data = $ql->find('img')->attrs('*');
            dump($the_data);
        }
    }
}
