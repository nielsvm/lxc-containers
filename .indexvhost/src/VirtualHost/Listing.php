<?PHP
namespace LXC\VirtualHost;
Use \LXC\Types\Dictionary;
Use \LXC\VirtualHost\VirtualHost;

/**
 * Represents all projects in /var/www.
 */
class Listing extends Dictionary {

  /**
   * Constructor.
   */
  public function __construct() {
    $www = \LXC\Container\Variables::get('www_docroot');

    // Initialize every directory in WWW as VirtualHost object.
    foreach(scandir($www) as $node) {
      if (in_array($node, array('.', '..', 'default'))) {
        continue;
      }
      if (!is_dir($www . '/' . $node)) {
        continue;
      }
      $this->data[] = new VirtualHost($www . '/' . $node);
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