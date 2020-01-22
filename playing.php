<?php

/**
* Please be aware. This gist requires at least PHP 5.4 to run correctly.
* Otherwise consider downgrading the $opts array code to the classic "array" syntax.
*/
function getMp3StreamTitle($streamingUrl, $interval, $offset = 0, $headers = true){
  $needle = 'StreamTitle=';
  $ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36';

  $opts = [
    'http' => [
      'method' => 'GET',
      'header' => 'Icy-MetaData: 1',
      'user_agent' => $ua
    ]
  ];

  if (($headers = get_headers($streamingUrl))) {
    foreach ($headers as $h) {
      if (strpos(strtolower($h), 'icy-metaint') !== false && ($interval = explode(':', $h)[1])) {
        break;
      }
    }
  }

  $context = stream_context_create($opts);

  if ($stream = fopen($streamingUrl, 'r', false, $context)) {
    $buffer = stream_get_contents($stream, $interval, $offset);
    fclose($stream);

    if (strpos($buffer, $needle) !== false) {
      $title = explode($needle, $buffer)[1];
      return substr($title, 1, strpos($title, ';') - 2);
    } else {
      return getMp3StreamTitle($streamingUrl, $interval, $offset + $interval, false);
    }
  } else {
    throw new Exception("Unable to open stream [{$streamingUrl}]");
  }
}

$result = getMp3StreamTitle('http://1.1.1.239:8000/stream', 19200);
echo $result;
// var_dump($result);
// print_r($result);

?>
