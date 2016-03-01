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
session_start();
error_reporting(E_ALL);


// Load required files.
require ("config.php");
require ($libroot . "connection/adpapiConnection.class.php");

include("inc/header.php");

?>
<h1>Main Menu</h1>
<?php

$connected = false;

if (isset($_SESSION['adpConn'])) {

	$adpConn = unserialize($_SESSION['adpConn']);
	if ($adpConn->isConnectedIndicator()) {
		$connected = true;
	}

}

$connStatus = "<span style='color: red'>No</span>";
if ($connected) {

	$connType   = $adpConn->grant_type;
	$connStatus = "<span style='color: green'>Yes:</span> <span style='color: maroon'>$connType</span>";
}

?>

Connected: <?php echo $connStatus; ?>
<BR><BR>
<?php

if (!$connected) {
	?>

	<!-- Show Connection Options here -->

	<a class="box login" href="/connect.php?grant=client_credentials">Connect with Client Credentials</a>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

	<a class="box login" href="/connect.php?grant=authorization_code">Connect with Authorization Code</a><BR><BR>

	<HR>

	<?php
}
else {

	// Find available APIS to call

	$callArray = glob("*.apicall.php");

	?>

	<a class="box logout" href="/logout.php">Logout</a><BR><BR>

	<HR>

	<h2>Callable APIs</h2>

	<?php

	// Show APIS we can call

	foreach ($callArray as $thisfile) {

		$justfile = str_replace(".apicall.php", "", $thisfile);		// Find just the filename
		$justfile = str_replace("_", " ", $justfile);				// Replace underscores with spaces
		$justfile = ucwords($justfile);

		?>
		<a class="api" href="/<?php echo $thisfile; ?>"><?php echo $justfile; ?></a>
		<?php

	}

}

include("inc/footer.php");

