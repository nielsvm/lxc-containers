<?PHP
namespace LXC\Container;
use LXC\Types\Dictionary;

/**
 * Information about the LXC container.
 */
class Variables extends Dictionary {
  public function __construct() {
    require '/etc/lxc-containervars.php';
    foreach (get_defined_vars() as $var => $value) {
      $this->data[str_replace('lxc_', '', $var)] = $value;
    }
  }
}
