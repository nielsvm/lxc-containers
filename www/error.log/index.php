<?php
if (isset($_GET['getLog'])) {
  die(file_get_contents("/var/log/apache2/error.log"));
}
?>
<html>
  <title>error.log</title>
  <style>
    body{
      background-color: black;
      color: white;
      font-family: "Lucida Console", Monaco, monospace;
      font-size: 10px;
      line-height: 20px;
      overflow-y: hidden;
    }
    h4{
      font-size: 18px;
      line-height: 22px;
      color: #353535;
    }
    #log {
      position: relative;
      top: -34px;
    }
    #scrollLock{
      width:2px;
      height: 2px;
      overflow: visible;
    }
  </style>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
  <script>
    var interval = 10000;
    setInterval(readLogFile, interval);
    window.onload = readLogFile;
    var pathname = window.location.pathname;
    var scrollLock = true;

    $(document).ready(function(){
      $('.disableScrollLock').click(function(){
        $("html,body").clearQueue()
        $(".disableScrollLock").hide();
        $(".enableScrollLock").show();
        scrollLock = false;
      });
      $('.enableScrollLock').click(function(){
        $("html,body").clearQueue()
        $(".enableScrollLock").hide();
        $(".disableScrollLock").show();
        scrollLock = true;
      });
    });
    function readLogFile() {
      $.get(pathname, { getLog : true }, function(data) {
              data = data.replace(new RegExp("\n", "g"), "<br />");
      $("#log").html(data);
      if(scrollLock == true) { $('html,body').animate({scrollTop: $("#scrollLock").offset().top}, interval) };
    });
    }
  </script>
  <body>
    <h4><?php echo $logFile; ?></h4>
    <div id="log"></div>
    <div id="scrollLock"> <input class="disableScrollLock" type="button" value="Disable Scroll Lock" /> <input class="enableScrollLock" style="display: none;" type="button" value="Enable Scroll Lock" /></div>
  </body>
</html>