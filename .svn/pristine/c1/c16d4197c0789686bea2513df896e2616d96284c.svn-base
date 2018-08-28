<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
error_reporting(0);
use \GatewayWorker\Lib\Gateway;
require_once __DIR__ .'/../mysql/src/Connection.php';
global $db;
$db = new Workerman\MySQL\Connection('rm-j6cx30n38n7r941l4.mysql.rds.aliyuncs.com', '3306', 'rdsadmin', 'V4m8D6b4', 'wanghong'); 

class Events
{
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
		global $db;
	   
 // debug
//        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
//        echo date("Y-m-d H:i:s")." IP:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}  session:".json_encode($_SESSION)."\n";
       // echo "Online:".$count." ".date("H:i:s")." IP:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}  UID:".$_SESSION['client_name']." BI:".$_SESSION['room_id']."\n";
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                $room_id = $market = $_SESSION['room_id'];
                $client_name = $user_id = $_SESSION['client_name'];
				if($room_id=="other"){
					return;
				}
				else
				{
					if(strstr($room_id,"__m")){
						$room_id = $market = str_replace("__m","",$_SESSION["room_id"]);
						//手机
					}
					else
					{
						// 电脑
						$room_id = $market = str_replace("__m","",$_SESSION["room_id"]);
						if(!strstr($client_name,"ke")){
							//getEntrustAndUsercoin
							$data = [];
							$result = $db->query('select * from qq3479015851_trade where status=0 and market=\'' . $market . '\' and userid="' . $user_id . '" order by id desc limit 10;');
							if ($result) {
								foreach ($result as $k => $v) {
									$data['entrust'][$k]['addtime'] = date('m-d H:i:s', $v['addtime']);
									$data['entrust'][$k]['type'] = $v['type'];
									$data['entrust'][$k]['price'] = $v['price'] * 1;
									$data['entrust'][$k]['num'] = round($v['num'], 6);
									$data['entrust'][$k]['deal'] = round($v['deal'], 6);
									$data['entrust'][$k]['id'] = round($v['id']);
								}
							}else {
								$data['entrust'] = null;
							}
							$userCoin = $db->row('select * from qq3479015851_user_coin where userid="' . $user_id . '";');
							if ($userCoin) {
								$xnb = explode('_', $market)[0];
								$rmb = explode('_', $market)[1];
								$data['usercoin']['xnb'] = floatval($userCoin[$xnb]);
								$data['usercoin']['xnbd'] = floatval($userCoin[$xnb . 'd']);
								$data['usercoin']['cny'] = floatval($userCoin[$rmb]);
								$data['usercoin']['cnyd'] = floatval($userCoin[$rmb . 'd']);
							}else {
								$data['usercoin'] = null;
							}
							$user = $db->row('select username from qq3479015851_user where id="' . $user_id . '";');
							$data['usercoin']['userid'] = $user_id;
							$data['usercoin']['username'] = $user['username'];
							$datas["getEntrustAndUsercoin"] = $data;
							$message_data['type'] = 'data';
							$message_data['time'] = date('Y-m-d H:i:s');
							$message_data['content'] = $datas;
	//						Gateway::sendToGroup($room_id, json_encode($message_data));
						   echo "--data_pc--".$room_id."--send--".$market.'--\n';				   
							return Gateway::sendToCurrentClient(json_encode($message_data));
							
							
						}
						
						
						
						
					}
					
					
					
				}
				
				
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;
              
//                // 获取房间内所有用户列表 
//                $clients_list = Gateway::getClientSessionsByGroup($room_id);
//                foreach($clients_list as $tmp_client_id=>$item)
//                {
//                    $clients_list[$tmp_client_id] = $item['client_name'];
//                }
//                $clients_list[$client_id] = $client_name;
//                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx} 
//                $new_message = array('type'=>$message_data['type'], 'client_id'=>$client_id, 'client_name'=>htmlspecialchars($client_name), 'time'=>date('Y-m-d H:i:s'));
//                Gateway::sendToGroup($room_id, json_encode($new_message));
                Gateway::joinGroup($client_id, $room_id);
               
//                // 给当前用户发送用户列表 
//                $new_message['client_list'] = $clients_list;
//                Gateway::sendToCurrentClient(json_encode($new_message));
				
