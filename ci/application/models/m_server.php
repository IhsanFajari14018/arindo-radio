<?php defined('BASEPATH') OR exit('No direct script access allowed');

class M_server extends CI_Model{

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

    if(preg_match_all("/$search_for/siU",$output,$matches)) {
      foreach($matches[0] as $match) {
        $to_push = str_replace($search_td,'',$match);
        $to_push = trim($to_push);
        array_push($temp_array,$to_push);
      }
    }

    if(count($temp_array)) {
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

}
