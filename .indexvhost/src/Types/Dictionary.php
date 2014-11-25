<?PHP
namespace LXC\Types;

/**
 * General purpose data container accessible as array, as object and iterable.
 */
abstract class Dictionary implements \ArrayAccess, \Iterator {

  /**
   * The data to be held by this container.
   * @var array
   */
  protected $data = array();

  /**
   * Iteration position.
   * @var int
   */
  private $index = 0;

  /**
   * Numeric index to associative key mapping.
   * @var array
   */
  private $keys = array();

  /**
   * Get a data by key.
   */
  public function &__get($key) {
    return $this->data[$key];
  }

  /**
   * Assigns a value to the specified data.
   */
  public function __set($key, $value) {
    $this->data[$key] = $value;
    $this->keys = array_keys($this->data);
  }

  /**
   * Whether or not an data exists by key.
   * @abstracting \ArrayAccess
   */
  public function __isset($key) {
    return isset($this->data[$key]);
  }

  /**
   * Deletes data by key.
   */
  public function __unset($key) {
    unset($this->data[$key]);
    $this->keys = array_keys($this->data);
  }

  /**
   * Assigns a value to the specified offset.
   * @abstracting \ArrayAccess
   */
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->data[] = $value;
    } else {
      $this->data[$offset] = $value;
    }
    $this->keys = array_keys($this->data);
  }

  /**
   * Whether or not an offset exists.
   * @abstracting \ArrayAccess
   */
  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }

  /**
   * Unsets an offset.
   * @abstracting \ArrayAccess
   */
  public function offsetUnset($offset) {
    if ($this->offsetExists($offset)) {
      unset($this->data[$offset]);
      $this->keys = array_keys($this->data);
    }
  }

  /**
   * Returns the value at specified offset.
   * @abstracting \ArrayAccess
   */
  public function offsetGet($offset) {
    return $this->offsetExists($offset) ? $this->data[$offset] : NULL;
  }

  /**
   * Rewind the iterator back to the beginning.
   * @abstracting \Iterator
   */
  public function rewind() {
    $this->keys = array_keys($this->data);
    $this->index = 0;
  }

  /**
   * Retrieve the current position within the dictionary.
   * @abstracting \Iterator
   */
  public function current() {
    $this->keys = array_keys($this->data);
    return $this->data[$this->keys[$this->index]];
  }

  /**
   * Retrieve the current key position within the dictionary.
   * @abstracting \Iterator
   */
  public function key() {
    $this->keys = array_keys($this->data);
    return $this->keys[$this->index];
  }

  /**
   * Advance the index position.
   * @abstracting \Iterator
   */
  function next() {
    $this->keys = array_keys($this->data);
    ++$this->index;
  }

  /**
   * Check whether the index exists.
   * @abstracting \Iterator
   */
  function valid() {
    $this->keys = array_keys($this->data);
    return isset($this->keys[$this->index]);
  }

  /**
   * Retrieve a statically cached copy of a Listing() instance.
   */
  static public function get($key = NULL) {
    static $copies;
    $class = get_called_class();
    if (is_null($copies)) {
      $copies = array();
    }
    if (!isset($copies[$class])) {
      $copies[$class] = new $class();
    }
    if (is_null($key)) {
      return $copies[$class];
    }
    else {
      return $copies[$class][$key];
    }
  }
}
