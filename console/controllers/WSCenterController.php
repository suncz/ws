<?php

namespace console\controllers;
defined('YII_CONSOLE_PATH') or define('YII_CONSOLE_PATH',realpath( __DIR__.'/../'));

use Symfony\Component\Console\Helper\Table;
use yii\console\Controller;

use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\log\Logger;
use Yii;

class WSCenterController extends Controller
{
    /**
     * 实例化服务
     *
     * @var
     */
    public $server;

    /**
     * 监听地址
     *
     * @var string
     */
    public $host = '0.0.0.0';

    /**
     * 监听端口
     *
     * @var string
     */
    public $port = 9502;

    /**
     * 运行的模式
     *
     * SWOOLE_PROCESS 多进程模式（默认）
     * SWOOLE_BASE 基本模式
     * @var
     */
    public $mode = SWOOLE_PROCESS;

    /**
     * @var
     */
    public $socketType = SWOOLE_SOCK_TCP;

    /**
     * 长连接方式
     *
     * @var string
     */
    public $type = 'ws';

    /**
     * swoole 配置
     *
     * @var array
     */
    public $config = [
        'daemonize' => false, // 守护进程执行
        'task_worker_num' => 4,//task进程的数量
        'ssl_cert_file' => '',
        'ssl_key_file' => '',
        'pid_file' => '',
        'buffer_output_size' => 2 * 1024 * 1024, //配置发送输出缓存区内存尺寸
        'heartbeat_check_interval' => 60,// 心跳检测秒数
        'heartbeat_idle_time' => 600,// 检查最近一次发送数据的时间和当前时间的差，大于则强行关闭
    ];

    /**
     * 启动
     *
     * @throws \yii\base\Exception
     */
    public function actionStart()
    {
        if ($this->getPid() !== false) {
            $this->stderr("服务已经启动...");
            exit(1);
        }
        $this->stdout("服务正在运行,监听 {$this->config['host']}:{$this->config['port']}" . PHP_EOL);
        $iMServer = new $this->server($this->config);
        $iMServer->templateMethod();
    }

    /**
     * 关闭进程
     */
    public function actionStop()
    {
        $this->sendSignal(SIGTERM);
        $this->stdout("服务已经停止, 停止监听 {$this->config['host']}:{$this->config['port']}" . PHP_EOL);
    }

    /**
     * 重启进程
     *
     * @throws \yii\base\Exception
     */
    public function actionRestart()
    {
        $this->sendSignal(SIGTERM);
        $time = 0;
        while (posix_getpgid($this->getPid()) && $time <= 10) {
            usleep(100000);
            $time++;
        }
        if ($time > 100) {
            $this->stderr("服务停止超时..." . PHP_EOL);
            exit(1);
        }

        if ($this->getPid() === false) {
            $this->stdout("服务重启成功..." . PHP_EOL);
        } else {
            $this->stderr("服务停止错误, 请手动处理杀死进程..." . PHP_EOL);
        }

        $this->actionStart();
    }


    /**
     * 平滑重启 worker tasker
     */
    public function actionReload()
    {
        $this->sendSignal(SIGUSR1);
    }

    /**
     * 平滑重启 tasker
     * @author: sunny <sunny@guojiang.tv>
     * @Date: 2019/4/19 18:13
     */
    public function actionReloadTask()
    {
        $this->sendSignal(SIGUSR2);
    }

    /**
     * 发送信号
     *
     * @param $sig
     */
    private function sendSignal($sig)
    {
        if ($pid = $this->getPid()) {
            posix_kill($pid, $sig);
        } else {
            $this->stdout("服务未运行..." . PHP_EOL);
            exit(1);
        }
    }

    /**
     * 获取pid进程
     *
     * @return bool|string
     */
    private function getPid()
    {
       $pidCmd="ps aux|grep php |grep 'ws'|grep  'master' |awk '{print $2}'";

        exec($pidCmd,$out);

        if(count($out)<2){
            return false;
        }
        return $out[0];
    }

}