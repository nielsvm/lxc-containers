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

  /**
   * The full absolute path to the document root.
   * @var string
   */
  public $path = '';

  /**
   * The short name of the site.
   * @var string
   */
  public $name = '';

  /**
   * The domain name as derived from the site.
   * @var string
   */
  public $domain = '';

  /**
   * Whether the site is a provided tool by this repository.
   * @var string
   */
  public $tool = FALSE;

  /**
   * Constructor.
   */
  public function __construct($path) {
    if (!file_exists($path)) {
      throw new \Exception("Path $path does not exist.");
    }
    $this->path = $path;
    $this->name = basename($path);
    $this->domain = sprintf(DOMAINFORMAT, $this->name);
    $this->tool = file_exists($this->path . '/.lxc-tool');
  }

  /**
   * Provide a string casted version.
   */
  function __toString() {
    return $this->name;
  }
}
