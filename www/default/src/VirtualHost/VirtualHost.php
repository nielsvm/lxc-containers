<?PHP
namespace LXC\VirtualHost;

/**
 * The string format of which a domain name is derived.
 */
define('DOMAINFORMAT', '%s.loc');

/**
 * Represents a single virtualhost.
 */
class VirtualHost {
  public $path = '';
  public $name = '';
  public $domain = '';
  public $tool = FALSE;

  public function __construct($path) {
    if (!file_exists($path)) {
      throw new \Exception("Path $path does not exist.");
    }
    $this->path = $path;
    $this->name = basename($path);
    $this->domain = sprintf(DOMAINFORMAT, $this->name);
    $this->tool = file_exists($this->path . '/.lxc-tool');
  }

  function __toString() {
    return $this->name;
  }
}
