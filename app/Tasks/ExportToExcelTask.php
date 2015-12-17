<?php
namespace App\Cli\Tasks;

/**
 * @author Thaer AlDwaik <t_dwaik@hotmail.com>
 * @date December 3, 2015
 *
 */

use App\Cli\CliTask as CliTask;

/**
 * Class ExportToExcelTask
 * @package App\Cli\Tasks
 */
class ExportToExcelTask extends CliTask {

    public function __init() {
        $this->registerArg('sellerId', 'i', 'Seller ID', true);
        $this->registerArg('sellerStatus', 's', 'Seller ID');
    }

    public function run() {

        try {
            // get ActiveMQ Frame
            $frame = $this->activeMQ->subscribe('/Events/ExportToExcel')->getMessage();
            $this->activeMQ->unsubscribe('/Events/ExportToExcel');

            $data = json_decode($frame->body);
            if(empty($data)) {
                echo 'No Frame' . PHP_EOL;
            }

            // TODO perform export to excel

        }catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}

