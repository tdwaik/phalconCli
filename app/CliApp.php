<?php
namespace PhalconCli\Cli;
/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @date December 3, 2015
 *
 */

/**
 * Class CliApp
 * @package PhalconCli\Cli
 */
class CliApp {

    protected $oDI;

    /**
     * @param $oDI
     */
    private function __construct($oDI) {
         $this->oDI = $oDI ;
    }

    /**
     * singleton insurance
     */
    private function __clone() {

    }

    /**
     * sets context IoC container
     * @param type $oDI
     */
    public static function setDI($oDI) {
        self::getInstance()->oDI = $oDI;
    }

    /**
     * Gets context IoC container
     * @return type
     */
    public static function getDI() {
        return self::getInstance()->oDI;
    }

    /**
     * @param type $DI
     * @param array $options
     * @return mixed|type|void
     * @throws \Exception
     */
    public static function run($DI, $options = array()) {
        $cliBootstrap = new CliBootstrap($DI);
        return $cliBootstrap->run($options);
    }

}