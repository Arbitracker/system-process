<?php
/**
 * systemProcess base class
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
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 * @version $Revision$
 */

namespace SystemProcess;

use \PHPUnit_Framework_TestCase;

use \SystemProcess\NonZeroExitCodeException;
use \SystemProcess\Argument\PathArgument;

/**
 * Main test class for the SystemProcess component.
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 * @version $Revision$
 * @group unittest
 */
class SystemProcessTest extends PHPUnit_Framework_TestCase
{
    protected static $win = false;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$win = ( strtoupper( substr( PHP_OS, 0, 3)) === 'WIN' );
    }

    public function testSimpleExecution()
    {
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( PHP_EOL, $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
    }

    public function testInvalidExecutable() 
    {       
        $process = new SystemProcess( __DIR__ . '/data' . '/not_existant_file' );
        $this->assertNotEquals( 0, $process->execute() );
        $this->assertEquals( "", $process->stdoutOutput );
        $this->assertNotSame( false, strpos( $process->stderrOutput, 'not_existant_file' ) );
    }

    public function testOneSimpleArgument() 
    {       
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( 'foobar' );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "foobar" . PHP_EOL, $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
    }
    
    public function testOneEscapedArgument() 
    {       
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( "foobar 42" );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "foobar 42" . PHP_EOL, $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
    }

    public function testTwoArguments() 
    {       
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( "foobar" )->argument( "42" );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "foobar 42" . PHP_EOL, $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
    }

    public function testStdoutOutputRedirection() 
    {       
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( "foobar" );
        $process->redirect( SystemProcess::STDOUT, SystemProcess::STDERR );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "", $process->stdoutOutput );
        $this->assertEquals( "foobar" . PHP_EOL, $process->stderrOutput );
    }

    public function testStdoutOutputRedirectionToFile() 
    {       
        $tmpfile = tempnam( sys_get_temp_dir(), "pbs" );
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( "foobar" );
        $process->redirect( SystemProcess::STDOUT, $tmpfile );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "", $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
        $this->assertEquals( "foobar" . PHP_EOL, file_get_contents( $tmpfile ) );
        unlink( $tmpfile );
    }

    public function testStdoutOutputRedirectionBeforeArgument() 
    {       
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->redirect( SystemProcess::STDOUT, SystemProcess::STDERR )
                ->argument( "foobar" );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "", $process->stdoutOutput );
        $this->assertEquals( "foobar" . PHP_EOL, $process->stderrOutput );
    }

    public function testStdoutOutputRedirectionToFileBeforeArgument() 
    {       
        $tmpfile = tempnam( sys_get_temp_dir(), "pbs" );
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->redirect( SystemProcess::STDOUT, $tmpfile )
                ->argument( "foobar" );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "", $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
        $this->assertEquals( "foobar" . PHP_EOL, file_get_contents( $tmpfile ) );
        unlink( $tmpfile );
    }

    public function testSimplePipe() 
    {
        $outputProcess = new SystemProcess( 'php ' . $this->getBinPath( 'cat' ) );
        $process       = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $process->argument( 'foobar' )
                ->pipe( $outputProcess );
        $this->assertEquals( 0, $process->execute() );
        $this->assertEquals( "foobar" . PHP_EOL, $process->stdoutOutput );
        $this->assertEquals( "", $process->stderrOutput );
    }

    /**
     * @return void
     * @expectedException \SystemProcess\RecursivePipeException
     */
    public function testRecursivePipe() 
    {
        $process = new SystemProcess( 'php' . $this->getBinPath( 'echo' ) );
        $process->pipe( $process );
    }

    public function testCustomEnvironment() 
    {
        if ( self::$win )
        {
            $this->markTestSkipped( 'Test skipped, because Windows does not support evaluation of environment variables.' );
        }

        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $this->assertEquals(
            0,
            $process->argument( '"${environment_test}"', true )
                    ->environment( 
                        array( 'environment_test' => 'foobar' )
                    )
                    ->execute()
       );
       $this->assertEquals( $process->stdoutOutput, "foobar" . PHP_EOL );
    }

    public function testCustomWorkingDirectory() 
    {
        $process = new SystemProcess(
            ( self::$win ? 'workingDirectoryTest.bat' : './workingDirectoryTest.sh' ) );
        $this->assertEquals(
            0,
            $process->workingDirectory( $this->getResourceDir() . '/data' )
                    ->execute()
        );
        $this->assertEquals( $process->stdoutOutput, "foobar" . PHP_EOL );
    }

    public function testAsyncExecution() 
    {
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $pipes = $process->argument( 'foobar' )
                         ->execute( true );
        $output = '';
        while( !feof( $pipes[1] ) ) 
        {
            $output .= fread( $pipes[1], 4096 );
        }
        $this->assertEquals( 0, $process->close() );
        $this->assertEquals( $output, "foobar" . PHP_EOL );
    }

    public function testWriteToStdin() 
    {
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'cat' ) );
        $pipes = $process->execute( true );
        fwrite( $pipes[0], "foobar" );
        fclose( $pipes[0] );
        $output = '';
        while( !feof( $pipes[1] ) ) 
        {
            $output .= fread( $pipes[1], 4096 );
        }
        $this->assertEquals( 0, $process->close() );
        $this->assertEquals( $output, "foobar" );
    }

    public function testCustomDescriptor() 
    {
        if ( self::$win )
        {
            $this->markTestSkipped( 'Test skipped, because Windows does not know custom file descriptors.' );
        }

        $process = new SystemProcess( $this->getDataPath( 'fileDescriptorTest' ) );
        $pipes = $process->descriptor( 4, SystemProcess::PIPE, 'r' )
                         ->descriptor( 5, SystemProcess::PIPE, 'w' )
                         ->execute( true );
        fwrite( $pipes[4], "foobar" );
        fclose( $pipes[4] );
        $output = '';
        while( true ) 
        {
            $output .= fread( $pipes[5], 4096 );
            if ( feof( $pipes[5] ) ) 
            {
                break;
            }
        }
        $this->assertEquals( $process->close(), 0 );
        $this->assertEquals( $output, 'foobar' );
    }

    public function testCustomDescriptorToFile() 
    {
        if ( self::$win )
        {
            $this->markTestSkipped( 'Test skipped, because Windows does not know custom file descriptors.' );
        }

        $tmpfile = tempnam( sys_get_temp_dir(), "pbs" );
        $process = new SystemProcess( $this->getDataPath( 'fileDescriptorTest' ) );
        $pipes = $process->descriptor( 4, SystemProcess::PIPE, 'r' )
             ->descriptor( 5, SystemProcess::FILE, $tmpfile, 'a' )
             ->execute( true );

        fwrite( $pipes[4], "foobar" );
        fclose( $pipes[4] );

        $this->assertEquals( 0, $process->close() );
        $this->assertEquals( 'foobar', file_get_contents( $tmpfile ) );
        unlink( $tmpfile );
    }

    public function testAsyncPipe() 
    {
        $grep = new SystemProcess( 'grep' );
        $grep->argument( '-v' )
             ->argument( 'baz' );

        $process = new SystemProcess( 'php ' . $this->getBinPath( 'echo' ) );
        $pipes = $process->argument( "foobar\nbaz" )
                         ->pipe( $grep )
                         ->execute( true );
        $output = '';
        while( !feof( $pipes[1] ) ) 
        {
            $output .= fread( $pipes[1], 4096 );
        }
        $this->assertEquals( $process->close(), 0 );
        $this->assertEquals( $output, "foobar" . PHP_EOL );
    }

    public function testSignal() 
    {
        $this->markTestSkipped( 'This test is broken, Jakob should fix this.' );

        if ( self::$win )
        {
            $this->markTestSkipped( 'Test skipped, because Windows signal handling is completely broken.' );
        }

        $process = new SystemProcess( $this->getDataPath( 'signalTest.php' ) );
        $pipes = $process->execute( true );
        $output = '';
        while( !feof( $pipes[1] ) ) 
        {
            $output .= fread( $pipes[1], 4096 );
            if ( $output === "ready" )
            {
                $output = '';
                $process->signal( SystemProcess::SIGUSR1 );
            }
        }

        $this->assertEquals( 0, $process->close() );
        $this->assertEquals( "SIGUSR1 recieved", $output );
    }

    public function testFluentInterface() 
    {
        // This process should not be executed. It just tests the fluent
        // interface pattern.
        $process = new SystemProcess( 'foobar' );
        $process->argument( '42' )
            ->pipe( new SystemProcess( 'baz' ) )
            ->redirect( SystemProcess::STDOUT, SystemProcess::STDERR )
            ->environment( array( 'foobar' => '42' ) )
            ->workingDirectory( __DIR__ . '/data' )
            ->descriptor( 4, SystemProcess::PIPE, 'r' )
            ->argument( '23' );
    }

    /**
     * @return void
     * @expectedException \SystemProcess\NonZeroExitCodeException
     */
    public function testNonZeroReturnCodeException() 
    {
        $process = new SystemProcess( 'php' );
        $process->nonZeroExitCodeException = true;
        $process->argument( '-r')->argument( 'exit( 1 );' );
        $process->execute();
    }

    /**
     * @return void
     * @expectedException \SystemProcess\NonZeroExitCodeException
     */
    public function testNonZeroReturnCodeExceptionStdout() 
    {
        $process = new SystemProcess( $this->getDataPath( 'nonZeroExitCodeOutputTest.' . ( self::$win ? 'bat' : 'sh' ) ) );
        $process->nonZeroExitCodeException = true;
        $process->argument( 'foobar' );
        $process->execute();
    }

    /**
     * @return void
     * @expectedException \SystemProcess\NonZeroExitCodeException
     */
    public function testNonZeroReturnCodeExceptionStderr() 
    {
        $process = new SystemProcess( $this->getDataPath( 'nonZeroExitCodeOutputTest.' . ( self::$win ? 'bat' : 'sh' ) ) );
        $process->nonZeroExitCodeException = true;
        $process->argument( 'foobar' );
        $process->redirect( SystemProcess::STDOUT, SystemProcess::STDERR );
        $process->execute();
    }

    public function testToStringMagicMethod() 
    {
        $process = new SystemProcess( 'someCommand' );
        $process->argument( 'someArgument' )
                ->argument( '42' )
                ->redirect( SystemProcess::STDOUT, SystemProcess::STDERR );

        if ( self::$win )
        {
            $this->assertEquals(
                'someCommand "someArgument" "42" 1>&2', (string)$process,
                'Magic __toString conversion did not return expected result.'
            );
        }
        else
        {
            $this->assertEquals(
                "someCommand 'someArgument' '42' 1>&2", (string)$process,
                'Magic __toString conversion did not return expected result.'
            );
        }
    }

    public function testNonZeroExitCodeExceptionStdErrTruncate() 
    {
        $err = array();
        for( $i = 0; $i <= 100; ++$i ) 
        {
            $err[] = (string)$i;
        }
        $e = new NonZeroExitCodeException(
            1,
            'foobar',
            implode( PHP_EOL, $err ),
            'command'
        );

        $this->assertEquals( 
            52, count( explode( PHP_EOL, $e->getMessage() ) ),
            "NonZeroExitCodeException did not truncate stderr correctly"
        );
    }

    public function testNonZeroExitCodeExceptionStdErrNoTruncate() 
    {
        $err = array();
        for( $i = 0; $i <= 49; ++$i ) 
        {
            $err[] = (string)$i;
        }
        $e = new NonZeroExitCodeException(
            1,
            'foobar',
            implode( PHP_EOL, $err ),
            'command'
        );

        $this->assertEquals( 
            51, count( explode( PHP_EOL, $e->getMessage() ) ),
            "NonZeroExitCodeException truncated a stderr message to small for trucating"
        );
    }

    public function testPathArgument()
    {
        $process = new SystemProcess( 'php ' . $this->getBinPath( 'cat' ) );
        $process->argument( new PathArgument( $this->getDataPath( 'workingDirectoryTest.sh' ) ) );
        $process->execute();

        $this->assertEquals(
            file_get_contents( $this->getDataPath( 'workingDirectoryTest.sh' ) ),
            $process->stdoutOutput
        );
    }

    /**
     * Returns the real path for the given binary.
     *
     * @param string $name
     * @return string
     */
    private function getBinPath( $name )
    {
        return realpath( $this->getResourceDir() . '/bin/' . $name );
    }

    /**
     * Returns the real path for the given data file.
     *
     * @param string $name
     * @return string
     */
    private function getDataPath( $name )
    {
        return realpath( $this->getResourceDir() . '/data/' . $name );
    }

    /**
     * Returns the directory with the test resources.
     *
     * @return string
     */
    private function getResourceDir()
    {
        return realpath( __DIR__ . '/../../resources/' );
    }
}
