<?php
namespace PhalconCli\Cli;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @since December 7, 2015
 *
 */

/**
 * Class ProcessLock
 * @package PhalconCli\Cli
 */
class ProcessLock {

    /**
     * @var string
     */
    private $lockPath;

    /**
     * @param $lockPath
     * @throws \Exception
     */
    public function __construct($lockPath) {
        $this->setLockPath($lockPath);
    }

    /**
     * @return string
     */
    public function getLockPath() {
        return $this->lockPath;
    }

    /**man
     * @param $lockPath
     * @throws \Exception
     */
    public function setLockPath($lockPath) {
        if(is_writable($lockPath)) {
            $this->lockPath = realpath($lockPath) . '/';
        }else {
            throw new \Exception("Error: LockPath '$lockPath' not writable !!");
        }
    }

    /**
     * @param $processName
     * @param array $params
     * @return bool
     */
    public function isLocked($processName, $params = array()) {
        return file_exists($this->getFileName($processName, $params));
    }

    /**
     * @param $processName
     * @param array $params
     * @return bool|int
     */
    public function lock($processName, $params = array()) {
        if($this->isLocked($processName, $params)) {
            return false;
        }

        return file_put_contents($this->getFileName($processName, $params), json_encode($params));
    }

    /**
     * @param $processName
     * @param array $params
     * @return bool
     */
    public function unlock($processName, $params = array()) {
        if(!$this->isLocked($processName, $params)) {
            return false;
        }

        return unlink($this->getFileName($processName, $params));
    }

    /**
     * @param $processName
     * @param array $params
     * @return string
     */
    private function getFileName($processName, $params = array()) {
        $md5params = '';
        if(!empty($params)) {
            $md5params = md5(json_encode($params));
        }
        return $this->lockPath . $processName  . $md5params . '.lock';
    }

}