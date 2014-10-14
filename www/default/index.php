<?PHP

// Load the autoloader and request the classes we need.
require 'vendor/autoload.php';
use LXC\VirtualHost\Listing;
use LXC\VirtualHost\Writer;
use LXC\Container\Variables;

/**
 * Construct the Listing object, which holds all virtual hosts.
 */
$vhosts = new Listing();

/**
 * Handle new virtual hosts.
 */
if ($vhosts->uninstalledVhostReached()) {

  // Render the one-moment page to the user.
  if (!isset($_GET['reconfigure'])) {
    $template = new h2o('templates/rebuilding.html');
    print $template->render();
  }

  // Open a SSH connection and reconfigure Apache if ?reconfigure is passed.
  else {
    $ssh = new Net_SSH2('localhost');
    if ($ssh->login('root', 'root')) {
      $w = new Writer('templates/vhost.conf', $vhosts, $ssh);
      $w->write();
      $ssh->disconnect();
    }
    else {
      die('Cannot connect to the container as "root", "root" over SSH!');
    }
  }
}

/**
 * Normally, just render the index listing.
 */
else {
  $template = new h2o('templates/listing.html');
  print $template->render(
    array(
      'lxc' => new Variables(),
      'vhosts' => $vhosts,
      'hostname' => gethostname()
    )
  );
}
