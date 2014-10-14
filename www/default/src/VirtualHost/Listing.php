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
   * Retrieve a statically cached copy of a Listing() instance.
   */
  static public function get() {
    static $copy;
    if (is_null($copy)) {
      $copy = new Listing();
    }
    return $copy;
  }
}