<?php

/**
 * Exception thrown if an executed application returns a non zero exit code 
 * 
 * @version //autogen//
 * @copyright Copyright (C) 2008 Jakob Westhoff. All rights reserved.
 * @author Jakob Westhoff <jakob@php.net> 
 * @license LGPLv3
 */
class pbsSystemProcessNonZeroExitCodeException extends Exception 
{
    public $command;
    public $exitCode;

    public function __construct( $command, $exitCode ) 
    {
        parent::__construct( 'During the execution of "' . $command . '" a non zero exit code (' . $exitCode . ' ) has been returned.' );
        $this->command = $command;
        $this->exitCode = $exitCode;
    }
}

?>
