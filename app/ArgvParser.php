<?php
namespace App\Cli;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @since December 9, 2015
 *
 */

/**
 * Class ArgvParser
 * @package App\Cli
 */
class ArgvParser {

    /**
     * single character (`-k` or `-k value` or `-kvalue` or `-abc cvalue`)
     * long character (`--key` or `--key=value` or `--key value`)
     * @param $argv
     * @param bool $skipFileName
     * @return array
     */
    public static function parseArgs($argv, $skipFileName = true) {

        $argCount = count($argv);
        $parsedArgv = array();

        // file name
        if(!$skipFileName) {
            $parsedArgv['fileName'] = $argv[0];
        }

        for($i = 1; $i < $argCount; $i++) {

            $arg = $argv[$i];
            $nextArg = ($i < $argCount - 1)? $argv[$i + 1] : null;

            // long character `--`
            if(preg_match('/^--\w+/', $arg)) {

                // --key=value
                if(strpos($arg, '=') > 0) {
                    $exp = explode('=', $arg);
                    $key = str_replace('--', '', $exp[0]);
                    $parsedArgv[$key] = $exp[1];
                }

                // --key
                elseif($nextArg === null || preg_match('/^-+\w+/', $nextArg)) {
                    $key = str_replace('--', '', $arg);
                    $parsedArgv[$key] = true;

                }

                // --key value
                else {
                    $key = str_replace('--', '', $arg);
                    $parsedArgv[$key] = $nextArg;
                    $i++;
                }

            }

            // single character `-`
            elseif(preg_match('/^-\w+$/', $arg)) {

                $arg = str_replace('-', '', $arg);

                // -k, -kvalue
                if($nextArg === null || preg_match('/^-+\w+/', $nextArg)) {

                    // -k
                    if(preg_match('/^\w$/', $arg)) {
                        $parsedArgv[$arg] = true;
                    }

                    // -kvalue
                    else {
                        $key = substr($arg, 0, 1);
                        $value = substr($arg, 1);
                        $parsedArgv[$key] = $value;
                    }
                }

                // -k value, -abc cvalue
                else {

                    // -k value
                    if(preg_match('/^\w$/', $arg)) {
                        $parsedArgv[$arg] = $nextArg;
                        $i++;
                    }

                    // -abc cvalue
                    elseif(preg_match('/^\w+$/', $arg)) {
                        $keys = str_split($arg);
                        $keysCount = count($keys);

                        for($k = 0; $k < $keysCount; $k++) {
                            if($k == $keysCount - 1) {
                                $parsedArgv[$keys[$k]] = $nextArg;
                            }else {
                                $parsedArgv[$keys[$k]] = true;
                            }
                        }
                        $i++;
                    }
                }

            }

        }

        return $parsedArgv;
    }
}