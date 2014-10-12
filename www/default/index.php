<?PHP

// Load the container variables, exported as PHP variables during initialization.
require "/etc/lxc-containervars.php";

// Generate a reliable list of projects.
$projects = array();
foreach(scandir('/var/www') as $project) {
  if (in_array($project, array('.', '..', 'default'))) {
    continue;
  }
  if (!is_dir("/var/www/$project")) {
    continue;
  }
  $projects[] = $project;
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <title><?=gethostname();?></title>
    <link rel="stylesheet" type="text/css" href="/min.css">
    <style media="screen" type="text/css">
      #details {
        display: none;
      }
      .msg {
        background-color: #F7F7F7;
        border-left: 5px solid #C0C0C0;
      }
      code {
        background-color: #F7F7F7;
        margin-left: 0.1em;
        margin-right: 0.1em;
        letter-spacing: 0.05em;
        color: #800080;
      }
    </style>
    <script type="text/javascript">
      <!--
        function toggle_visibility(id) {
          var e = document.getElementById(id);
          if(e.style.display == 'block')
            e.style.display = 'none';
          else
            e.style.display = 'block';
        }
      //-->
    </script>
</head>
  <body>
    <div class="container">
      <div>
        <h2><?=gethostname();?></h2>
        <p>Welcome to your container powered webserver!</p>
      </div>

      <div class="sites">
        <p>
<?php
  foreach($projects as $project) {
    if (file_exists(sprintf('/var/www/%s/.lxc-tool', $project))) {
      printf('<a class="btn smooth btn-a" href="http://%s.loc/" target="_blank">%s</a>&nbsp;', $project, $project);
    }
    else {
      printf('<a class="btn smooth" href="http://%s.loc/" target="_blank">%s</a>&nbsp;', $project, $project);
    }
  }
?>
        </p>
        <p>
          <a class="btn smooth btn-a btn-sm" href="#" onclick="toggle_visibility('details');">help</a>
        </p>
      </div>
      <div id="details">
        <div>
          <h3>How it works</h3>
          <p>
            Projects placed in the <code>www/</code> directory are automatically set up as virtual hosts with their own domain names. For instance, a directory <code>mysite</code> can be reached via <code>http://mysite.loc/</code> after you configured its <code>/etc/hosts</code> record. You can <i>easily</i> update your hosts file with the <code>www/update-etc-hosts</code> script:
          </p>
          <div class="msg">
  <pre>$ cd www/
$ ./update-etc-hosts
[sudo] password for <?=$lxc_user?>:
Successfully replaced the old hosts in /etc/hosts!

# update-etc-hosts - DO NOT TOUCH THIS SECTION! #############
10.0.3.10 adminer.loc
# /update-etc-hosts #########################################</pre>
          </div>
          <p>
            But if that isn't your thing, you can use these auto-generated lines:
          </p>
          <div class="msg">
<pre><?php
  foreach($projects as $project) {
    printf("%s\t%s.loc\n", $lxc_ipv4_address, $project);
  }
?></pre>
          </div>
        </div>

        <div>
          <h4>Different projects directory?</h4>
          <p>Instead of the <code>www/</code> directory, you can mount your own projects directory as long as your user owns the files. This is achieved by changing the <code>config.ini</code> file of your container to mount your projects directory as <code>/var/www</code> in the container, a <code>halt -p</code> and restart of your container is sufficient.</p>
          <pre>/path/to/my/projects/on/host = /var/www</pre>
          <p>Alternatively, you can make symbolic links from within <code>www/</code> to your real code checkouts. Both Apache as this script will see them as real sites and everything will remain to work, plus, it makes it more flexible to swap codebases.</p>
        </div>
      </div>
    </div>
  </body>
</html>
































