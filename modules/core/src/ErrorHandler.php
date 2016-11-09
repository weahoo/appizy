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
                echo "$errstr \n";
                echo "Aborting due to fatal error :/";
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
}
