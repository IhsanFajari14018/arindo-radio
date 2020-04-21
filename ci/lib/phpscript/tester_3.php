<?php

$IC = new IceCast();
$result = $IC->getStatus();
// $result = json_encode($result);
print_r($result);
echo "<br><br>";

echo "<b>Mount Start: </b>".$result['mount_start']."<br>";
echo "<b>Current Listeners: </b>".$result['listeners'];

// $result_decode = json_decode($result);
// print_r($result_decode);

class IceCast {
  var $server = "http://rs-ap.id:8000";
  var $stats_file = "/status.xsl";
  var $radio_info=array();

  function __construct() {
    //build array to store our radio stats for later use
    $this->radio_info['server'] = $this->server;
    $this->radio_info['title'] = 'Offline';
    $this->radio_info['description'] = 'Radio offline';
    $this->radio_info['content_type'] = '';
    $this->radio_info['mount_start'] = '';
    $this->radio_info['bit_rate'] = '';
    $this->radio_info['listeners'] = '';
    $this->radio_info['most_listeners'] = '';
    $this->radio_info['genre'] = '';
    $this->radio_info['url'] = '';
    $this->radio_info['now_playing'] = array();
    $this->radio_info['now_playing']['artist'] = 'Unknown';
    $this->radio_info['now_playing']['track'] = 'Unknown';
  }

  function setUrl($url) {
    $this->server=$url;
    $this->radio_info['server'] = $this->server;
  }

  private function fetch() {
    //create a new curl resource
    $ch = curl_init();

    //set url
    curl_setopt($ch,CURLOPT_URL,$this->server.$this->stats_file);

    //return as a string
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

    //$output = our stauts.xsl file
    $output = curl_exec($ch);

    //close curl resource to free up system resources
    curl_close($ch);

    return $output;
  }

  function getStatus() {
    $output=$this->fetch();

    //loop through $ouput and sort into our different arrays
    $temp_array = array();

    $search_for = "<td\s[^>]*class=\"streamstats\">(.*)<\/td>";
    $search_td = array('<td class="streamstats">','</td>');

    // print_r($output);

    if(preg_match_all("/$search_for/siU",$output,$matches)) {
      foreach($matches[0] as $match) {
        $to_push = str_replace($search_td,'',$match);
        $to_push = trim($to_push);
        array_push($temp_array,$to_push);
      }
    }

    print_r($temp_array);
    echo "<br><br>";

    if(count($temp_array)) {

      // echo $temp_array[6];
      //sort our temp array into our ral array
      // $this->radio_info['title'] = $temp_array[0];
      // $this->radio_info['description'] = $temp_array[1];
      // $this->radio_info['content_type'] = $temp_array[2];
      $this->radio_info['mount_start'] = $temp_array[14];
      $this->radio_info['bit_rate'] = $temp_array[15];
      $this->radio_info['listeners'] = $temp_array[16];
      $this->radio_info['most_listeners'] = $temp_array[17];
      $this->radio_info['genre'] = $temp_array[18];
      $this->radio_info['url'] = $temp_array[19];

      if(isset($temp_array[20])) {
        $x = explode(" - ",$temp_array[20]);
        $this->radio_info['now_playing']['artist'] = $x[0];
        $this->radio_info['now_playing']['track'] = $x[1];
      }
    }
    return $this->radio_info;
  }

  private function dummyResult(){
    $array = "Array (
    [server] => 'http://rs-ap.id:8000'
    [title] => Offline
    [description] => Radio offline
    [content_type] =>
      [mount_start] => Fri, 17 Apr 2020 17:11:16 +0700
      [bit_rate] => 96
      [listeners] => 6
      [most_listeners] => 25
      [genre] => Any Genre
      [url] => rs-ap.id
      [now_playing] => Array (
        [artist] => Guns N Roses
        [track] => Welcome To The Jungle
      )
    )";
  }

}
?>
