<?PHP

// Load the autoloader and request the classes we need.
require 'vendor/autoload.php';

/**
 * Let the provisioner automatically intervene and reconfigure Apache. This only
 * happens when users try to access a domain that Apache isn't aware off yet and
 * the provisioner will then - just in place - install vhost files and reload.
 */
LXC\VirtualHost\ApacheProvisioner::check(
  'root', 'root', // SSH credentials used to execute commands as root.
  'templates/rebuilding.html',
  'templates/vhost.conf');

/**
 * Render the index listing.
 */
$template = new h2o('templates/listing.html');
print $template->render(
  array(
    'lxc' => new LXC\Container\Variables(),
    'vhosts' => LXC\VirtualHost\Listing::get(),
    'hostname' => gethostname()
  )
);
