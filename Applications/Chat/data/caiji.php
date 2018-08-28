<?php
define('MSCODE', '95D3A7E98EE9F913B462B87C73DS');
 error_reporting(0);
//机器人
function caiji()
{
	echo "--".date("Y-m-d H:i:s")."\n";
	global $db;
	require_once  'Connection.php';//采集用
	$apic = new Connection();
	$time = time();
	$isauto = $db->row('select * from qq3479015851_auto  LIMIT 1;');
	$pstime = $isauto['sit'];//延时执行
	$pstime = 0;
	//抓取数据，虚拟交易
	$hui = $isauto['hui'];
	$sui = 0.01+mt_rand()/mt_getrandmax() * (1-0.01);//0.5-1随机数
	
	   //汇率更新
	   /*
	   if($time%5)
	   {
		   $huis = $apic->get_hui();
		   $hui_new = $huis['appraised_rates']['buy_rate'];
			if($hui_new){
				$db->query("UPDATE  `qq3479015851_auto` SET   `hui` =  '$hui_new' WHERE  `aid` =1;");
			}
	   }
		echo "--hui--".$hui."\n";*/
	   //汇率更新end
	
	$coinlist = $db->query('select name,a_auto,a_time,a_max,a_pri from qq3479015851_coin where a_max > 0 and a_time > 0 ;');
	if($coinlist && $hui)
	{
	   $new_pri = $apic->get_pris();
		foreach($coinlist as $ko => $vo )
		{
			$time_n = 0;
			@$time_n = $vo['a_auto']+$vo['a_time'];
			
			if($time_n < $time && $new_pri)
			{
				//计算数量，价格
				$nums = $vo['a_max']*$sui;//随机数量
				if(rand(1,10)==1) $nums = $nums * 8;//10%几率数量翻x倍
				if($vo['name']=='btc'){
					$nums = round($nums,6);
				}else{
					$nums = round($nums,4);
				}
				if($vo['name']=='cnutss'){
					$tiao = 1.2;
					$time = time();
					$pris = $new_pri['trx_usdt']['last'] * $hui* $tiao;//最新价格
					$change = round($new_pri['trx_usdt']['percentChange'],4) ? round($new_pri['trx_usdt']['percentChange'],4):0;//涨幅
					$pris_min = $new_pri['tsl_usdt']['low24hr'] * $hui * $tiao;
					$pris_max = $new_pri['trx_usdt']['high24hr'] * $hui * $tiao;
					$pris_h = $new_pri['trx_usdt']['highestBid'] * $hui * $tiao;
					$pris_l = $new_pri['trx_usdt']['lowestAsk'] * $hui * $tiao;
				}else{
					$pris = $new_pri[$vo['name'].'_usdt']['last'] * $hui;//最新价格
					$change = round($new_pri[$vo['name'].'_usdt']['percentChange'],4) ? round($new_pri[$vo['name'].'_usdt']['percentChange'],4):0;//涨幅
					$pris_min = $new_pri[$vo['name'].'_usdt']['low24hr'] * $hui;//
					$pris_max = $new_pri[$vo['name'].'_usdt']['high24hr'] * $hui;//
					$pris_h = $new_pri[$vo['name'].'_usdt']['highestBid'] * $hui;//
					$pris_l = $new_pri[$vo['name'].'_usdt']['lowestAsk'] * $hui;//
				}
				$pris = round($pris,4);
				
				$market = $vo['name']."_cny";
				
				//由于gate.io喂屎，暂时规避每次插入前判断是否为最高价，不是才插入
				$_price = $db->query("SELECT max_price from qq3479015851_market where name='".$market."' limit 1");
				$_price = round($_price[0]['max_price'], 4);
				echo "\r\n----Compare Price----".$market."----".$_price."----".$pris."-----\r\n";
				if ($pris == $_price) {
					echo "\r\ngate.io feeds us shit\r\n";
					continue;
				}
				
				
				$db->query("UPDATE  `qq3479015851_coin` SET  `a_auto`='$time' WHERE  `name` ='".$vo['name']."';");//执行时间更新
				//虚拟成交
				if($nums && $pris)
				{
				   //执行撤单 
					$addtime = $time - 60 * 10;//撤x分钟以前的单
					$d_num = $db->query("select id from qq3479015851_trade  WHERE userid = 111908 and status = 0 and sort = 0 and `market` ='".$market."' and addtime < '$addtime' ;");
//					$d_num = $db->query("select id from qq3479015851_trade  WHERE userid = 111908 and status = 0 and sort = 0 and `market` ='".$market."' order by addtime asc;");
					echo "--cedan--";
					foreach($d_num as $k=>$v){
						$cedan = $db->query("INSERT INTO `a_auto` (`aid`, `type`, `addtime`, `uptime`, `tid`, `uid`) VALUES (NULL, '1', '$time', '0', '".$v['id']."', '111908');");
					   echo $v['id']."--";
					}
				   //执行撤单end
	
					
					//买单
					   echo "\n--buy--";
					   $m_num = rand(1,5);
					   for ($x=0; $x<$m_num; $x++) {
						   $cprimin = $pris-($pris*rand(1,10)/100);//挂单价格
						   if($vo['name']!="btc") $cprimin = round($cprimin,4);
						   $cnums = $nums * rand(50,90)/100;//挂单数量
							if($vo['name']=='btc'){
								$cnums = round($cnums,6);
							}else{
								$cnums = round($cnums,4);
							}
							$mum = round($cnums * $cprimin, 8);//总价
						   if($cprimin>0.0001 && $cnums > 0.0001){
								$rs = $db->insert('a_market')->cols(array('type' => 1, 'addtime' => $time, 'market' => $market, 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
							   echo $x."--".$market."--".$cprimin."--".$cnums."--";
						   }
					   }
					//买单end
					//卖单
					   echo "\n--buy--";
					   $m_num = rand(1,5);
					   for ($x=0; $x<$m_num; $x++) {
						   $cprimin = $pris+($pris*rand(1,10)/100);//挂单价格
						   if($vo['name']!="btc") $cprimin = round($cprimin,4);
						   $cnums = $nums * rand(50,90)/100;//挂单数量
							if($vo['name']=='btc'){
								$cnums = round($cnums,6);
							}else{
								$cnums = round($cnums,4);
							}
							$mum = round($cnums * $cprimin, 8);//总价
						   if($cprimin>0.0001 && $cnums > 0.0001){
								$rs = $db->insert('a_market')->cols(array('type' => 2, 'addtime' => $time, 'market' => $market, 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
							   echo $x."--".$market."--".$cprimin."--".$cnums."--";
						   }
					   }
					//卖单end
					   //收购价格低的卖单
					   //查询比当前价格低的卖单
						$sell = $db->query("select * from qq3479015851_trade  WHERE userid != 111908 and status = 0 and sort = 0 and type = 2 and `market` ='".$market."' and price < '$pris';");
					   if($sell)
					   {
						   echo "\n--m_sell--";
						   foreach($sell as $k1=>$v1){
							   $cprimin = $v1['price'];//价格
							   $cnums = $v1['num'];//数量
								$mum = round($cnums * $cprimin, 8);
							   if($cprimin>0.0001 && $cnums > 0.0001){
								$rs = $db->insert('a_market')->cols(array('type' => 1, 'addtime' => $time, 'market' => $market, 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
								   echo $k."--".$market."--".$cprimin."--".$cnums."--";
							   }
						   }
					   }
					   //收购价格高的买单
					   //查询比当前价格低的卖单
						$sell = $db->query("select * from qq3479015851_trade  WHERE userid != 111908 and status = 0 and sort = 0 and type = 1 and `market` ='".$market."' and price > '$pris';");
					   if($sell)
					   {
						   echo "\n--m_buy--";
						   foreach($sell as $k2=>$v2){
							   $cprimin = $v2['price'];//价格
							   $cnums = $v2['num'];//数量
								$mum = round($cnums * $cprimin, 8);
							   if($cprimin>0.0001 && $cnums > 0.0001){
								$rs = $db->insert('a_market')->cols(array('type' => 2, 'addtime' => $time, 'market' => $market, 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
								   echo $k."--".$market."--".$cprimin."--".$cnums."--";
							   }
						   }
					   }
					   //收购end
					   //虚拟成交
					   $cnum = $nums;//数量
					   $dpri = $pris;//单价
						$cpri = $cnum * $dpri;//总价
					   $ctp = rand(1,2);
					   echo "\n--xuni--".$market."--".$dpri."--".$cnum."--"."--".$cpri."--";
					   
					
						$db->query("INSERT INTO `qq3479015851_trade_log` (`id`, `userid`, `peerid`, `market`, `price`, `num`, `mum`, `fee_buy`, `fee_sell`, `type`, `sort`, `addtime`, `endtime`, `status`) VALUES (NULL, '0', '0', '".$market."', '".$dpri."', '$cnum', '$cpri', '0', '0', '$ctp', '0', '$time', '0', '1');");
						   //修改实时价格
						   echo "\n--edit_now_time--".$market."--".$dpri;
							//$db->query("UPDATE  `qq3479015851_market` SET `change` = '$change',`new_price` =  '".$dpri."',`min_price` =  '".$pris_min."',`max_price` =  '".$pris_max."',`buy_price` =  '".$pris_h."',`sell_price` =  '".$pris_l."'  WHERE  `name` ='".$market."';");
//							$db->query("UPDATE  `qq3479015851_market` SET `new_price` =  '".$dpri."',`min_price` =  '".$pris_min."',`max_price` =  '".$pris_max."',`buy_price` =  '".$pris_h."',`sell_price` =  '".$pris_l."'  WHERE  `name` ='".$market."';");
							//$db->query("UPDATE  `qq3479015851_market` SET `new_price` =  '".$dpri."',`max_price` =  '".$pris_max."',`buy_price` =  '".$pris_h."',`sell_price` =  '".$pris_l."'  WHERE  `name` ='".$market."';");
							$db->query("UPDATE  `qq3479015851_market` SET `new_price` =  '".$dpri."',`buy_price` =  '".$pris_h."',`sell_price` =  '".$pris_l."'  WHERE  `name` ='".$market."';");
					   //虚拟成交end

				}
			}
			
			
		}
		
	}
   echo "\n";
	
	
	
}


//成交
function chengjiao()
{
	global $db;
	$time = time();
 	$t1 = microtime(true);
	$starttime = explode(' ',microtime());
	//优先撤销
	$auto_cx = $db->query("select * from a_auto  WHERE type = 1 order by type desc LIMIT 10;");
	if($auto_cx)
	{
		echo "\n--cedan--";
	//撤单
		foreach($auto_cx as $k=>$v)
		{
			//$db->query('set autocommit=0');
			//进入撤销
			$trade = $db->query("select * from qq3479015851_trade  WHERE id ='".$v['tid']."' ;");
			$trade = $trade[0];
			$xnb = explode('_', $trade['market'])[0];
			$rmb = explode('_', $trade['market'])[1];
			$market = $db->query("select * from qq3479015851_market  WHERE name ='".$trade['market']."' ;");
			$market = $market[0];
			$fee_buy = $market['fee_buy'];
			$fee_sell = $market['fee_sell'];
	//		$db->beginTrans('lock tables qq3479015851_user_coin write  , qq3479015851_trade write ,qq3479015851_finance write');
			//开始
			//最后检查冻结为负问题
			if ($trade['type'] == 1) 
			{
				//解冻数量
				$mun = round(((($trade['num'] - $trade['deal']) * $trade['price']) / 100) * (100 + $fee_buy), 8);
				$save_buy_rmb = $mun;

//				$finance = $db->query("select * from qq3479015851_finance  WHERE userid ='".$trade['userid']."' order by id desc LIMIT 1;");
//				$finance = $finance[0];
//				$finance_num_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$trade['userid']."' ;");
//				$finance_num_user_coin = $finance_num_user_coin[0];
				//解冻
				$rs[] = $db->query("update qq3479015851_user_coin set cny = cny + $save_buy_rmb, cnyd = cnyd - $save_buy_rmb  where userid = ".$trade["userid"]." limit 1");
//				$finance_nameid = $trade['id'];
//				$finance_mum_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$trade['userid']."' ;");
//				$finance_mum_user_coin = $finance_mum_user_coin[0];
//				//MSCODE ？？
//				$finance_hash = md5($trade['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $save_buy_rmb . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.qq3479015851.com');
//				$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];
//
//				if ($finance['mum'] < $finance_num) {
//					$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
//				}
//				else {
//					$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
//				}

//				$rs[] = $db->insert('qq3479015851_finance')->cols(array('userid' => $trade['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 1, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-交易撤销' . $trade['market'], 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status))->query();

				$rs[] = $db->query("update qq3479015851_trade set status = 2  where id = ".$trade["id"]." limit 1");
				//$you_buy = $db->query("select * from qq3479015851_trade  WHERE market like'%$rmb%' and status = 0 and userid = '".$trade['userid']."'  ;");
				//冻结负数清0
				$you_buy = $db->query("update  qq3479015851_user_coin  set ".$xnb."d"." = 0  WHERE  userid = '".$trade['userid']."' and $xnb"."d"." < 0  ;");
			}
			else if ($trade['type'] == 2) 
			{
				$mun = round($trade['num'] - $trade['deal'], 8);
				$save_sell_xnb = $mun;
				if (0 < $save_sell_xnb) {
					//解冻
					$rs[] = $db->query("update qq3479015851_user_coin set $xnb = $xnb + $save_sell_xnb, $xnb"."d"." = $xnb"."d"." - $save_sell_xnb  where userid = ".$trade["userid"]." limit 1");
				}
				$rs[] = $db->query("update qq3479015851_trade set status = 2  where id = ".$trade["id"]." limit 1");
				//冻结负数清0
				$you_buy = $db->query("update  qq3479015851_user_coin  set ".$xnb."d"." = 0  WHERE  userid = '".$trade['userid']."' and $xnb"."d"." < 0  ;");


			}
			//标记
			$db->query("UPDATE a_auto  SET `type` = 0,`uptime` = '$time'  WHERE aid ='".$v['aid']."' ;");
	//		$db->beginTrans('unlock tables');
			echo $k."--";
		}
	}
	else
	{
		$auto_jy = $db->query("select * from a_market  WHERE stat = 1 and userid !=111908 order by mid asc LIMIT 1;");
		@$auto_jy = $auto_jy[0];
		if(!$auto_jy){
			$auto_jy = $db->query("select * from a_market  WHERE stat = 1 order by mid asc LIMIT 1;");
			@$auto_jy = $auto_jy[0];
		}
		//写入交易
			if($auto_jy)
			{
				$rs = array();
				$user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid = '".$auto_jy['userid']."' ;");
				$user_coin = $user_coin[0];
				$market = $auto_jy['market'];
				$xnb = explode('_', $market)[0];
				$rmb = explode('_', $market)[1];
				$type = $auto_jy['type'];
				$price = $auto_jy['pri'];
				$num = $auto_jy['num'];
				$mum = $auto_jy['mum'];
				$mid = $auto_jy['mid'];
				$fee = $auto_jy['fee'];
				$userid = $auto_jy['userid'];
				echo "--jiaoyi--".$market."--".$userid."--\n";
				
				if ($type == 1) 
				{
					if ($user_coin[$rmb] < $mum) {
						$auto_cx = $db->query("UPDATE  `a_market` SET  `stat` =  '2',`uptime` =  '$time' WHERE  `mid` ='$mid';");
						return false;
					}

//					$finance = $db->query("select * from qq3479015851_finance  WHERE userid = '".$userid."' order by id desc LIMIT 1;");
//					$finance = $finance[0];
//					$finance_num_user_coin = $user_coin;
					
					//设置冻结
					$rs[] = $db->query("UPDATE qq3479015851_user_coin  set $rmb = $rmb-$mum,$rmb"."d"." = $rmb"."d"."+$mum where userid =  '".$userid."' ;");
					//插入交易
					$finance_nameid = $db->query("INSERT INTO `qq3479015851_trade` (`id`, `userid`, `market`, `price`, `num`, `deal`, `mum`, `fee`, `type`, `sort`, `addtime`, `endtime`, `status`, `auto`) VALUES (NULL, '$userid', '$market', '$price', '$num', '0.00000000', '$mum', '$fee', '1', '0', '$time', '0', '0', '0');");
 					

//					if($rmb == "cny"){
//						$finance_mum_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid = '".$userid."' ;");
//						$finance_mum_user_coin = $finance_mum_user_coin[0];
//						$finance_hash = md5($userid . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.qq3479015851.com');
//						$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];
//
//						if ($finance['mum'] < $finance_num) {
//							$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
//						}
//						else {
//							$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
//						}
//
//						$rs[] = $db->insert('qq3479015851_finance')->cols(array('userid' => $userid, 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mum, 'type' => 2, 'name' => 'trade', 'nameid' => $finance_nameid, 'remark' => '交易中心-委托买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status))->query();
//					}
				}
				else if ($type == 2) 
				{
					if ($user_coin[$xnb] < $num) {
						$auto_cx = $db->query("UPDATE  `a_market` SET  `stat` =  '2',`uptime` =  '$time' WHERE  `mid` ='$mid';");
						return false;
						exit;
					}
					$rs[] = $db->query("UPDATE qq3479015851_user_coin  set $xnb = $xnb-$num,$xnb"."d"." = $xnb"."d"."+$num where userid =  '".$userid."' ;");
					$rs[] = $db->query("INSERT INTO `qq3479015851_trade` (`id`, `userid`, `market`, `price`, `num`, `deal`, `mum`, `fee`, `type`, `sort`, `addtime`, `endtime`, `status`, `auto`) VALUES (NULL, '$userid', '$market', '$price', '$num', '0.00000000', '$mum', '$fee', '2', '0', '$time', '0', '0', '0');");
				}
				
				
				$db->query("UPDATE  `a_market` SET  `stat` =  '0',`uptime` =  '$time' WHERE  `mid` ='$mid';");
				$markets = $db->query("select * from qq3479015851_market  WHERE name ='".$market."' ;");
				$markets = $markets[0];
				$fee_buy = $markets['fee_buy'];
				$fee_sell = $markets['fee_sell'];
				$invit_buy = $markets['invit_buy'];
				$invit_sell = $markets['invit_sell'];
				$invit_1 = $markets['invit_1'];
				$invit_2 = $markets['invit_2'];
				$invit_3 = $markets['invit_3'];
				$new_trade_auto = 0;

				for (; true; ) 
				{
					$buy = $db->query("select * from qq3479015851_trade  WHERE market ='".$market."' and type = 1 and  status = 0 order by price desc,id asc LIMIT 1;");
					@$buy = $buy[0];
					$sell = $db->query("select * from qq3479015851_trade  WHERE market ='".$market."' and type = 2 and  status = 0 order by price asc,id asc LIMIT 1;");
					@$sell = $sell[0];
					if ($sell['id'] < $buy['id']) {
						$type = 1;
					}
					else {
						$type = 2;
					}

					if ($buy && $sell && (0 <= floatval($buy['price']) - floatval($sell['price']))) 
					{
						$rs = array();

						$amount = min(round($buy['num'] - $buy['deal'], 8 - $markets['round']), round($sell['num'] - $sell['deal'], 8 - $markets['round']));
						$amount = round($amount, 8 - $markets['round']);

						if ($amount <= 0) {
							$log = '错误1交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . "\n";
							$log .= 'ERR: 成交数量出错，数量是' . $amount;
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$buy['id']."';");
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$sell['id']."';");
							break;
						}

						if ($type == 1) {
							$price = $sell['price'];
						}
						else if ($type == 2) {
							$price = $buy['price'];
						}
						else {
							break;
						}

						if (!$price) {
							$log = '错误2交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
							$log .= 'ERR: 成交价格出错，价格是' . $price;
							break;
						}
						else {
							// TODO: SEPARATE
							$price = round($price, $markets['round']);
						}

						$mum = round($price * $amount, 8);

						if (!$mum) {
							$log = '错误3交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . "\n";
							$log .= 'ERR: 成交总额出错，总额是' . $mum;
							break;
						}
						else {
							$mum = round($mum, 8);
						}

						if ($fee_buy) {
							$fee_buy = round($buy['fee'] / ($buy['mum'] - $buy['fee']),4);
							$buy_fee = round(($mum / 100) * $fee_buy, 8);
							$buy_save = round(($mum / 100) * (100 + $fee_buy), 8);
						}
						else {
							$buy_fee = 0;
							$buy_save = $mum;
						}

						if (!$buy_save) {
							$log = '错误4交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家更新数量出错，更新数量是' . $buy_save;
							break;
						}

						if ($fee_sell) {
							$fee_sell = round($buy['fee'] / ($buy['mum'] + $buy['fee']),4);
							$sell_fee = round(($mum / 100) * $fee_sell, 8);
							$sell_save = round(($mum / 100) * (100 - $fee_sell), 8);
						}
						else {
							$sell_fee = 0;
							$sell_save = $mum;
						}

						if (!$sell_save) {
							$log = '错误5交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 卖家更新数量出错，更新数量是' . $sell_save;
							break;
						}

						$user_buy = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$buy['userid']."' LIMIT 1;");
						$user_buy = $user_buy[0];

						if (!$user_buy[$rmb . 'd']) {
							$log = '错误6交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家财产错误，冻结财产是' . $user_buy[$rmb . 'd'];
							break;
						}

						$user_sell = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$sell['userid']."' LIMIT 1;");
						$user_sell = $user_sell[0];

						if (!$user_sell[$xnb . 'd']) {
							$log = '错误7交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 卖家财产错误，冻结财产是' . $user_sell[$xnb . 'd'];
							break;
						}

						if ($user_buy[$rmb . 'd'] < 1.0E-8) {
							$log = '错误88交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$buy['id']."';");
							break;
						}

						if ($buy_save <= round($user_buy[$rmb . 'd'], 8)) {
							$save_buy_rmb = $buy_save;
						}
						else if ($buy_save <= round($user_buy[$rmb . 'd'], 8) + 1) {
							$save_buy_rmb = $user_buy[$rmb . 'd'];
							$log = '错误8交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家更新冻结人民币出现误差,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '实际更新' . $save_buy_rmb;
						}
						else {
							$log = '错误9交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家更新冻结人民币出现错误,应该更新' . $buy_save . '账号余额' . $user_buy[$rmb . 'd'] . '进行错误处理';
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$buy['id']."';");
							break;
						}
						// TODO: SEPARATE

						if ($amount <= round($user_sell[$xnb . 'd'], $markets['round'])) {
							$save_sell_xnb = $amount;
						}
						else 
						{
							// TODO: SEPARATE

							if ($amount <= round($user_sell[$xnb . 'd'], $markets['round']) + 1) {
								$save_sell_xnb = $user_sell[$xnb . 'd'];
								$log = '错误10交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
								$log .= 'ERR: 卖家更新冻结虚拟币出现误差,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '实际更新' . $save_sell_xnb;
							}
							else {
								$log = '错误11交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
								$log .= 'ERR: 卖家更新冻结虚拟币出现错误,应该更新' . $amount . '账号余额' . $user_sell[$xnb . 'd'] . '进行错误处理';
								$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$sell['id']."';");
								break;
							}
						}

						if (!$save_buy_rmb) {
							$log = '错误12交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 买家更新数量出错错误,更新数量是' . $save_buy_rmb;
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$buy['id']."';");
							break;
						}

						if (!$save_sell_xnb) {
							$log = '错误13交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount . '成交价格' . $price . '成交总额' . $mum . "\n";
							$log .= 'ERR: 卖家更新数量出错错误,更新数量是' . $save_sell_xnb;
							$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '1' WHERE  `id` ='".$sell['id']."';");
							break;
						}
						
						$rs[] =  $db->query("UPDATE  `qq3479015851_trade` SET  `deal` = deal +$amount  WHERE  `id` ='".$buy['id']."';");
						$rs[] =  $db->query("UPDATE  `qq3479015851_trade` SET  `deal` = deal +$amount  WHERE  `id` ='".$sell['id']."';");
						$rs[] = $finance_nameid = $db->query("INSERT INTO `qq3479015851_trade_log` (`id`, `userid`, `peerid`, `market`, `price`, `num`, `mum`, `type`, `fee_buy`, `fee_sell`, `addtime`, `status`) VALUES (NULL, '".$buy['userid']."', '".$sell['userid']."', '$market', '$price', '$amount', '$mum', '$type', '$buy_fee', '$sell_fee', '$time', '1');");
						//new修改实时价格snjeso
						$rs[] =  $db->query("UPDATE  `qq3479015851_user_coin` SET  `$xnb` = $xnb + $amount  WHERE  `userid` ='".$buy['userid']."';");
						$finance = $db->query("select * from qq3479015851_finance  WHERE userid ='".$buy['userid']."' order by id desc LIMIT 1;");
						$finance = $finance[0];
						$finance_num_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$buy['userid']."'  LIMIT 1;");
						$finance_num_user_coin = $finance_num_user_coin[0];
						$rs[] =  $db->query("UPDATE  `qq3479015851_user_coin` SET  `$rmb"."d"."` = $rmb"."d"." - $save_buy_rmb  WHERE  `userid` ='".$buy['userid']."';");
						$finance_mum_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$buy['userid']."'  LIMIT 1;");
						$finance_mum_user_coin = $finance_mum_user_coin[0];
						$finance_hash = md5($buy['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.qq3479015851.com');
						$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

						if ($finance['mum'] < $finance_num) {
							$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
						}
						else {
							$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
						}


						if($rmb == "cny"){
//							$rs[] = $db->insert('qq3479015851_finance')->cols(array('userid' => $buy['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 2, 'name' => 'tradelog', 'nameid' => $finance_nameid, 'remark' => '交易中心-成功买入-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status))->query();
						}

						$finance = $db->query("select * from qq3479015851_finance  WHERE userid ='".$buy['userid']."' order by id desc LIMIT 1;");
						$finance = $finance[0];
						//$finance = $mo->table('qq3479015851_finance')->where(array('userid' => $buy['userid']))->order('id desc')->find();
						$finance_num_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$sell['userid']."'  LIMIT 1;");
						$finance_num_user_coin = $finance_num_user_coin[0];
						//$finance_num_user_coin = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->find();
						$rs[] =  $db->query("UPDATE  `qq3479015851_user_coin` SET  `$rmb` = $rmb + $sell_save  WHERE  `userid` ='".$sell['userid']."';");
						//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $sell_save);
						$finance_mum_user_coin = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$sell['userid']."'  LIMIT 1;");
						$finance_mum_user_coin = $finance_mum_user_coin[0];
						//$finance_mum_user_coin = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->find();
						$finance_hash = md5($sell['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'auth.qq3479015851.com');
						$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

						if ($finance['mum'] < $finance_num) {
							$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
						}
						else {
							$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
						}


						if($rmb == "cny"){
//							$rs[] = $db->insert('qq3479015851_finance')->cols(array('userid' => $sell['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $save_buy_rmb, 'type' => 1, 'name' => 'tradelog', 'nameid' => $finance_nameid, 'remark' => '交易中心-成功卖出-市场' . $market, 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status))->query();
						}


						$rs[] =  $db->query("UPDATE  `qq3479015851_user_coin` SET  `$xnb"."d"."` = $xnb"."d"." - $save_sell_xnb  WHERE  `userid` ='".$sell['userid']."';");
						//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->setDec($xnb . 'd', $save_sell_xnb);
						$buy_list = $db->query("select * from qq3479015851_trade  WHERE id ='".$buy['id']."' and status = 0  LIMIT 1;");
						$buy_list = $buy_list[0];
						//$buy_list = $mo->table('qq3479015851_trade')->where(array('id' => $buy['id'], 'status' => 0))->find();

						if ($buy_list) {
							if ($buy_list['num'] <= $buy_list['deal']) {
								$rs[] =  $db->query("UPDATE  `qq3479015851_trade` SET  status = 1  WHERE  `id` ='".$buy['id']."';");
								//$rs[] = $mo->table('qq3479015851_trade')->where(array('id' => $buy['id']))->setField('status', 1);
							}
						}
						$sell_list = $db->query("select * from qq3479015851_trade  WHERE id ='".$sell['id']."' and status = 0  LIMIT 1;");
						$sell_list = $sell_list[0];
						//$sell_list = $mo->table('qq3479015851_trade')->where(array('id' => $sell['id'], 'status' => 0))->find();

						if ($sell_list) {
							if ($sell_list['num'] <= $sell_list['deal']) {
								$rs[] =  $db->query("UPDATE  `qq3479015851_trade` SET  status = 1  WHERE  `id` ='".$sell['id']."';");
								//$rs[] = $mo->table('qq3479015851_trade')->where(array('id' => $sell['id']))->setField('status', 1);
							}
						}

						if ($price < $buy['price']) {
							$chajia_dong = round((($amount * $buy['price']) / 100) * (100 + $fee_buy), 8);
							$chajia_shiji = round((($amount * $price) / 100) * (100 + $fee_buy), 8);
							$chajia = round($chajia_dong - $chajia_shiji, 8);

							if ($chajia) {
								$chajia_user_buy = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$buy['userid']."'  LIMIT 1;");
								$chajia_user_buy = $chajia_user_buy[0];
								//$chajia_user_buy = $mo->table('qq3479015851_user_coin')->where(array('userid' => $buy['userid']))->find();

								if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8)) {
									$chajia_save_buy_rmb = $chajia;
								}
								else if ($chajia <= round($chajia_user_buy[$rmb . 'd'], 8) + 1) {
									$chajia_save_buy_rmb = $chajia_user_buy[$rmb . 'd'];
									mlog('错误91交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
									mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现误差,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '实际更新' . $chajia_save_buy_rmb);
								}
								else {
									mlog('错误92交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '交易方式：' . $type . '成交数量' . $amount, '成交价格' . $price . '成交总额' . $mum . "\n");
									mlog('交易市场' . $market . '出错：买入订单:' . $buy['id'] . '卖出订单：' . $sell['id'] . '成交数量' . $amount . '交易方式：' . $type . '卖家更新冻结虚拟币出现错误,应该更新' . $chajia . '账号余额' . $chajia_user_buy[$rmb . 'd'] . '进行错误处理');
									$db->query("UPDATE  `qq3479015851_trade` SET  status = 1  WHERE  `id` ='".$buy['id']."';");
									break;
								}

								if ($chajia_save_buy_rmb) {
									$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$chajia_save_buy_rmb,$rmb"."d"." = $rmb"."d"."-$chajia_save_buy_rmb  WHERE  `userid` ='".$buy['userid']."';");
								}
							}
						}
						$you_buy = $db->query("select * from qq3479015851_trade  WHERE userid ='".$buy['userid']."' and market like '%$rmb%'  and status = 0  LIMIT 1;");
						@$you_buy = $you_buy[0];
						$you_sell = $db->query("select * from qq3479015851_trade  WHERE userid ='".$sell['userid']."' and market like '%$xnb%' and status = 0  LIMIT 1;");
						@$you_sell = $you_sell[0];
						if (!$you_buy) {
							//$you_user_buy = $mo->table('qq3479015851_user_coin')->where(array('userid' => $buy['userid']))->find();
//							$rmb_ti = $rmb * 0.15/100;
//							$rs[] = $db->query("UPDATE  `a_eth` SET  cny = cny+$rmb_ti  WHERE  `userid` ='".$buy['userid']."';");
							if (0 < $you_user_buy[$rmb . 'd']) {
								$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+".$you_user_buy[$rmb . 'd'].",$rmb"."d"." = 0  WHERE  `userid` ='".$buy['userid']."';");
								//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $buy['userid']))->setField($rmb . 'd', 0);
								//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $buy['userid']))->setInc($rmb, $you_user_buy[$rmb . 'd']);
							}
						}
						if (!$you_sell) {
							$you_user_buy = $db->query("select * from qq3479015851_user_coin  WHERE userid ='".$sell['userid']."'  LIMIT 1;");
							$you_user_buy = $you_user_buy[0];
							//$you_user_sell = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->find();
							if (0 < $you_user_sell[$xnb . 'd']) {
								$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+".$you_user_sell[$xnb . 'd'].",$xnb"."d"." = 0  WHERE  `userid` ='".$sell['userid']."';");
								//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->setField($xnb . 'd', 0);
								//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $sell['userid']))->setInc($rmb, $you_user_sell[$xnb . 'd']);
							}
						}
						$invit_buy_user = $db->query("select * from qq3479015851_user  WHERE id ='".$buy['userid']."'  LIMIT 1;");
						$invit_buy_user = $invit_buy_user[0];
						$invit_sell_user = $db->query("select * from qq3479015851_user  WHERE id ='".$sell['userid']."'  LIMIT 1;");
						$invit_sell_user = $invit_sell_user[0];

						if ($invit_buy) {
							if ($invit_1) {
								if ($buy_fee) {
									if ($invit_buy_user['invit_1']) {
										$invit_buy_save_1 = round(($buy_fee / 100) * $invit_1, 6);

										if ($invit_buy_save_1) {
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_buy_user['invit_1']))->setInc($rmb, $invit_buy_save_1);
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_buy_save_1  WHERE  `userid` ='".$invit_buy_user['invit_1']."';");
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_buy_user['invit_1'], 'invit' => $buy['userid'], 'name' => '一代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_buy_save_1, 'addtime' => time(), 'status' => 1))->query();
										}
									}

									if ($invit_buy_user['invit_2']) {
										$invit_buy_save_2 = round(($buy_fee / 100) * $invit_2, 6);

										if ($invit_buy_save_2) {
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_buy_user['invit_2']))->setInc($rmb, $invit_buy_save_2);
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_buy_save_2  WHERE  `userid` ='".$invit_buy_user['invit_2']."';");
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_buy_user['invit_2'], 'invit' => $buy['userid'], 'name' => '二代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_buy_save_2, 'addtime' => time(), 'status' => 1))->query();
											
										}
									}

									if ($invit_buy_user['invit_3']) {
										$invit_buy_save_3 = round(($buy_fee / 100) * $invit_3, 6);
										if ($invit_buy_save_3) {
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_buy_user['invit_3']))->setInc($rmb, $invit_buy_save_3);
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_buy_save_3  WHERE  `userid` ='".$invit_buy_user['invit_3']."';");
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_buy_user['invit_3'], 'invit' => $buy['userid'], 'name' => '三代买入赠送', 'type' => $market . '买入交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_buy_save_3, 'addtime' => time(), 'status' => 1))->query();
										}
									}
								}
							}

							if ($invit_sell) {
								if ($sell_fee) {
									if ($invit_sell_user['invit_1']) {
										$invit_sell_save_1 = round(($sell_fee / 100) * $invit_1, 6);

										if ($invit_sell_save_1) {
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_sell_save_1  WHERE  `userid` ='".$invit_sell_user['invit_1']."';");
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_sell_user['invit_1']))->setInc($rmb, $invit_sell_save_1);
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_sell_user['invit_1'], 'invit' => $sell['userid'], 'name' => '一代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_sell_save_1, 'addtime' => time(), 'status' => 1));
										}
									}

									if ($invit_sell_user['invit_2']) {
										$invit_sell_save_2 = round(($sell_fee / 100) * $invit_2, 6);

										if ($invit_sell_save_2) {
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_sell_save_2  WHERE  `userid` ='".$invit_sell_user['invit_2']."';");
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_sell_user['invit_2']))->setInc($rmb, $invit_sell_save_2);
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_sell_user['invit_2'], 'invit' => $sell['userid'], 'name' => '二代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_sell_save_2, 'addtime' => time(), 'status' => 1))->query();
										}
									}

									if ($invit_sell_user['invit_3']) {
										$invit_sell_save_3 = round(($sell_fee / 100) * $invit_3, 6);

										if ($invit_sell_save_3) {
											$rs[] = $db->query("UPDATE  `qq3479015851_user_coin` SET  $rmb = $rmb+$invit_sell_save_3  WHERE  `userid` ='".$invit_sell_user['invit_3']."';");
											//$rs[] = $mo->table('qq3479015851_user_coin')->where(array('userid' => $invit_sell_user['invit_3']))->setInc($rmb, $invit_sell_save_3);
											$rs[] = $db->insert('qq3479015851_invit')->cols(array('userid' => $invit_sell_user['invit_3'], 'invit' => $sell['userid'], 'name' => '三代卖出赠送', 'type' => $market . '卖出交易赠送', 'num' => $amount, 'mum' => $mum, 'fee' => $invit_sell_save_3, 'addtime' => time(), 'status' => 1))->query();
										}
									}
								}
							}
						}
						$new_trade_auto = 1;
					}
					else {
						break;
					}

				}
		}
	}
	$endtime = explode(' ',microtime());
	$thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
	//$thistime = round($thistime,8);
	if($thistime) echo '>time--'.$thistime."-- \n";
	//echo ">>>".md5(rand(1111,22222))."\n";
}

