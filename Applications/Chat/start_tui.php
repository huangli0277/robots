﻿<?php 
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ .'/../mysql/src/Connection.php';
require_once __DIR__ .'/data/caiji.php';//业务主逻辑
global $db;
$db = new Workerman\MySQL\Connection('192.168.0.187', '3306', 'root', '123456', 'wanghong'); 
$task = new Worker();
$task->name = 'tui';
// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 3;
$task->onWorkerStart = function($task)
{

	//定时推送
    Timer::add(4, function()
    {
		send_data();
    });
	
	
	
};


// 运行worker
Worker::runAll();
