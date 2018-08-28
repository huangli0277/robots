<?php 
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ .'/../mysql/src/Connection.php';
require_once __DIR__ .'/data/caiji.php';//业务主逻辑
global $db;
$db = new Workerman\MySQL\Connection('192.168.0.187', '3306', 'root', '123456', 'wanghong'); 
$task = new Worker();
$task->name = 'qianbaos';
// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 2;
$task->onWorkerStart = function($task)
{

	//钱包更新
    Timer::add(51, function()
    {
		qianbao();
    });
	//eth钱包更新
    Timer::add(26, function()
    {
		qianbao_eth();
    });

	
};


// 运行worker
Worker::runAll();