// 定期更新数据
function update_prices()
{
	global $db; 
	echo "\r\n".date("m-d H:i:s")." update_prices \r\n";
	
	$marketlist = $db->query("select name from qq3479015851_market where status=1");
	foreach($marketlist as $k => $v){
		$new_trade_auto = 1;
		
		$market = $v['name'];
		
		echo "\r\n".$market."\r\n";
		$db->beginTrans();
		if ($new_trade_auto) {
			//$otime = time()- 24*60*60;
			$otime = 0;
			$new_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime'  order by id desc  LIMIT 1;");
			@$new_price = round($new_price[0]['price'],6);
			$hou_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime'  order by id asc  LIMIT 1;");
			@$hou_price = round($hou_price[0]['price'],6);
			$buy_price = $db->query("select price from qq3479015851_trade  WHERE market ='$market' and status = 0 and type=1 and addtime > '$otime' order by price desc  LIMIT 1;");
			@$buy_price = round($buy_price[0]['price'],6);
			$sell_price = $db->query("select price from qq3479015851_trade  WHERE market ='$market' and status = 0 and type=2 and addtime > '$otime' order by price asc  LIMIT 1;");
			@$sell_price = round($sell_price[0]['price'],6);
			$min_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' order by price asc  LIMIT 1;");
			@$min_price = round($min_price[0]['price'],6);
			$max_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' order by price desc  LIMIT 1;");
			@$max_price = round($max_price[0]['price'],6);
			$volume = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' ;");
			@$volume = round($volume[0]['sum'],6);
			//$sta_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime' order by id asc LIMIT 1;");
			//@$sta_price = round($sta_price[0]['price'],6);
			$Cmarket = $db->query("select * from qq3479015851_market  WHERE name ='$market'  LIMIT 1;");
			@$Cmarket = $Cmarket[0];
			
			//if ($Cmarket['new_price'] != $new_price) {
				$upCoinData['new_price'] = $new_price;
			//}

			//if ($Cmarket['buy_price'] != $buy_price) {
				$upCoinData['buy_price'] = $buy_price;
			//}

			//if ($Cmarket['sell_price'] != $sell_price) {
				$upCoinData['sell_price'] = $sell_price;
			//}
			
			//if ($Cmarket['min_price'] != $min_price) {
				$upCoinData['min_price'] = $min_price;
			//}

			//if ($Cmarket['max_price'] != $max_price) {
				$upCoinData['max_price'] = $max_price;
			//}

			//if ($Cmarket['volume'] != $volume) {
				$upCoinData['volume'] = $volume;
			//}
			//if ($Cmarket['hou_price'] != $hou_price) {
				$upCoinData['hou_price'] = $hou_price;
			//}
			
			if ($Cmarket['hou_price'] == 0)
                $change = 0;
            else
                $change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 8);
            $upCoinData['change'] = $change;
			
			//if ($hou_price == 0) continue;
			//if ($Cmarket['hou_price'] == 0) continue;
			
			//$change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 8);
			//$upCoinData['change'] = $change;
			
			if ($upCoinData) {
				$row_count = $db->update('qq3479015851_market')->cols($upCoinData)->where("name = '$market' ")->query();
				//$db->query('commit');
			}
		}
		
		$db->commitTrans();
	}
}

