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


function showADPException($exception) {

?>

<h2>Exception Encountered!</h2>

<div class="alert alert-danger">

<Table>
<tr><td>Message: 	</td>	<td><?php echo $exception->getMessage(); ?></td></tr>
<tr><td>Status: 	</td>	<td><?php echo $exception->getStatus(); ?></td></tr>
<tr><td valign="top">Response: 	</td>	<td><?php echo nl2br($exception->getResponse()); ?></td></tr>
</table>

</div>

<?php
}