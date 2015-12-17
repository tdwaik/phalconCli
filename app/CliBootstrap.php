<?php
namespace App\Cli;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @date December 3, 2015
 *
 */

use Phalcon\Config as PhConfig;
use Phalcon\CLI\Console as ConsoleApp;
use Phalcon\Logger\Adapter\File as PhLog;
use Phalcon\Security as PhSecurity;
use \Libs\AMQ as AMQ;


/**
 * Class CliBootstrap
 * @package App\Cli
 */
class CliBootstrap {

    protected $oDI;

    /**
     * @var
     */
    protected $loaders;

    /**
     * Constructor
     *
     * @param $oDI
     */
    public function __construct($oDI) {
        $this->oDI = $oDI;
        $this->loaders = array(
            'config',
            'log',
            'redis',
            'ActiveMQ'
        );
    }

    /**
     * @param $aOptions
     * @return mixed|void
     * @throws \Exception
     */
    public function run($aOptions) {
        try {

            foreach ($this->loaders as $service) {
                $function = 'init' . ucfirst($service);
                $this->$function($aOptions);
            }

            // Create a console application
            $console = new ConsoleApp();
            $console->setDI($this->oDI);

            return $console;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Initializes the config. Reads it from its location and
     * stores it in the Di container for easier access
     * @param array $aOptions
     */
    protected function initConfig($aOptions = array()) {
        $oConfig = new PhConfig();
        $aSharedConfig = require(ROOT_PATH . '/config/config.php');
        $oConfig->merge(new PhConfig($aSharedConfig));
        $this->oDI->setShared('config', $oConfig);
    }

    /**
     * @param array $aOptions
     */
    protected function initLog($aOptions = array()) {

        $config = $this->oDI->get('config');
        $this->oDI->setShared('log', function () use ($config) {
            return new PhLog($config->app->logDir . $config->app->log);
        });
    }

    protected function initRedis($aOptions = array()) {
        $di = $this->oDI;
        $di->setShared('redis', function () use ($di) {
            $redisConfig = $di->get('hitmeisterConfig')->redis->main;
            $redis = new \Redis;
            $redis->connect($redisConfig->host, $redisConfig->port, $redisConfig->timeout);
            if (!$isConnected) {
                $di->get('log')->error("Failed to connect to redis host {$redisConfig->host}.");
            }
            $redis->setOption(\Redis::OPT_PREFIX, 'SC:');
            return $redis;
        });
    }

    protected function initActiveMQ($sOptions = array()) {
        $di = $this->oDI;
        print_r($di->activeMQ);
        $di->setShared('activeMQ', function () use ($di) {
            $activeMQConfig = $di->activeMQ;
            try {
                $activeMQ = new AMQ\ActiveMQ();
                $activeMQ->schema($activeMQConfig->scheme)
                    ->host($activeMQConfig->host)
                    ->port($activeMQConfig->port)
                    ->user($activeMQConfig->user)
                    ->password($activeMQConfig->password)
                    ->connect()
                    ->persistent('true');
                return $activeMQ;
            } catch (\Exception $e) {
                $di->get('log')->error(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
            }
        });
    }

}