function update_price($market)
{
	global $db; 
	echo "\r\n".date("m-d H:i:s")." update_prices \r\n";
	
	$new_trade_auto = 1;
	
		
		echo "\r\n".$market."\r\n";
		$db->beginTrans();
		if ($new_trade_auto) {
			$otime = time()- 24*60*60*2;
			$new_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime'  order by id desc  LIMIT 1;");
			@$new_price = round($new_price[0]['price'],6);
			$hou_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime'  order by id asc  LIMIT 1;");
			@$hou_price = round($hou_price[0]['price'],6);
			$buy_price = $db->query("select price from qq3479015851_trade  WHERE market ='$market' and status = 0 and type=1 and addtime > '$otime' order by price desc  LIMIT 1;");
			@$buy_price = round($buy_price[0]['price'],6);
			$sell_price = $db->query("select price from qq3479015851_trade  WHERE market ='$market' and status = 0 and type=2 and addtime > '$otime' order by price asc  LIMIT 1;");
			@$sell_price = round($sell_price[0]['price'],6);
			$min_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' order by price asc  LIMIT 1;");
			@$min_price = round($min_price[0]['price'],6);
			$max_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' order by price desc  LIMIT 1;");
			@$max_price = round($max_price[0]['price'],6);
			$volume = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market ='$market' and addtime > '$otime' ;");
			@$volume = round($volume[0]['sum'],6);
			//$sta_price = $db->query("select price from qq3479015851_trade_log  WHERE market ='$market' and status = 1 and addtime > '$otime' order by id asc LIMIT 1;");
			//@$sta_price = round($sta_price[0]['price'],6);
			$Cmarket = $db->query("select * from qq3479015851_market  WHERE name ='$market'  LIMIT 1;");
			@$Cmarket = $Cmarket[0];
			
			if ($Cmarket['new_price'] != $new_price) {
				$upCoinData['new_price'] = $new_price;
			}

			if ($Cmarket['buy_price'] != $buy_price) {
				$upCoinData['buy_price'] = $buy_price;
			}

			if ($Cmarket['sell_price'] != $sell_price) {
				$upCoinData['sell_price'] = $sell_price;
			}
			
			if ($Cmarket['min_price'] != $min_price) {
				$upCoinData['min_price'] = $min_price;
			}

			if ($Cmarket['max_price'] != $max_price) {
				$upCoinData['max_price'] = $max_price;
			}

			if ($Cmarket['volume'] != $volume) {
				$upCoinData['volume'] = $volume;
			}
			if ($Cmarket['hou_price'] != $hou_price) {
				$upCoinData['hou_price'] = $hou_price;
			}
			
			if ($Cmarket['hou_price'] == 0)
                $change = 0;
            else
                $change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 8);
            $upCoinData['change'] = $change;
			
			echo(var_dump($upCoinData));
			
			//if ($hou_price == 0) continue;
			//if ($Cmarket['hou_price'] == 0) continue;
			
			//$change = round((($new_price - $Cmarket['hou_price']) / $Cmarket['hou_price']) * 100, 8);
			//$upCoinData['change'] = $change;
			
			if ($upCoinData) {
				$row_count = $db->update('qq3479015851_market')->cols($upCoinData)->where("name = '$market' ")->query();
				//$db->query('commit');
			}
		}
		
		$db->commitTrans();
}


	//清理自动数据
