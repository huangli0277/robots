<?php 
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ .'/../mysql/src/Connection.php';
require_once __DIR__ .'/data/caiji.php';//业务主逻辑
global $db;
$db = new Workerman\MySQL\Connection('192.168.0.187', '3306', 'root', '123456', 'wanghong'); 
$task = new Worker();
$task->name = 'chengjiao';
// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 1;
$task->onWorkerStart = function($task)
{
	//成交
    Timer::add(1, function()
    {
		chengjiao();
    });
};


// 运行worker
Worker::runAll();
