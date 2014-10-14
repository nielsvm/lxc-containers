<?PHP
namespace LXC\Logging;
use \LXC\Types\Dictionary;
use \LXC\Logging\File;

/**
 * The directory holding all Apache logs.
 */
define('DIR', '/var/log/apache2');

/**
 * The list of log files accessible to the webserver user.
 */
class Files extends Dictionary {
  public function __construct() {
    foreach (scandir('/var/log/apache2') as $item) {
      $path = DIR . '/' . $item;
      if (is_file($path) && is_readable($path)) {
        $this->data[] = new File($path);
      }
    }
  }
}