function auto_chengjiao()
{
	global $db; 
	echo date("m-d H:i:s")." auto_chengjiao \n";
	$oldtime = time()-1*60*60;//清理多少小时前的数据
	$db->query("DELETE FROM `a_auto` WHERE   uptime < '$oldtime' ");
	$db->query("DELETE FROM `a_market` WHERE  uptime < '$oldtime' ");
}
	//清理j交易数据
function auto_jiaoyi()
{
	global $db;
	echo date("m-d H:i:s")." auto_jiaoyi \n";
	$oldtime7 = time()-7*24*60*60;//7天
	$oldtime2 = time()-2*24*60*60;//2天
	$oldtime1 = time()-1*24*60*60;//1天
//	$db->query("DELETE FROM `qq3479015851_trade` WHERE addtime < '$oldtime7' and status !=0");
	$db->query("DELETE FROM `qq3479015851_trade` WHERE userid=111908 and status !=0");
	
//	$db->query("DELETE FROM `qq3479015851_trade_log` WHERE addtime < '$oldtime7' ");
//	$db->query("DELETE FROM `qq3479015851_trade_log` WHERE addtime < '$oldtime7' and userid = 0 ");
//	$db->query("DELETE FROM `qq3479015851_trade_log` WHERE addtime < '$oldtime7' and peerid = 0 ");
	
//	$db->query("DELETE FROM `qq3479015851_finance` WHERE addtime < '$oldtime7' ");
//	$db->query("DELETE FROM `qq3479015851_finance` WHERE userid = 111908 and addtime < '$oldtime1'  ");
}

	//清理冻结数据
