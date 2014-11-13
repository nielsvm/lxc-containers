<?PHP
namespace LXC\VirtualHost;
Use \LXC\Types\Dictionary;
Use \LXC\VirtualHost\VirtualHost;

/**
 * The hardcoded document root (as mounted within the container).
 */
define('WWW', '/var/www');

/**
 * Represents all projects in /var/www.
 */
class Listing extends Dictionary {

  /**
   * Constructor.
   */
  public function __construct() {

    // Initialize every directory in WWW as VirtualHost object.
    foreach(scandir(WWW) as $node) {
      if (in_array($node, array('.', '..', 'default'))) {
        continue;
      }
      if (!is_dir(WWW . '/' . $node)) {
        continue;
      }
      $this->data[] = new VirtualHost(WWW . '/' . $node);
    }
  }

  /**
   * Check if any of the vhosts are missing from the hosts file.
   */
  static public function are_hosts_outdated() {
    $hosts_outdated = FALSE;
    $class = get_called_class();
    foreach ($class::get() as $vhost) {
      if (!$vhost->is_in_hosts) {
        $hosts_outdated = TRUE;
        break;
      }
    }
    return $hosts_outdated;
  }
}