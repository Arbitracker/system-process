#!/usr/bin/env php
<?php
declare( ticks = 1 );

function signal_handler( $signum ) 
{
    file_put_contents( '/tmp/signal.log', date('H:i:s') . ' ' . $signum, FILE_APPEND );
    if ( $signum === SIGUSR1 ) 
    {
        echo "SIGUSR1 recieved";
        exit( 0 );
    }
}

pcntl_signal( SIGUSR1, 'signal_handler' );

echo "ready";

// Wait the maximum of 2 second
sleep( 2 );
