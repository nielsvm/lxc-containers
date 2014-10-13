<?PHP
namespace LXC\VirtualHost;
Use LXC\VirtualHost\VirtualHost;

/**
 * The hardcoded document root (as mounted within the container).
 */
define('WWW', '/var/www');

/**
 * Represents all projects in /var/www.
 */
class Listing implements \Iterator {
  private $position = 0;
  private $vhosts = array();

  public function __construct() {
    $this->position = 0;
    foreach(scandir(WWW) as $node) {
      if (in_array($node, array('.', '..', 'default'))) {
        continue;
      }
      if (!is_dir(WWW . '/' . $node)) {
        continue;
      }
      $this->vhosts[] = new VirtualHost(WWW . '/' . $node);
    }
  }

  # Detect if the current request didn't reach the right codebase.
  public function uninstalledVhostReached() {
    foreach ($this->vhosts as $vhost) {
      if ($_SERVER['HTTP_HOST'] == $vhost->domain) {
        return TRUE;
      }
    }
    return FALSE;
  }

  function rewind() {
    $this->position = 0;
  }

  function current() {
    return $this->vhosts[$this->position];
  }

  function key() {
    return $this->position;
  }

  function next() {
    ++$this->position;
  }

  function valid() {
    return isset($this->vhosts[$this->position]);
  }
}