function auto_dong()
{
	global $db;
	echo date("m-d H:i:s")." auto_dong \n";
	$oldtime = time()-5*60*60;//清理多少小时内的冻结数据
	$time = time();
	//$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '0' WHERE  status = 1 and num !=deal and addtime > $oldtime ;");
	
	$data = $db->query("select * from qq3479015851_trade  WHERE  status = 1 and num !=deal  and addtime > $oldtime order by id desc LIMIT 0,500 ;");
	foreach($data as $k=>$v){
		$cha = $v['num'] - $v['deal'];
		if($cha > 0.001){
			//$rs = M()->table('a_auto')->add(array('type' => 1, 'addtime' => $time, 'tid' => $v['id'], 'uid' => '111'));
			$db->query("INSERT INTO `a_auto` (`aid`, `type`, `addtime`, `uptime`, `tid`, `uid`) VALUES (NULL, '1', '$time', '0', '".$v['id']."', '111');");
			echo $v['id']."--";
		}
	}
	echo " \n";
	
}
	//清理交易小于0的数据
function auto_ck_jiaoyi()
{
	global $db;
	echo date("m-d H:i:s")." auto_ck_jiaoyi \n";
	$data = $db->query("select * from qq3479015851_trade  WHERE  status = 0 and num !=deal  ;");
	foreach($data as $k=>$v){
		$cha = $v['num'] - $v['deal'];
		if($cha < 0.001 ){
			$db->query("UPDATE  `qq3479015851_trade` SET  `status` =  '2' WHERE  id = '".$v['id']."' ;");
			echo $v['id']."--";
		}
		
	}
	echo " \n";
}

