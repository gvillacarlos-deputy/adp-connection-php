<?php

/*
Copyright © 2015-2016 ADP, LLC.

Licensed under the Apache License, Version 2.0 (the “License”);
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an “AS IS” BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
express or implied.  See the License for the specific language
governing permissions and limitations under the License.
*/

/**********************************************************************
*
*  makeclient.php
*
*  This script will create a client to demonstrate the various api
*  modules that are installed.
*
*     USAGE:
*
*			php makeclient.php
*
***********************************************************************/


error_reporting(E_ALL);

// Set up folders
$filedir = getcwd() . "/";
$apidir = realpath($filedir . "../../") . "/";
$rootdir = realpath($apidir . "../") . "/";

$clientdir = $rootdir . "client/";

// check to see if $clientdir exists.  If not, create it.

echo "\n\n\n";
echo "Checking for existing client folder...\n";

if (!file_exists($clientdir)) {
	echo "Creating client folder...\n";
    mkdir($clientdir, 0777, true);
}

// Ok, client folder exists.  Get list of all apis install in apilib

$apiarray = glob($apidir . "*");

// have the list, now enumerate through it

foreach ($apiarray as $apiloc) {

	$apiname = basename($apiloc);

	$from 	= $apiloc . "/client/";
	$to 	= $clientdir;

	echo "Moving api:  $apiname...\n";
	recurse_copy($from, $to);
	//echo "Copy from:  '$from' to '$to'\n\n";

}

echo "\n";
echo "Complete.\n\n\n";

echo "Cut and paste the following two lines to start the server:\n\n";

echo "cd $clientdir\n";

echo "php -S 127.0.0.1:8889\n\n";

exit();

//*****************************************************************************

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

//*****************************************************************************

?>
