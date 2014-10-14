<?PHP
namespace LXC\Container;

/**
 * The name of the PHP file that contains the variables.
 */
define('FILE', '/etc/lxc-containervars.php');

/**
 * Encapsulates the PHP variables written out by the container.
 */
class Variables implements \ArrayAccess {
  private $data = array();

  public function __construct() {
    require FILE;
    foreach (get_defined_vars() as $var => $value) {
      $this->data[str_replace('lxc_', '', $var)] = $value;
    }
  }

  /**
   * Whether or not an data exists by key
   *
   * @param string An data key to check for
   * @access public
   * @return boolean
   * @abstracting ArrayAccess
   */
  public function __isset ($key) {
    return isset($this->data[$key]);
  }

  /**
   * Unsets an data by key
   *
   * @param string The key to unset
   * @access public
   */
  public function __unset($key) {
    unset($this->data[$key]);
  }

  /**
   * Assigns a value to the specified offset
   *
   * @param string The offset to assign the value to
   * @param mixed  The value to set
   * @access public
   * @abstracting ArrayAccess
   */
  public function offsetSet($offset, $value) {
    throw new \Exception("This container is read-only!");
  }

  /**
   * Whether or not an offset exists
   *
   * @param string An offset to check for
   * @access public
   * @return boolean
   * @abstracting ArrayAccess
   */
  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }

  /**
   * Unsets an offset
   *
   * @param string The offset to unset
   * @access public
   * @abstracting ArrayAccess
   */
  public function offsetUnset($offset) {
    if ($this->offsetExists($offset)) {
      unset($this->data[$offset]);
    }
  }

  /**
   * Returns the value at specified offset
   *
   * @param string The offset to retrieve
   * @access public
   * @return mixed
   * @abstracting ArrayAccess
   */
  public function offsetGet($offset) {
    return $this->offsetExists($offset) ? $this->data[$offset] : null;
  }
}
