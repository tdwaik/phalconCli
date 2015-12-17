<?php
namespace App\Cli;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @date December 3, 2015
 *
 */

use \Phalcon\DI\FactoryDefault\CLI as CliDI;

error_reporting(E_ALL);

if(php_sapi_name() !== 'cli') {
    die('Run only in cli mode' .PHP_EOL);
}

define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

require_once(ROOT_PATH . '/app/ArgvParser.php');

// Process the console arguments
$options = ArgvParser::parseArgs($argv);
$task = array_key_exists(1, $argv)? $argv[1] : null;
$force = array_key_exists('force', $options) || array_key_exists('f', $options)? true : false;

$arguments = array();
$arguments['task'] = ($task !== null)? 'App\\Cli\\Tasks\\' . $task : null;
$arguments['action'] = 'run';
$arguments['params'] = (object) $options;

if($arguments['task'] == null) {
    die('Error: Please specify task' . PHP_EOL);
}

if(array_key_exists('help', $options) || array_key_exists('h', $options)) {
    require_once(ROOT_PATH . '/app/CliTask.php');
    require_once(ROOT_PATH . '/app/Tasks/'. $task .'Task.php');

    $className = $arguments['task'] . 'Task';
    $job = new $className();
    $job->printHelp();
    exit;
}

require_once(ROOT_PATH . '/app/ProcessLock.php');

try {
    $processLock = new ProcessLock('/tmp/');
}catch(\Exception $e) {
    die($e->getMessage() . PHP_EOL);
}

if(!$force) {
    set_error_handler(function() use ($task, $processLock, $options) {
        $processLock->unlock($task, $options);
        return true;
    });
}

register_shutdown_function(function() use ($task, $processLock, $options) {
    $processLock->unlock($task, $options);
    return true;
});

try {
    // Lock Process
    if(!$force) {
        if ($processLock->isLocked($task)) {
            die('Process ' . $task . ' is locked' . PHP_EOL);
        }
        $processLock->lock($task, $options);
    }

    require_once(ROOT_PATH . '/vendor/autoload.php');

    // Run Console App
    $DI = new CliDI();
    $console = CliApp::run($DI);
    $console->handle($arguments);

    if(!$force) {
        $processLock->unlock($task, $options);
    }

}catch(\Exception $e) {
    if(!$force) {
        $processLock->unlock($task, $options);
    }

    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(255);
}






