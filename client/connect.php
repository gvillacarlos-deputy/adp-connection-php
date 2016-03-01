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

@session_start();

require_once("config.php");
require_once($libroot . "connection/adpapiConnection.class.php");

if (isset($_SESSION['adpConn'])) {

	$adpConn = unserialize($_SESSION['adpConn']);
	if ($adpConn->isConnectedIndicator()) {
		header("Location: /");
		exit();
	}

}

require("config.php");

$gtype = $_GET['grant'];

if ($gtype == "client_credentials") {
	include("connect.clientcredentials.php");
	header("Location: /");
}
elseif ($gtype == "authorization_code") {

	// If this is authorization code, AND not running script is NOT connect.php, add to session.
	$tmp = $_SERVER['PHP_SELF'];

	$tmp = str_replace("/", "", $tmp);

	if ($tmp != "doconnect.php" && $tmp != "connect.php") {

		$_SESSION['goingto'] = $tmp;
	}
	else {
		$_SESSION['goingto'] = "";
	}

	include("connect.authorizationcode.php");
	exit();
}
else {
	echo "Invalid Grant Type";
	exit();
}

