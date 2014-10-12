<?php
/**
 * @author
 * Niels van Mourik <niels@nielsvm.org>
 *
 * @file
 * Automatic log file tailer - not to be called directly.
 *
 * Include this script after defining LOGFILE:
 * define('LOGFILE', '/var/log/apache2/error.log');
 */
if (!defined('LOGFILE')) {
  exit;
}
if (isset($_GET['last_line'])) {
  $handle = fopen(LOGFILE, 'r');
  $last_five_lines = array();
  $last_line = (int)$_GET['last_line'];

  while ($line = fgets($handle)) {
    if (!isset($current_line)) $current_line = -1;
    $current_line++;

    if ($current_line <= $last_line) {
      continue;
    }
    if ($last_line === 0) {
      $last_five_lines[] = $line;
      if (count($last_five_lines) > 5) {
        array_shift($last_five_lines);
      }
    }
    elseif (!strstr($line, 'GET /?last_line=')) {
      print $line;
    }
  }
  if (count($last_five_lines)) {
    print implode("", $last_five_lines);
  }
  fclose($handle);
  header('Content-type: text/plain');
  header(sprintf("X-Last-Line: %d", $current_line));
  die();
}

// Load the container variables, exported as PHP variables during initialization.
require "/etc/lxc-containervars.php";
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <title><?=basename(LOGFILE);?></title>
    <link rel="stylesheet" type="text/css" href="/min.css">
    <link rel="stylesheet" type="text/css" href="/icons.css">
    <style>
      body {
        background-color: black;
        color: white;
        overflow: hidden;
        padding-left: 0.5em;
      }
      .hidden {display: none;}
      #log {
        position: relative;
        top: -34px;
        line-height: 20px;
        font-size: 10px;
      }
      #controls a {
        float: left;
        margin-right: 0.5em;
      }
      #controls {
        width: 100%;
        height: 45px;
        overflow: visible;
      }
    </style>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
    <script>
      var interval = 3000;
      setInterval(readLogFile, interval);
      window.onload = readLogFile;
      var pathname = window.location.pathname;
      var scrollLock = true;
      var x_last_line = 0;
      var paused = false;

      $(document).ready(function(){
        $('#clear').click(function(){
          $("html,body").clearQueue()
          $('#log').html('<br />');
        });
        $('#noscroll').click(function(){
          $("html,body").clearQueue()
          $("#noscroll").hide();
          $("#scroll").show();
          scrollLock = true;
        });
        $('#scroll').click(function(){
          $("html,body").clearQueue()
          $("#scroll").hide();
          $("#noscroll").show();
          scrollLock = false;
        });
        $('#continue').click(function(){
          $("html,body").clearQueue()
          $("#pause").show();
          $("#continue").hide();
          paused = false;
        });
        $('#pause').click(function(){
          $("html,body").clearQueue()
          $("#continue").show();
          $("#pause").hide();
          paused = true;
        });
      });
      function readLogFile() {
        if (paused) {
          return;
        }

        $.get(pathname, { last_line : x_last_line }, function(data, textStatus, jqXHR) {
          x_last_line = jqXHR.getResponseHeader('X-Last-Line');
        $("#log").append(data);
        if(scrollLock == true) { $('html,body').animate({scrollTop: $("#controls").offset().top}, interval) };
      });
      }
    </script>
  </head>
  <body>
    <h4><?=LOGFILE;?></h4>
    <pre id="log"><br /></pre>
    <div id="controls">
      <a id="home" class="icon-home btn smooth btn-sm" href="http://<?=$lxc_ipv4_address;?>" target="_blank" title="Home"></a>
      <a id="noscroll" class="icon-arrow-right hidden btn smooth btn-a btn-sm" href="#" title="Enable scroll lock"></a>
      <a id="scroll" class="icon-arrow-down btn smooth btn-a btn-sm" href="#" title="Disable scroll lock"></a>

      <a id="continue" class="icon-play hidden btn smooth btn-a btn-sm" href="#" title="Continue"></a>
      <a id="pause" class="icon-pause btn smooth btn-a btn-sm" href="#" title="Pause"></a>


      <a id="clear" class="icon-arrow-up btn smooth btn-a btn-sm" href="#" title="Clear"></a>
    </div>
  </body>
</html>