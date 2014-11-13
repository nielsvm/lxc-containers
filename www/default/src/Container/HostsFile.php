<?PHP
namespace LXC\Container;
use \LXC\Types\Dictionary;

/**
 * The (mounted) hosts file from the host.
 */
define('HOSTSFILE', '/host-etc/hosts');

/**
 * Represents a read-only copy of the systems hosts file.
 */
class HostsFile extends Dictionary {

  /**
   * The full absolute path to the (mounted) hosts file on the host.
   * @var string
   */
  public $path = HOSTSFILE;

  /**
   * The buffered copy of the hosts file.
   * @var string
   */
  public $buffer = '';

  /**
   * Constructor.
   */
  public function __construct() {
    if (!file_exists($this->path)) {
      throw new \Exception("Path ". $this->path ."does not exist.");
    }

    // Read and parse the hosts file.
    $this->buffer = file_get_contents($this->path);
    $this->data['domains'] = array();
    foreach (explode("\n", $this->buffer) as $line) {
      $cols = preg_split('/\s+/', $line);
      if (isset($cols[0]) && $cols[0] == '#') {
        continue;
      }
      if (isset($cols[0]) && $cols[0] == '') {
        continue;
      }
      $ip = array_shift($cols);
      if (!isset($this->data[$ip])) {
        $this->data[$ip] = array();
      }
      foreach ($cols as $domain) {
        if (!in_array($domain, $this->data[$ip])) {
          $this->data[$ip][] = $domain;
        }
        if (!in_array($domain, $this->data['domains'])) {
          $this->data['domains'][] = $domain;
        }
      }
    }
  }

  /**
   * Check if the given domain exists in the hosts file.
   * @param $domain
   *   The domain name to check.
   * @return
   *   Boolean TRUE/FALSE.
   */
  static public function hasDomain($domain) {
    $hosts_outdated = FALSE;
    $class = get_called_class();
    $hosts = $class::get();
    return in_array($domain, $hosts['domains']);
  }
}