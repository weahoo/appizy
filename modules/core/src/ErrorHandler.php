<?php

namespace Appizy\Core;

class ErrorHandler
{
    static function handle($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        switch ($errno) {
            case E_USER_ERROR:
                echo "Aborting due to fatal error : $errstr \n";
                exit(1);
                break;

            case E_USER_WARNING:
                echo "WARNING: $errstr \n";
                break;

            case E_USER_NOTICE:
                echo "$errstr \n";
                break;

            default:
                echo "Unknown error: $errstr<br />\n";
                break;
        }

        /* Don't execute PHP internal error handler */
        return true;
    }

    static function fatalHandler() {
        $error = error_get_last();

        if( $error['type'] === E_ERROR) {
            echo "FATAL ERROR: we are really sorry for the inconvenience. Please report us the problem, sending if 
            possible the spreadsheet you are trying to convert.\n";
        }
    }
}
