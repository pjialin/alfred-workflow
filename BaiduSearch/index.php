<?php
BaiduSearch::Go('林子互联');
class BaiduSearch{
    static protected $searchUrl = 'https://www.baidu.com/s?w=';

	static public function Go($query)
	{
        self::DoSearch($query);

	}

	/**
	 *  进行搜索
	 */
    static protected function DoSearch($query)
    {
        $searchRes = self::getSearchContent($query);  
        $searchRes = self::execContent($searchRes);
        self::showAlfred($searchRes,$query);
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

    }

    /**
     * 处理搜索结果
     */
    static protected function execContent($content)
    {
        $patt = '#class="result.*?class="t.*?<a.*?href.*?=.*?"(.*?)".*?>(.*?)</a>.*?><.*?class="c-showurl.*?>(.*?)<#si';
        preg_match_all($patt,$content,$res);
        $searchResLen = count($res[1]);
        if($searchResLen > 0){
            for ($i=0; $i < $searchResLen; $i++) { 
                $searchRes[$i]['url'] = $res[1][$i];
                $searchRes[$i]['title'] = str_replace(['<em>','</em>'], '', $res[2][$i]);
                preg_match('#^([\w-]+\.)+[\w-]+#',$res[3][$i], $tmp_url);
                $searchRes[$i]['weburl'] = empty($tmp_url[0]) ?  '': $tmp_url[0];
            }
        }
        return $len > 0 ? $searchRes : false; 
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
            self::$curl_error = curl_error($ch);
            //exit("CURL ERROR:$url " . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }


}
