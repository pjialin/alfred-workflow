<?php

require ('workflows.php');

#$query = $_GET['q'];
#$Fanyi = new Fanyi();
#$Fanyi->Go($query);

class Fanyi
{
    public $api_info = [
        'keyfrom' => 'YouDaoWeb',
        'key' => '1027234625'
    ];
	protected $api_url = 'http://fanyi.youdao.com/openapi.do?';
    /** WorkFlow Ins */
    protected $w;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->w = new Workflows();   
    }
    
    /**
     * 处理操作
     */
    public function exec($info,$type = 'copy')
    {
        $info = json_decode($info);
        if(isset($info->type)){
            $this->w->result($this->getUid(),'http://baidu.com','exec','',$this->getIcon());
            echo $this->w->toxml();
        }
    }

	/**
	 *  开始翻译
	 */
	public function Go($value='')
	{
	    $transRes = $this->getTransRes($value);	
        $this->showAlfred($transRes,$value);
        echo $this->w->toxml();
	}

	/**
	 * 处理翻译结果
	 */
	protected function getTransRes($query)
	{
        $sendData = [
            'type' => 'data',
            'doctype' => 'json',
            'version' => '1.2',
            'q' => $query,
        ];
        $apiUrl = $this->api_url . http_build_query(array_merge($this->api_info,$sendData));
        $apiRes = $this->curl($apiUrl);      
        $apiRes = json_decode(urldecode($apiRes));
        return $apiRes;
	}

    /**
     * 显示到Alfred
     */ 
    protected function showAlfred($info,$query)
    {
        $saveData = [
            'url' => 'http://dict.youdao.com/search?q=' . $query,
        ];
        if(isset($info->errorCode) && $info->errorCode != 0){
            $this->w->result($this->getUid(),'Error',$this->getErrorInfo($info->errorCode),'翻译出错',$this->getIcon());
        }else if(!empty($info->translation) && $info->translation[0]){
            $str = $info->translation[0];
            if($str != $query)
            {
                if(!empty($info->basic->phonetic))
                    $str .=  ' [' . $info->basic->phonetic . ']';
                $this->w->result($this->getUid(),$this->saveData(array_merge($saveData,['copy'=> $str])),$str,'翻译结果',$this->getIcon());
                
                if(!empty($info->basic->explains))
                {
                    foreach($info->basic->explains as $v){
                        $this->w->result($this->getUid(),$this->saveData(array_merge($saveData,['copy'=> $str])),$v,'简明释义',$this->getIcon());
                    }
                }

                if(!empty($info->web)){
                    foreach($info->web as $v){
                        $tmp_v = implode(',',$v->value);
                        $this->w->result($this->getUid(),$this->saveData(array_merge($saveData,['copy'=> $str])),$tmp_v,'网络释义：'. $v->key,$this->getIcon());
                    }
                }

            }else{
                $this->w->result($this->getUid(),$this->saveData($saveData),'有道翻译也爱莫能助了，按Enter键进行网上搜索','会不会是你拼错了呢？'. $query,$this->getIcon());
            }
        }
        else 
        {
            $error = $this->curl_error ? : '';
            $this->w->result($this->getUid(),'','什么也没找到呀，检查下网络试试',$error,$this->getIcon());
        }
    }

    /**
	 * @param string $url 地址
	 * @param bool|false $data post数据
	 * @param array $s_option curl参数
	 * @return mixed
	 */
	protected function curl($url, $data = false,$s_option = []){
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
            $this->curl_error = curl_error($ch);
			//exit("CURL ERROR:$url " . curl_error($ch));
		}
		curl_close($ch);
		return $response;
	}

    /**
     * 获取错误信息
     */
    protected function getErrorInfo($code)
    {
        $errorInfo = [
            20 => '要翻译的文本过长',
            30 => '无法进行有效的翻译',
            40 => '不支持的语言类型',
            50 => '无效的key',
            60 => '无词典结果',
        ];
        return $errorInfo[$code];
    }

    /**
     * 获取节点uid
     */
    protected function getUid()
    {
        return time() . mt_rand(1000,9999);
    }

    /**
     * 获取图标
     */
    protected function getIcon($name = ''){
        return 'youdao.ico';
    }

    /**
     * 存储返回数据
     */
    protected function saveData($name,$value = ''){
        $data = [];
        if(is_array($name) && !empty($name)){
            foreach($name as $k => $v){
                $data[$k] = $v;  
            }
        }else
            $data[$name] = $value;
        return json_encode($data);
    }
}	
