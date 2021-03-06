<?PHP
require 'vendor/autoload.php';

/**
 * Let the provisioner automatically intervene and reconfigure Apache. This only
 * happens when users try to access a domain that Apache isn't aware off yet and
 * the provisioner will then - just in time - install vhost files and reload.
 */
\LXC\VirtualHost\ApacheProvisioner::check(
  'root', 'root', // SSH credentials used to execute commands as root.
  'templates/rebuilding.html',
  'templates/vhost.conf');

/**
 * Configure the router.
 */
$router = new \Bramus\Router\Router();
$t = new \h2o();

# ROUTE /: index listing.
$router->get('/', function() use ($t) {
  $t->loadTemplate('templates/listing.html');
  print $t->render(
    array(
      'lxc' => \LXC\Container\Variables::get(),
      'vhosts' => \LXC\VirtualHost\Listing::get(),
      'hostsoutdated' => \LXC\VirtualHost\Listing::are_hosts_outdated(),
      'logfiles' => \LXC\Logging\Files::get(),
      'hostname' => gethostname()));
});

# ROUTE /php: PHP information.
$router->get('/php', function() {phpinfo();});

# ROUTE /$LOGFILE: tail -f style log viewer.
foreach (\LXC\Logging\Files::get() as $logfile) {
  $path = '/' . $logfile->name;
  $router->get($path, function() use ($t, $logfile) {
    $t->loadTemplate('templates/logtail.html');
    print $t->render(
      array(
        'file' => $logfile,
        'lxc' => \LXC\Container\Variables::get(),
        'hostname' => gethostname()));
  });
  $router->get($path . '/(\d+)', function($from_line) use ($t, $logfile) {
    $logfile->sendPayload((int) $from_line);
  });
}

/**
 * Redirect on unmatched routes.
 */
$router->set404(function() {
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: /');
  echo '404, route not found!';
});

/**
 * Dispatch the request.
 */
$router->run();
