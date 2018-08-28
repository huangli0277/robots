<?php 
use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ .'/../mysql/src/Connection.php';
require_once __DIR__ .'/data/caiji.php';//业务主逻辑
global $db;
$db = new Workerman\MySQL\Connection('192.168.0.187', '3306', 'root', '123456', 'wanghong'); 
$task = new Worker();
$task->name = 'caiji';
// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 2;
$task->onWorkerStart = function($task)
{
	//清理自动数据
    Timer::add(511, function()
    {
		auto_chengjiao();
    });
	//清理冻结数据
    Timer::add(14, function()
    {
		auto_dong();
    });
	//清理j交易数据
    Timer::add(33, function()
    {
		auto_jiaoyi();
    });
	//清理交易小于0的数据
    Timer::add(55, function()
    {
		auto_ck_jiaoyi();
    });
	//每天统计一次cnut余额
    Timer::add(654, function()
    {
		cnut_count();
    });

	
};


// 运行worker
Worker::runAll();
