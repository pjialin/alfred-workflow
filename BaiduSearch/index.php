<?php

require ('workflows.php');

# BaiduSearch::Go('content');

class BaiduSearch{
    static protected $searchUrl = 'https://www.baidu.com/s?w=';
    /** workflow ins */
    static protected $w;
    /** save curl error */
    static protected $curlError;

	static public function Go($query)
	{
        self::DoSearch(urlencode($query));
	}

	/**
	 *  进行搜索
	 */
    static protected function DoSearch($query)
    {
        self::$w = new Workflows();
        $searchRes = self::getSearchContent($query);  
        $searchRes = self::execContent($searchRes);
        self::showAlfred($searchRes,$query);
        //print_r(self::$w->results());
        echo self::$w->toxml();
    }

    /**
     * 获取搜索内容
     */
    static protected function getSearchContent($query)
    {
        return self::curl(self::$searchUrl . $query,false,[
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36 SE 2.X MetaSr 1.0'
        ]);
    }
    /**
     * 显示到Alfred
     */ 
    static protected function showAlfred($info,$query){
        $argu = [
                'query' => $query,
                'website' => 'https://www.baidu.com/s?wd='. $query . '#'
            ];
        self::getargu($argu);
        if(!self::$curlError){
            if(!empty($info))
            {
                foreach($info as $v)
                    self::$w->result(self::getuid(),self::getargu(['copy'=>$v['title'],'url'=>$v['url'] . '#']),$v['title'],$v['weburl'],self::geticon());
            }else{
                self::$w->result(self::getuid(),self::getargu(['copy'=>$query]),'好像什么也没搜到，按enter键去百度官网搜索试试',''. $query,self::geticon());
            }
        }
        else 
        {
            $error = self::$curlError;
            self::$w->result(self::getuid(),self::getargu(['copy'=>$query]),'什么也没找到呀，检查下网络试试',$error,self::geticon());
        }

    }

    /**
     * 处理搜索结果
     */
    static protected function execContent($content)
    {
        $patt = '#class="result.*?class="t.*?<a.*?href.*?=.*?"(.*?)".*?>(.*?)</a>.*?class="c-showurl.*?>([^<?].*?)</[^b]#si';
        preg_match_all($patt,$content,$res);
    #print_r($res[1]);
    #print_r($res[2]);
    #print_r($res[3]);
        $searchResLen = count($res[1]);
        if($searchResLen > 0){
            for ($i=0; $i < $searchResLen; $i++) { 
                $searchRes[$i]['url'] = $res[1][$i];
                $searchRes[$i]['title'] = str_replace(['<em>','</em>','<font color=#CC0000>','</font>'], '', $res[2][$i]);
                preg_match('#^([\w-]+\.)+[\w-]+#',str_replace(['<b>','</b>'], '', $res[3][$i]), $tmp_url);
                $searchRes[$i]['weburl'] = empty($tmp_url[0]) ?  '': $tmp_url[0];
            }
        }
        return $searchResLen > 0 ? $searchRes : false; 
    }

    /**
     * @param string $url 地址
     * @param bool|false $data post数据
     * @param array $s_option curl参数
     * @return mixed
     */
    static protected function curl($url, $data = false,$s_option = []){
        $ch = curl_init();
        $option = [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];
        if ( $data ) {
            $option[CURLOPT_POST] = 1;
            $option[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        foreach($s_option as $k => $v){
            $option[$k] = $v;
        };
        curl_setopt_array($ch, $option);
        $response = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            self::$curlError = curl_error($ch);
            //exit("CURL ERROR:$url " . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 获取节点uid
     */
    static protected function getUid()
    {
        return time() . mt_rand(1000,9999);
    }
    /**
     * 获取图标
     */
    static protected function getIcon($name = ''){
        return 'baidu.ico';
    }

    /**
     * 返回值Argu
     */
    static protected function getArgu($info = [],$clean = false)
    {
        static $data = [];
        if($clean === true) 
            $data = [];
        $data = array_merge($data,$info);
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    } 

}
