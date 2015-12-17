<?php
namespace PhalconCli\Cli;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @date December 3, 2015
 *
 */

/**
 * Class CliTask
 * @package PhalconCli\Cli
 */
abstract class CliTask extends \Phalcon\CLI\Task {

    /**
     * @var array
     */
    private $argsMap = array();

    /**
     * @var array
     */
    protected $args = array();

    /**
     * empty function init()
     * this should be overridden in task class if args are needed
     */
    public function __init() {}

    public function printHelp() {
        $helpMessage = '';

        foreach($this->argsMap as $long => $arg) {
            $helpMessage .= '[--' . $long;
            $helpMessage .= (($arg['single'] !== null)? ', -' . $arg['single'] : '') . "]\t";
            $helpMessage .= ($arg['required'] == true? 'Required' : '') . "\t";
            $helpMessage .= $arg['default'] . "\t";
            $helpMessage .= $arg['help'];
            $helpMessage .= PHP_EOL;
        }

        echo $helpMessage;
    }

    /**
     * @param $args
     */
    public function runAction($args) {
        $args = (array) $args;
        $mappedArgs = $this->mapArgs($args);
        $missingArgs = $this->validateArgs($mappedArgs);
        if(!empty($missingArgs)) {
            echo 'Error: missing args [' . implode(', ', $missingArgs) . ']' . PHP_EOL;
            return;
        }
        array_walk_recursive($args, 'mysql_real_escape_string');
        $this->args = $mappedArgs;
        $this->run();
    }

    /**
     * @param $longChar
     * @param $singleChar
     * @param $helpMessage
     * @param bool $required
     * @param null $defaultValue
     */
    protected function registerArg($longChar, $singleChar, $helpMessage, $required = false, $defaultValue = null) {
        $this->argsMap[$longChar] = array(
            'single'    => $singleChar,
            'help'      => $helpMessage,
            'required'  => $required,
            'default'   => $defaultValue
        );
    }

    /**
     * @param $mappedArgs
     * @return array
     */
    private function validateArgs($mappedArgs) {
        $missingArgs = array();

        foreach($this->argsMap as $long => $arg) {
            if($arg['required'] == true && !array_key_exists($long, $mappedArgs)) {
                $missingArgs[] = $long;
            }
        }

        return $missingArgs;
    }

    /**
     * @param $args
     * @return array
     */
    private function mapArgs($args) {
        $mappedArgs = array();

        foreach($this->argsMap as $long => $arg) {
            if(array_key_exists($long, $args)) {
                $mappedArgs[$long] = $args[$long];
            }elseif(array_key_exists($arg['single'], $args)) {
                $mappedArgs[$long] = $args[$arg['single']];
            }elseif($arg['default'] !== null) {
                $mappedArgs[$long] = $arg['default'];
            }
        }

        return $mappedArgs;
    }
}