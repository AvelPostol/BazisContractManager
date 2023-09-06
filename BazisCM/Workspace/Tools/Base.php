<?php
namespace BazisСM\Workspace\Tools;

class Base {

    public function writeLog($data) {
        $logFile = __DIR__.'/log_'.$data['meta'].time().'.txt';
        $formattedData = var_export($data['body'], true);
        file_put_contents($logFile, '<?php $array = ' . $formattedData . ';', FILE_APPEND);
    }
    
}