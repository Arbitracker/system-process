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
    public $exitCode;
    public $stdoutOutput;
    public $stderrOutput;
    public $command;

    public function __construct( $exitCode, $stdoutOutput, $stderrOutput, $command ) 
    {
        parent::__construct( 'During the execution of "' . $command . '" a non zero exit code (' . $exitCode . ') has been returned.' );
        $this->exitCode = $exitCode;
        $this->stdoutOutput = $stdoutOutput;
        $this->stderrOutput = $stderrOutput;
        $this->command = $command;
    }
}

?>
