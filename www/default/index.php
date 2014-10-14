<?PHP

// Load the autoloader and request the classes we need.
require 'vendor/autoload.php';

/**
 * Let the provisioner automatically intervene and reconfigure Apache. This only
 * happens when users try to access a domain that Apache isn't aware off yet and
 * the provisioner will then - just in place - install vhost files and reload.
 */
\LXC\VirtualHost\ApacheProvisioner::check(
  'root', 'root', // SSH credentials used to execute commands as root.
  'templates/rebuilding.html',
  'templates/vhost.conf');

/**
 * Initialize the router and template engine.
 */
$router = new \Bramus\Router\Router();
$t = new \h2o();

/**
 * ROUTER REGISTRY: Index listing.
 */
$router->get('/', function() use ($t) {
  $t->loadTemplate('templates/listing.html');
  print $t->render(
    array(
      'lxc' => \LXC\Container\Variables::get(),
      'vhosts' => \LXC\VirtualHost\Listing::get(),
      'logfiles' => \LXC\Logging\Files::get(),
      'hostname' => gethostname()
    )
  );
});

/**
 * ROUTER REGISTRY: log tail UI for each log file.
 */
foreach (\LXC\Logging\Files::get() as $logfile) {
  $path = '/' . $logfile->name;
  $router->get($path, function() use ($t, $logfile) {
    $t->loadTemplate('templates/logtail.html');
    print $t->render(
      array(
        'file' => $logfile,
        'lxc' => \LXC\Container\Variables::get(),
        'hostname' => gethostname()
      )
    );
  });
  $router->get($path . '/(\d+)', function($from_line) use ($t, $logfile) {
    $logfile->sendPayload((int) $from_line);
  });
}

/**
 * Dispatch the request.
 */
$router->run();
