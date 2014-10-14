<?PHP

// Load the autoloader and request the classes we need.
require 'vendor/autoload.php';
use LXC\Container\Variables;
use LXC\Logging\File;

// Initialize the file object for this log file.
$file = new File('/var/log/apache2/access.log');

/**
 * Render the log tail UI or send the payload when the JS requested this.
 */
if (isset($_GET['last_line'])) {
  $file->sendPayload((int) $_GET['last_line']);
}
else {
  $template = new h2o('templates/logtail.html');
  print $template->render(
    array(
      'file' => $file,
      'lxc' => new Variables(),
      'hostname' => gethostname()
    )
  );
}