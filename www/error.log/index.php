<?php
define('LOGFILE', '/var/log/apache2/error.log');

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
    else {
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
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <title><?=basename(LOGFILE);?></title>
    <link rel="stylesheet" type="text/css" href="/min.css">
    <style>
      body {
        background-color: black;
        color: white;
        overflow-y: hidden;
        padding-left: 0.5em;
      }
      .ico {font: 25px Arial Unicode MS,Lucida Sans Unicode;}
      #log {
        position: relative;
        top: -34px;
        line-height: 20px;
        font-size: 10px;
      }
      #enable {}
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
      var interval = 1000;
      setInterval(readLogFile, interval);
      window.onload = readLogFile;
      var pathname = window.location.pathname;
      var scrollLock = true;
      var x_last_line = 0;

      $(document).ready(function(){
        $('.clear').click(function(){
          $("html,body").clearQueue()
          $('#log').html('<br />');
        });
        $('.disable').click(function(){
          $("html,body").clearQueue()
          $(".disable").hide();
          $(".enable").show();
          scrollLock = false;
        });
        $('.enable').click(function(){
          $("html,body").clearQueue()
          $(".enable").hide();
          $(".disable").show();
          scrollLock = true;
        });
      });
      function readLogFile() {
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
      <a class="disable btn smooth btn-sm" href="#"><i class="ico">☟</i></a>
      <a class="enable btn smooth btn-sm" style="display: none;" href="#"><i class="ico">⇦</i></a>
      <a class="clear btn smooth btn-a btn-sm" href="#"><i class="ico">☒</i></a>
    </div>
  </body>
</html>