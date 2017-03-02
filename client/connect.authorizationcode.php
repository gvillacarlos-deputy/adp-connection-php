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

// Set some handling up.

if (!session_id()) {
	session_start();
}
error_reporting(E_ALL);


// Load required files.
require_once ("config.php");
require_once ($libroot . "connection/adpapiConnection.class.php");

//----------------------
// Create Config Array
//----------------------

$configuration = array (
        'grantType' 			=> 'authorization_code',
        'clientID'				=> $ADP_AC_CLIENTID,
        'clientSecret'			=> $ADP_AC_CLSECRET,
        'sslCertPath'			=> $ADP_CERTFILE,
        'sslKeyPath'			=> $ADP_KEYFILE,
        'tokenServerURL'		=> $ADP_APIROOT,
        'scope'					=> 'openid',
        'responseType'			=> 'code',
        'redirectURL'			=> $ADP_REDIRECTURL
    );


//----------------------
// Create the class
//----------------------

try {
	$logger->write("Creating class.");
	$adpConn = adpapiConnectionFactory::create($configuration);
	$logger->write("Class created.");
}
catch (adpException $e) {

	include("inc/header.php");
	showADPException($e);
	exit();

}

//-----------------------------------------------
// Request an authentication URL from Connection
//-----------------------------------------------

try {

	$logger->write("Getting Auth URL");
	$result = $adpConn->getAuthRequest();
	$logger->write("Success!");
}
catch (adpException $e) {

	include("inc/header.php");
	showADPException($e);
	exit();

}

$_SESSION['state'] = $adpConn->state;
$_SESSION['adpConn'] = serialize($adpConn);

session_commit();

$logger->write("URL created.  Redirecting for authentication.");
header("Location: " . $result);

