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

// Part 2 of authorization code authentication

// Set some handling up.
session_start();
error_reporting(E_ALL);

$webroot = "../";

// Load required files.
require ($webroot . "config.php");
require ($libroot . "connection/adpapiConnection.class.php");

// Check to see if there is an error

if (isset($_GET['error'])) {

	$error_string = filter_var($_GET['error'], FILTER_SANITIZE_STRING);

	include($webroot . "inc/header.php");
	echo "<h1>Gateway Error</h1>";
	echo "<div class='alert alert-danger'>\n";
	echo "The error returned is: " . $error_string;
	echo "</div>\n";
	exit();

}

// Get the authorization code, if available.  BE SURE TO SANITIZE!!!  For this demo, we will not.
$retcode = $_GET['code'];

$retcode = filter_var($_GET['code'], FILTER_SANITIZE_STRING);

$logger->write("Back from authentication.  Received code.");

// restore session object from session.  Handle sessions the way you see fit.
$adpConn = unserialize($_SESSION['adpConn']);

//-----------------------------------------------------------------------------------------
// Add the returned code to the connection object
//-----------------------------------------------------------------------------------------

$logger->write("Setting class properties.");

$adpConn->auth_code 		= $retcode;

//-------------------------------------------
// Request a token for API access
//-------------------------------------------

try {
	$logger->write("Requesting Token.");
	$result = $adpConn->Connect();
	$logger->write("Back from request.");
}
catch (adpException $e) {

	include("inc/header.php");
	showADPException($e);
	exit();

}

$logger->write("Success!  We have a token!");

//-------------------------------------------
// Success!  We have a token.
//-------------------------------------------

$_SESSION['adpConn'] = serialize($adpConn);

if (isset($_SESSION['goingto']) && strlen($_SESSION['goingto']) > 1) {

	$logger->write("Grabbing page logic for " . $_SESSION['goingto']);
	$goingto = $_SESSION['goingto'];
	$_SESSION['goingto'] = "";
	header("Location: /" . $goingto);

}
else {
	header("Location: /");
}