                $room_id = $market = $_SESSION['room_id'];
                $client_name = $user_id = $_SESSION['client_name'];
                return;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                
                // 私聊
                if($message_data['to_client_id'] != 'all')
                {
                    $new_message = array(
                        'type'=>'say',
                        'from_client_id'=>$client_id, 
                        'from_client_name' =>$client_name,
                        'to_client_id'=>$message_data['to_client_id'],
                        'content'=>"<b>对你说: </b>".nl2br(htmlspecialchars($message_data['content'])),
                        'time'=>date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    $new_message['content'] = "<b>你对".htmlspecialchars($message_data['to_client_name'])."说: </b>".nl2br(htmlspecialchars($message_data['content']));
                    return Gateway::sendToCurrentClient(json_encode($new_message));
                }
                
                $new_message = array(
                    'type'=>'say', 
                    'from_client_id'=>$client_id,
                    'from_client_name' =>$client_name,
                    'to_client_id'=>'all',
                    'content'=>nl2br(htmlspecialchars($message_data['content'])),
                    'time'=>date('Y-m-d H:i:s'),
                );
               // return Gateway::sendToGroup($room_id ,json_encode($new_message));
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
//       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
      // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
    
       // 从房间的客户端列表中删除
//       if(isset($_SESSION['room_id']))
//       {
//           $room_id = $_SESSION['room_id'];
//           $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('Y-m-d H:i:s'));
//           Gateway::sendToGroup($room_id, json_encode($new_message));
//       }
	   
	   if($_SERVER['GATEWAY_ADDR'] == $_SERVER['REMOTE_ADDR'])
	   {
          // Gateway::sendToGroup($room_id, json_encode($new_message));
//		$new_message['aaa'] = "aaaasssssssssssss";
//		Gateway::sendToGroup('doge_cny', json_encode($new_message));
		$room = array("btd_cny","tix_cny","esm_cny","ifc_cny","cnut_cny","btc_cny","ltc_cny","eth_cny","xrp_cny","doge_cny","qtum_cny","bch_cny","dash_cny","eos_cny","etc_cny","neo_cny","ada_cny","ae_cny","omg_cny","bat_cny","bts_cny","btm_cny");
		// $room = array("doge_cny","dash_cny");
	   $count = Gateway::getALLClientInfo();
	   $count = count($count);
        echo date("H:i:s")." Online:".$count."  \n";
		   echo "--".date("H:i:s")."--data-send--";	
		   foreach($room as $ks=>$room_id)
		   {
				global $db;
			   echo $room_id."--";	
			   	//推送电脑端
			   if($room_id)
			   {
					//手机
					if(1)
					{
						$market = $room_id;
					   echo "m--";	
						//getJsonData
							//$getTradeBuy
							$getTradeBuy =[];
							$buy = $db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade  where status=0 and type=1 and num !=deal  and market =\'' . $market . '\' group by price order by price desc limit 7;');
							if ($buy) {
								$a =0;
								foreach ($buy as $k => $v) {
									$a = max($v["nums"], $a);
								}
								$maxNums = $a/2;

								foreach ($buy as $k => $v) {
									//if($v['nums'])  $getTradeBuy .= '<dd class="clear"><span class="wb_50">' . floatval($v['price']) . '</span><span class="wb_50">' . floatval($v['nums']) . '</span><i class="turntable_bg_red" style="width: ' . ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100) . 'px"></i></dd>';
									if($v['nums']>0)  $getTradeBuy[$k][0] =  floatval($v['price']);
									if($v['nums']>0)  $getTradeBuy[$k][1] = floatval($v['nums']) ;
								}
							}
							//getTradeSell
							$getTradeSell =[];
							$sell = array_reverse($db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade where status=0 and type=2 and num !=deal  and market =\'' . $market . '\' group by price order by price asc limit 7;'));
							if ($sell) {
								$a =0;
								foreach ($sell as $k => $v) {
									$a = max($v["nums"], $a);
								}
								$maxNums = $a/2;
								foreach ($sell as $k => $v) {
									//if($v['nums']) $getTradeSell .= '<dd class="clear"><span class="wb_50 pl_20">' . floatval($v['price']) . '</span><span class="wb_50">' . floatval($v['nums']) . '</span><i class="turntable_bg_red turntable_bg_green" style="width: ' . ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100) . 'px;" ></i></dd>';
									if($v['nums']>0){
										$getTradeSell[$k][0] =  floatval($v['price']);
										$getTradeSell[$k][1] = floatval($v['nums']) ;
									}
									
								}
							}
						$getTradeSell = $getTradeSell;
							//getTradeLog
							$getTradeLog =[];
							$log = $db->query('select * from qq3479015851_trade_log where status=1 and market =\'' . $market . '\'  order by id desc limit 20;');
							if ($log) {
								foreach ($log as $k => $v) {
									//$getTradeLog .= '<tr><td class="' . $type . '"  width="70">' . date('H:i:s', $v['addtime']) . '</td><td class="' . $type . '"  width="70">' . floatval($v['price']) . '</td><td class="' . $type . '"  width="100">' . floatval($v['num']) . '</td><td class="' . $type . '">' . floatval($v['mum']) . '</td></tr>';
									$getTradeLog[$k]['type']=$v['type'];
									$getTradeLog[$k]['price']=floatval($v['price']);
									$getTradeLog[$k]['num']=floatval($v['num']);
									$getTradeLog[$k]['oktime']=date('H:i:s', $v['addtime']);
								}
							}
							$data = [];
							//getJsonData
						//getJsonTop
						$v = $db->row("SELECT * FROM `qq3479015851_market` WHERE name='".$room_id."'");
						if ($v) {
							if ($market) {
								$xnb = explode('_', $market)[0];
								$rmb = explode('_', $market)[1];
								$v['xnb'] = explode('_', $v['name'])[0];
								$v['rmb'] = explode('_', $v['name'])[1];
								$s = $db->row("SELECT * FROM `qq3479015851_coin` WHERE name='".$v['xnb']."'");
								$getJsonTop['new_price'] = round($v['new_price'],8);
								$getJsonTop['change'] = round($v['change'],8);
							}
						}
						//getJsonTop
						
							$data['depth']['buy'] = $getTradeBuy;
							$data['depth']['sell'] = $getTradeSell;
							$data['tradelog'] = $getTradeLog;
							$data['getJsonTop'] = $getJsonTop;
							$datas["getJsonData"] = $data;
							$message_data['type'] = 'data';
							$message_data['time'] = date('Y-m-d H:i:s');
							$message_data['content'] = $datas;
							Gateway::sendToGroup($room_id."__m", json_encode($message_data));
						   echo "--";	

					}
					//手机
					//电脑
				   if(1)
				   {
					   echo "pc--";	
						//getJsonTop
						$v = $db->row("SELECT * FROM `qq3479015851_market` WHERE name='".$room_id."'");
						if ($v) {
							if ($market) {
								$xnb = explode('_', $market)[0];
								$rmb = explode('_', $market)[1];
								$v['xnb'] = explode('_', $v['name'])[0];
								$v['rmb'] = explode('_', $v['name'])[1];
								$s = $db->row("SELECT * FROM `qq3479015851_coin` WHERE name='".$v['xnb']."'");
								$data['list'][0]['name'] = $s['name'];
								$data['list'][0]['img'] = $s['img'];
								$data['list'][0]['title'] = $s['title'];
								$data['list'][0]['new_price'] = round($v['new_price'],4);
								$data['info']['img'] = $s['img'];
								$data['info']['title'] = $s['title'];
								$data['info']['new_price'] = round($v['new_price'],8);
								$data['info']['max_price'] = round($v['max_price'],8);
								$data['info']['min_price'] = round($v['min_price'],8);
//								$data['info']['buy_price'] = round($v['buy_price'],4);
//								$data['info']['sell_price'] = round($v['sell_price'],4);
								$data['info']['volume'] = $v['volume'];
								$data['info']['volumes'] = round($v['volume']*$v['new_price'],2);
								$data['info']['change'] = round($v['change'],4);
								$datas["getJsonTop"] = $data;
							}
						}
						//getJsonTop
						//getDepth
						$data = [];
						$limt = 10;
						for($trade_moshi = 1;$trade_moshi<=1;$trade_moshi++)
						{
							if ($trade_moshi == 1) {
								$buy = $db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade where status=0 and type=1 and num !=deal  and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
								$sell = array_reverse($db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade where status=0 and type=2 and num !=deal  and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
							}
							if ($trade_moshi == 3) {
								$buy = $db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade where status=0 and type=1 and num !=deal  and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
								$sell = null;
							}
							if ($trade_moshi == 4) {
								$buy = null;
								$sell = array_reverse($db->query('select id,price,sum(num-deal)as nums from qq3479015851_trade where status=0 and type=2 and num !=deal  and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
							}

							if ($buy) {
								foreach ($buy as $k => $v) {
									if($v['nums'] > 0) $data[$trade_moshi]['depth']['buy'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
								}
							}
							else {
								$data[$trade_moshi]['depth']['buy'] = '';
							}

							if ($sell) {
								foreach ($sell as $k => $v) {
									if($v['nums'] > 0) $data[$trade_moshi]['depth']['sell'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
								}
							}
							else {
								$data[$trade_moshi]['depth']['sell'] = '';
							}
						}
						$datas["getDepth"] = $data;
						//getDepth
						//getTradelog
						$data = [];
						$tradeLog = $db->query('select * from qq3479015851_trade_log where status=1  and market =\'' . $market . '\'  order by id desc limit 20;');
						foreach ($tradeLog as $k => $v) {
							$data['tradelog'][$k]['addtime'] = date('m-d H:i:s', $v['addtime']);
							$data['tradelog'][$k]['type'] = $v['type'];
							$data['tradelog'][$k]['price'] = $v['price'] * 1;
							$data['tradelog'][$k]['num'] = round($v['num'], 6);
							$data['tradelog'][$k]['mum'] = round($v['mum'], 2);
						}
						$datas["getTradelog"] = $data;
						//getTradelog
						$message_data['type'] = 'data';
						$message_data['time'] = date('Y-m-d H:i:s');
						$message_data['content'] = $datas;
						Gateway::sendToGroup($room_id, json_encode($message_data));
					   echo "--";				   
					   
				   }
				   //电脑推送完毕
				}
		   }
		   echo " \n";	
		   
		   

	   }
	   
	   
   }
  
}
