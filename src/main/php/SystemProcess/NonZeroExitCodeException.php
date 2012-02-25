<?php
/**
 * systemProcess argument base class
 *
 * This file is part of systemProcess.
 *
 * systemProcess is free software; you can redistribute it and/or modify it
 * under the terms of the Lesser GNU General Public License as published by the
 * Free Software Foundation; version 3 of the License.
 *
 * systemProcess is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the Lesser GNU General Public License
 * for more details.
 *
 * You should have received a copy of the Lesser GNU General Public License
 * along with systemProcess; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace SystemProcess;

/**
 * Exception thrown if an executed application returns a non zero exit code 
 *
 * @version $Revision$
 * @license LGPLv3
 */
class NonZeroExitCodeException extends \Exception
{
    public $exitCode;
    public $stdoutOutput;
    public $stderrOutput;
    public $command;

    /**
     * Instantiates a new exception instance.
     *
     * @param integer $exitCode
     * @param string $stdoutOutput
     * @param string $stderrOutput
     * @param string $command
     */
    public function __construct( $exitCode, $stdoutOutput, $stderrOutput, $command ) 
    {
        // Generate a useful error message including the stderr output cutoff
        // after 50 lines max.
        $truncatedStderrOutput = implode( PHP_EOL, array_slice( ( $exploded = explode( PHP_EOL, $stderrOutput ) ), 0, 50 ) )
                               . ( 
                                   ( count( $exploded ) > 50  )
                                 ? ( PHP_EOL . "... truncated after 50 lines ..." )
                                 : ( "" )
                               );

        parent::__construct( 
            'During the execution of "' . $command . '" a non zero exit code (' . $exitCode . ') has been returned:' . PHP_EOL . $truncatedStderrOutput
        );

        $this->exitCode = $exitCode;
        $this->stdoutOutput = $stdoutOutput;
        $this->stderrOutput = $stderrOutput;
        $this->command = $command;
    }
}
