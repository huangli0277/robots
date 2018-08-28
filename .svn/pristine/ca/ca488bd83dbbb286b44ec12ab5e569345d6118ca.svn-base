<?php
class send
{

	function sendSMS ($mobile, $content)
	{
	$appid = "1400077312";
	$appkey = "0a4056ff5385b4e9b46a027f1aecbdbb";
	$re = $this->qq_send($mobile, $content, $appid , $appkey); // POST方式提交
	$data = json_decode($re);                    
	// change_sms change_start
//	echo $data->errmsg;
	if($data->errmsg == "OK")
	// change_sms change_end
	{
		return true;
	}
	else
	{
		return false;
	}
	}

	function qq_send($phoneNumber, $msg, $appid = "", $appkey = "")
	{
		$nationCode = 86;
		$random = rand(100000, 999999);
		$curTime = time();
	   $wholeUrl = "https://yun.tim.qq.com/v5/tlsvoicesvr/sendvoiceprompt?sdkappid=" . $appid . "&random=" . $random;
		// 按照协议组织 post 包体
		$data = new \stdClass();
		$tel = new \stdClass();
		$tel->nationcode = "".$nationCode;
		$tel->mobile = "".$phoneNumber;
		$data->tel = $tel;
		//$data->promptfile = iconv('GB2312', 'UTF-8', $msg);;
		$data->promptfile = $msg;
		$data->prompttype = 2;
		$data->playtimes = 2;
		$data->sig = hash("sha256",
			"appkey=".$appkey."&random=".$random."&time="
			.$curTime."&mobile=".$phoneNumber, FALSE);
		$data->time = $curTime;
		$data->extend = "";
		$data->ext = "";
		return $this->sendCurlPost1($wholeUrl, $data);
	}
	function sendCurlPost1($url, $dataObj)
	{
//		echo $url."<br>";
//		echo json_encode($dataObj);
//		echo "<br>";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		$ret = curl_exec($curl);
		if (false == $ret) {
			// curl_exec failed
			$result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
		} else {
			$rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if (200 != $rsp) {
				$result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp
						. " " . curl_error($curl) ."\"}";
			} else {
				$result = $ret;
			}
		}
		curl_close($curl);
		return $result;
	}
}