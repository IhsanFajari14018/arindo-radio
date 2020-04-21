<?php

include 'icecaststatus.class.php';

$ics = new IcecastStatus('http://rs-ap.id:8000/status.xsl');
print_r($ics);

echo "<br>";
$result = $ics->getMounts();
print_r($result);


 ?>
