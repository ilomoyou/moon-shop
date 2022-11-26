<?php


namespace App\util;


class Express
{
    private $appId;
    private $appKey;
    private $appUrl;

    public function __construct()
    {
        $this->appId = env('EXPRESS_APP_ID', '');
        $this->appKey = env('EXPRESS_APP_KEY', '');
        $this->appUrl = env('EXPRESS_APP_URL', '');
    }

    /**
     * 根据物流公司编号获取物流公司名称
     * @param $code
     * @return string
     */
    public static function getExpressName($code)
    {
        return [
                "ZTO" => "中通快递",
                "YTO" => "圆通速递",
                "YD" => "韵达速递",
                "YZPY" => "邮政快递包裹",
                "EMS" => "EMS",
                "DBL" => "德邦快递",
                "FAST" => "快捷快递",
                "ZJS" => "宅急送",
                "TNT" => "TNT快递",
                "UPS" => "UPS",
                "DHL" => "DHL",
                "FEDEX" => "FEDEX联邦(国内件)",
                "FEDEX_GJ" => "FEDEX联邦(国际件)"
            ][$code] ?? '';
    }

    /**
     * 快递鸟物流查询接口查询订单物流轨迹
     * YTO请求示例：
     * {
     * "OrderCode": "",
     * "ShipperCode": "YTO",
     * "LogisticCode": "12345678",
     * }
     *
     * @param  string  $com  物流公司编号
     * @param  string  $code  物流订单编号
     * @return mixed
     */
    public function getOrderTraces(string $com, string $code)
    {
        // 组装应用级参数
        $requestData = "{".
            "'CustomerName': '',".
            "'OrderCode': '',".
            "'ShipperCode': '$com',".
            "'LogisticCode': '$code',".
            "}";

        // 组装系统级参数
        $datas = array(
            'EBusinessID' => $this->appId,
            'RequestType' => '1002', //免费即时查询接口指令1002/在途监控即时查询接口指令8001/地图版即时查询接口指令8003
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->appKey);

        //以form表单形式提交post请求，post请求体中包含了应用级参数和系统级参数
        $result = $this->sendPost($this->appUrl, $datas);
        return json_decode($result, true);
    }

    /**
     * post提交数据
     * @param  string  $url  请求Url
     * @param  array  $datas  提交的数据
     * @return string url响应返回的html
     */
    private function sendPost(string $url, array $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST ".$url_info['path']." HTTP/1.0\r\n";
        $httpheader .= "Host:".$url_info['host']."\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:".strlen($post_data)."\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param  string  $data  内容
     * @param  string  $ApiKey  ApiKey
     * @return string 签名
     */
    private function encrypt(string $data, string $ApiKey)
    {
        return urlencode(base64_encode(md5($data.$ApiKey)));
    }
}