//测试挂单，撤单
function test_gua()
{
	echo "--".date("Y-m-d H:i:s")."\n";
	global $db;
	$mum = 0;
	$time = time();
	$sui = 0.01+mt_rand()/mt_getrandmax() * (0.99);//0-1随机数

			   //统计撤销挂单数量
				//$d_num = $db->query("select id from qq3479015851_trade  WHERE userid = 111908 and status = 0 and sort = 0 and `market` ='doge_cny';");
			  // 撤销挂单
			  // echo "--cedan--";
			  // foreach($d_num as $k=>$v){
					//$cedan = $db->query("INSERT INTO `a_auto` (`aid`, `type`, `addtime`, `uptime`, `tid`, `uid`) VALUES (NULL, '1', '$time', '0', '".$v['id']."', '111908');");
				   //echo $cedan."--";
			  // }
			   //echo "\n--buy--";
			   $m_num = 10;
			   for ($x=0; $x<$m_num; $x++) {
				   $cprimin = rand(1,5)*$sui;//价格
				   $cnums = rand(1000,20000)*$sui;//数量
					$mum = round($cnums * $cprimin, 8);
				   if($cprimin>0.001 && $cnums > 0.001){
						$rs = $db->insert('a_market')->cols(array('type' => 1, 'addtime' => $time, 'market' => "doge_cny", 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
					   
//						$url='http://www.cc1.com/trade/a6fd87sf68s7df68sdf6ds8f6d/paypassword/1/market/doge_cny/price/'.$cprimin.'/num/'.$cnums.'/type/1/';
//						$curl = curl_init();  
//						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
//						curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
//						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
//						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  
//						curl_setopt($curl, CURLOPT_URL, $url);  
//						$res = curl_exec($curl);  
//						curl_close($curl); 
					   //echo $url.' \r\n';
					   echo $x."--".$cprimin."--".$cnums."--";
				   }
			   }
			   echo "\n--sell--";
			   $m_num1 = 10;
			   for ($x=0; $x<$m_num1; $x++) {
				   $cprimin = rand(1,5)*$sui;//价格
				   $cnums = rand(1000,20000)*$sui;//数量
					$mum = round($cnums * $cprimin, 8);
				   if($cprimin>0.001  && $cnums > 0.001){
						$rs = $db->insert('a_market')->cols(array('type' => 2, 'addtime' => $time, 'market' => "doge_cny", 'num' => $cnums,'mum' => $mum, 'pri' => $cprimin, 'fee' => 0, 'userid' => 111908))->query();
//						$url='http://www.cc1.com/trade/a6fd87sf68s7df68sdf6ds8f6d/paypassword/1/market/doge_cny/price/'.$cprimin.'/num/'.$cnums.'/type/2/';
//						$curl = curl_init();  
//						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
//						curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
//						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
//						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  
//						curl_setopt($curl, CURLOPT_URL, $url);  
//						$res = curl_exec($curl);  
//						curl_close($curl); 
					   //echo $url.' \r\n';
					   echo $x."--".$cprimin."--".$cnums."--";
				   }

			   }
		   
		   echo "\n";
	
	
	
}


	//推送消息
function send_data()
{
	echo date("m-d H:i:s")." \n";
	$address = '127.0.0.1';
	$port = 7272; 
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	$result = socket_connect($socket, $address, $port);
	socket_close($socket); // 关闭socket连接
}


//K线图数据更新
function kline1()
	{
		global $db;
		echo "--kline--";
		$timearr = array(5,15, 30, 60, 360,1440);
		$room = array("tix_cny","cnut_cny","btc_cny","ltc_cny","eth_cny","xrp_cny","doge_cny","qtum_cny","bch_cny");
		//$room = array("cnut_cny");
		foreach($room as $k=>$market)
		{
			echo $market."--";
			foreach ($timearr as $k => $v) 
			{
				//$tradeJson = M('TradeJson')->where(array('market' => $market, 'type' => $v))->order('id desc')->find();
				@$tradeJson = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and type = '$v' order by id desc LIMIT 1;");
				@$tradeJson = $tradeJson[0];
				if ($tradeJson) {
					$addtime = $tradeJson['addtime'];
				}
				else {
					//$addtime = M('TradeLog')->where(array('market' => $market))->order('id asc')->getField('addtime');
					$addtime = $db->query("select addtime from qq3479015851_trade_log  WHERE market = '$market'  order by id desc LIMIT 1;");
					$addtime = $addtime[0]['addtime'];
				}
				if ($addtime) {
					//$youtradelog = M('TradeLog')->where('addtime >=' . $addtime . '  and market =\'' . $market . '\'')->sum('num');
					$youtradelog = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$addtime' ;");
					$youtradelog = $youtradelog[0]['sum'];
				}
				if ($youtradelog) {
					if ($v == 1) {
						$start_time = $addtime;
					}
					else {
						$start_time = mktime(date('H', $addtime), floor(date('i', $addtime) / $v) * $v, 0, date('m', $addtime), date('d', $addtime), date('Y', $addtime));
					}

					$x = 0;
					for (; $x <= 20; $x++) {
						$na = $start_time + (60 * $v * $x);
						$nb = $start_time + (60 * $v * ($x + 1));

						if (time() < $na) {
							break;
						}
						//$sum = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->sum('num');
						$sum = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' ;");
						$sum = $sum[0]['sum'];
						if ($sum) {
							$sta = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by id asc  LIMIT 1;");
							$sta = $sta[0]['price'];
							$max = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by price desc  LIMIT 1;");
							$max = $max[0]['price'];
							$min = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by price asc  LIMIT 1;");
							$min = $min[0]['price'];
							$end = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by id desc  LIMIT 1;");
							$end = $end[0]['price'];
							$d = array($na, $sum, $sta, $max, $min, $end);

							//$sta = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id asc')->getField('price');
							//$max = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->max('price');
							//$min = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->min('price');
							//$end = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id desc')->getField('price');
							//$d = array($na, $sum, $sta, $max, $min, $end);
							$tradeJson1 = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and addtime = '$na' and type = '$v'  LIMIT 1;");

							if ($tradeJson1) {
								$tradeJson1 = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and addtime = '$na' and type = '$v'  LIMIT 1;");
								$db->query("UPDATE  `qq3479015851_trade_json` SET  `data` =  '".json_encode($d)."' WHERE  `market` ='$market'  and   `addtime` ='$na'  and  `type` ='$v' ;");
								//M('TradeJson')->where(array('market' => $market, 'addtime' => $na, 'type' => $v))->save(array('data' => json_encode($d)));
							}
							else {
								$aa = $db->insert('qq3479015851_trade_json')->cols(array('market' => $market, 'data' => json_encode($d), 'addtime' => $na, 'type' => $v))->query();
							}
						}
						else {

							$db->insert('qq3479015851_trade_json')->cols(array('market' => $market, 'data' => '', 'addtime' => $na, 'type' => $v))->query();
						}
					}
				}
				echo $v."--";
			}
			echo "--\n";
		}
			echo "--\n";

	}

//K线图数据更新
function kline2()
	{
		global $db;
		echo "--kline--";
		$timearr = array(5,15, 30, 60, 360,1440);
		$room = array("esm_cny","btd_cny","ifc_cny","dash_cny","eos_cny","etc_cny","neo_cny","ada_cny","ae_cny","omg_cny","bat_cny","bts_cny","btm_cny");
		//$room = array("cnut_cny");
		foreach($room as $k=>$market)
		{
			echo $market."--";
			foreach ($timearr as $k => $v) 
			{
				//$tradeJson = M('TradeJson')->where(array('market' => $market, 'type' => $v))->order('id desc')->find();
				@$tradeJson = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and type = '$v' order by id desc LIMIT 1;");
				@$tradeJson = $tradeJson[0];
				if ($tradeJson) {
					$addtime = $tradeJson['addtime'];
				}
				else {
					//$addtime = M('TradeLog')->where(array('market' => $market))->order('id asc')->getField('addtime');
					$addtime = $db->query("select addtime from qq3479015851_trade_log  WHERE market = '$market'  order by id desc LIMIT 1;");
					$addtime = $addtime[0]['addtime'];
				}
				if ($addtime) {
					//$youtradelog = M('TradeLog')->where('addtime >=' . $addtime . '  and market =\'' . $market . '\'')->sum('num');
					$youtradelog = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$addtime' ;");
					$youtradelog = $youtradelog[0]['sum'];
				}
				if ($youtradelog) {
					if ($v == 1) {
						$start_time = $addtime;
					}
					else {
						$start_time = mktime(date('H', $addtime), floor(date('i', $addtime) / $v) * $v, 0, date('m', $addtime), date('d', $addtime), date('Y', $addtime));
					}

					$x = 0;
					for (; $x <= 20; $x++) {
						$na = $start_time + (60 * $v * $x);
						$nb = $start_time + (60 * $v * ($x + 1));

						if (time() < $na) {
							break;
						}
						//$sum = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->sum('num');
						$sum = $db->query("select sum(num) as sum from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' ;");
						$sum = $sum[0]['sum'];
						if ($sum) {
							$sta = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by id asc  LIMIT 1;");
							$sta = $sta[0]['price'];
							$max = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by price desc  LIMIT 1;");
							$max = $max[0]['price'];
							$min = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by price asc  LIMIT 1;");
							$min = $min[0]['price'];
							$end = $db->query("select price from qq3479015851_trade_log  WHERE market = '$market' and addtime >= '$na' and addtime < '$nb' order by id desc  LIMIT 1;");
							$end = $end[0]['price'];
							$d = array($na, $sum, $sta, $max, $min, $end);

							//$sta = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id asc')->getField('price');
							//$max = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->max('price');
							//$min = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->min('price');
							//$end = M('TradeLog')->where('addtime >=' . $na . ' and addtime <' . $nb . ' and market =\'' . $market . '\'')->order('id desc')->getField('price');
							//$d = array($na, $sum, $sta, $max, $min, $end);
							$tradeJson1 = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and addtime = '$na' and type = '$v'  LIMIT 1;");

							if ($tradeJson1) {
								$tradeJson1 = $db->query("select * from qq3479015851_trade_json  WHERE market = '$market' and addtime = '$na' and type = '$v'  LIMIT 1;");
								$db->query("UPDATE  `qq3479015851_trade_json` SET  `data` =  '".json_encode($d)."' WHERE  `market` ='$market'  and   `addtime` ='$na'  and  `type` ='$v' ;");
								//M('TradeJson')->where(array('market' => $market, 'addtime' => $na, 'type' => $v))->save(array('data' => json_encode($d)));
							}
							else {
								$aa = $db->insert('qq3479015851_trade_json')->cols(array('market' => $market, 'data' => json_encode($d), 'addtime' => $na, 'type' => $v))->query();
							}
						}
						else {

							$db->insert('qq3479015851_trade_json')->cols(array('market' => $market, 'data' => '', 'addtime' => $na, 'type' => $v))->query();
						}
					}
				}
				echo $v."--";
			}
			echo "--\n";
		}
			echo "--\n";

	}

   //钱包更新
function qianbao()
{
	$t1 = microtime(true);
	echo " \n";
   //钱包
	$url='http://www.bjs.bi/Queue9ce2472db8c3b3d94511365004ce8468/qianbaob8c3b3d94512472db8';
	$curl = curl_init();  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
	curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  
	curl_setopt($curl, CURLOPT_URL, $url);
	$res = curl_exec($curl);  
	curl_close($curl); 
   //echo $url.'\n';
   //钱包
  	$t2 = microtime(true);
	 echo date("m-d H:i:s")." btc time ".round($t2-$t1,3)." s \n";	
	
}
 
   //eth钱包更新
function qianbao_eth()
{
 	$t1 = microtime(true);
	echo " \n";
  //钱包
	$url='http://bi.bjs.bi/Queue9ce2472db8c3b3d94511365004ce8468/qianbaoethb8c3b3d94512472db8';
	$curl = curl_init();  
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
	curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  
	curl_setopt($curl, CURLOPT_URL, $url);  
	$res = curl_exec($curl);  
	curl_close($curl); 
  	$t2 = microtime(true);
	 echo date("m-d H:i:s")." eth time ".round($t2-$t1,3)." s \n";	
   //钱包
	
	
}
 

//定时发送短信
function send_sms()
{
	echo date("m-d H:i:s")."--send_sms--";
	require_once  'sendsms.php';//短信用
	$send = new send();
	$time = time();
	$oldtime = $time - 30*60;//不重复通知
	$rmb = 0.1;
	$pks = 0;
	$cnut = 0;
	$pksy = 20;//每次最大短信发送量
	global $db;
	$coin = $db->query("select id,name from qq3479015851_coin  order by id asc;");
	foreach($coin as $k=>$v){
		$coins[$v['id']]['name'] = $v['name'];
		$name = $v['name'].'_cny';
		@$psy = $db->query("select new_price from qq3479015851_market where name = '$name' and new_price > 0 ;");
		@$coins[$v['id']]['price'] = $psy[0]['new_price'];
		if($v['name']=="cnut") {
			$cnut = $psy[0]['new_price'];
		}
	}
	
	$data = $db->query("select * from qq3479015851_user_yujing  WHERE status = '1'  order by autotime asc LIMIT $pksy;");
	foreach($data as $k=>$v){
		$yid = $v['id'];
		$uid = $v['uid'];
		$bid = $v['bid'];
		@$b_coin = $coins[$bid]['name'];
		@$b_price = $coins[$bid]['price'];
		if(!$b_price) continue;
		$pris = round($rmb/$cnut,4);//cnut条数
		$pris = $pris ? $pris : 0;
		$yujing1 = $v['yujing1'];
		$yujing2 = $v['yujing2'];
		$yujing3 = $v['yujing3'];
		
		$yujing4 = $v['yujing4'];
		$yujing5 = $v['yujing5'];
		$yujing6 = $v['yujing6'];
		echo "--".$b_coin."--".$b_price.'--';
		
		if($b_price > $yujing3){
			$ji = 3;
			$jis = "上涨3级";
		}elseif($b_price < $yujing3 && $b_price > $yujing2 ){
			$ji = 2;
			$jis = "上涨2级";
		}elseif($b_price < $yujing2 && $b_price > $yujing1 ){
			$ji = 1;
			$jis = "上涨1级";
		}elseif($b_price < $yujing4 && $b_price > $yujing5 ){
			$ji = 4;
			$jis = "下跌1级";
		}elseif($b_price < $yujing5 && $b_price > $yujing6 ){
			$ji = 5;
			$jis = "下跌2级";
		}elseif( $b_price < $yujing6 ){
			$ji = 6;
			$jis = "下跌3级";
		}else{
			$ji = 0;
		}
		if($ji){
			
			$msg = "尊敬的C网用户，您的".$b_coin."触发您设置的".$jis."预警，请登录查看！";
			$isck = $db->query("select cnut from qq3479015851_user_coin  WHERE userid = '$uid' ;");
			$mos = $db->query("select moble from qq3479015851_user  WHERE id = '$uid' ;");
			$fone = $mos[0]['moble'];
			if($isck[0]['cnut'] > $pris){
				//查询手机
				if($pris) $db->query("UPDATE  `qq3479015851_user_coin` SET  `cnut` =  cnut - $pris  WHERE  userid = '$uid' ;");//扣费
				//查询上次发送短信时间
				@$issms = $db->query("select * from a_sms  WHERE userid = '$uid' and coin = '$bid' and  type ='$ji' and addtime > '$oldtime'  ;");
				if(!$issms){
					$db->insert('a_sms')->cols(array('userid' => $uid,'mobile' => $fone, 'coin' => $bid, 'pri' => $b_price, 'pris' => $pris,'addtime' => $time, 'sendtime' => $time, 'type' => $ji))->query();
					//调用短信
					if($fone && $msg){
			//echo json_encode($v);
			//echo "\n";
						$send->sendSMS($fone,$msg);
					}
					$pks++;
				}

			}else
			{
				//余额不足
				//暂停所有通知
				$db->query("UPDATE  `qq3479015851_user_yujing` SET  `status` =  2  WHERE  uid = '$uid' ;");
				$msg = "尊敬的C网用户，您的CUNT余额不足，预警功能自动关闭！";
					if($fone && $msg){
						$send->sendSMS($fone,$msg);
					}

				//查询上次发送短信时间
				@$issms = $db->query("select * from a_sms  WHERE userid = '$uid' and coin = '$bid' and  type ='$ji' and addtime > '$oldtime'  ;");
				if(!$issms){
					$db->insert('a_sms')->cols(array('userid' => $uid,'mobile' => $fone, 'coin' => $bid, 'pri' => $b_price, 'pris' => $pris,'addtime' => $time, 'sendtime' => 1, 'type' => $ji))->query();
				}
			}
			
		}
		$db->query("UPDATE  `qq3479015851_user_yujing` SET  `autotime` =  '$time' WHERE  id = '$yid'  ;");
	}
	echo $pks."--num--\n\n";
}

	//每天统计一次cnut余额
function cnut_count()
{
	global $db;
	echo "cnut_count star \n";
	$time = date("Ymd",time());
	$isck = $db->query("select * from a_cnut LIMIT 0,1 ;");
	if($isck[0]['date']!=$time){
		$db->query("TRUNCATE TABLE `a_cnut`");
		$data = $db->query("select cnut,userid from qq3479015851_user_coin  order by userid desc ;");
		foreach($data as $k=>$v){
			$db->query("INSERT INTO `a_cnut` (`userid`, `cnut`, `date`) VALUES ('".$v['userid']."', '".$v['cnut']."', '$time');");
			echo "-";
		}
	}
	echo "cnut_count end \n";
	
}


?>