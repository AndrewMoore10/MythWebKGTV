<?php
/**
 * view and manipulate recorded programs.
 *
 * @license     GPL
 *
 * @package     MythWeb
 * @subpackage  TV
 *
/**/

// Load the sorting routines
    require_once 'includes/sorting.php';
    
    if( $_POST['channel'] || $_GET['channel']){
        if(!$_POST['channel']) $_POST['channel']  = $_GET['channel'];
        //run change channel script
        $address = 'localhost';
        $service_port = 61910;
        
        /* Create a TCP/IP socket. */
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "OK.\n";
        }

        echo "Attempting to connect to '$address' on port '$service_port'...";
        $result = socket_connect($socket, $address, $service_port);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }
        $in = "CHAN-". $_POST['channel'];
        socket_write($socket, $in, strlen($in));

        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";
        
        //sleep
        sleep(5);
        //refesh
        redirect_browser(root_url.'tv/live');
    }
    
    

// Load the class for this page
    require_once tmpl_dir.'live.php';

// Exit
    exit;
