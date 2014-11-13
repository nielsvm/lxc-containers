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
   * Whether the hosts's /etc/hosts file needs updates or not.
   */
  public $hostsoutdated = FALSE;

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

    // Check if one of the VirtualHosts are missing from /etc/hosts.
    $this->hostsoutdated = FALSE;
    foreach ($this->data as $vhost) {
      if (!$vhost->is_in_hosts) {
        $this->hostsoutdated = TRUE;
      }
    }
  }
}