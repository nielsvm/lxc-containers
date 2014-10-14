<?PHP
namespace LXC\Logging;

/**
 * Represents a single virtualhost.
 */
class File {

  /**
   * The full absolute path to the log file.
   * @var string
   */
  public $path = '';

  /**
   * The base name of the log file.
   * @var string
   */
  public $name = '';

  /**
   * Represents the line number at the end of the generated payload.
   * @var int
   */
  private $last_line_number = 0;

  /**
   * Constructor.
   */
  public function __construct($path) {
    if (!file_exists($path)) {
      throw new \Exception("Path $path does not exist.");
    }
    $this->path = $path;
    $this->name = basename($path);
  }

  /**
   * Provide a string casted version.
   */
  public function __toString() {
    return $this->name;
  }

  /**
   * Finish the HTTP request and respond with the payload.
   *
   * @param $from_line
   *   The line from which the payload needs to begin.
   */
  public function sendPayload($from_line = 0) {
    $payload = $this->getPayload($from_line);
    header('Content-type: text/plain');
    header('Cache-control: no-cache, must-revalidate, post-check=0, pre-check=0');
    header('X-Last-Line: ' . $this->getLastLine());
    die($payload);
  }

  /**
   * Extract a payload from the log file.
   *
   * @param $from_line
   *   The line from which the payload needs to begin.
   *
   */
  public function getPayload($from_line = 0) {
    $last_five_lines = array();
    $current_line = -1;
    $handle = fopen($this->path, 'r');
    $buffer = '';

    // Open the file and build a buffer of log lines.
    while ($line = fgets($handle)) {
      $current_line++;
      if ($current_line <= $from_line) {
        continue;
      }
      if ($from_line === 0) {
        $last_five_lines[] = $line;
        if (count($last_five_lines) > 5) {
          array_shift($last_five_lines);
        }
      }
      else {
        $this->getPayloadBufferAppended($line, $buffer);
      }
    }
    if (count($last_five_lines)) {
      foreach ($last_five_lines as $line) {
        $this->getPayloadBufferAppended($line, $buffer);
      }
    }
    fclose($handle);

    // Determine the last line number.
    if ($current_line == -1) {
      $this->last_line_number = 0;
    }
    else {
      $this->last_line_number = $current_line;
    }

    return $buffer;
  }

  /**
   * Append a line to the payload buffer.
   *
   * @param $line
   *   The line to append.
   * @param &$buffer
   *   The string buffer to append to.
   */
  private function getPayloadBufferAppended($line, &$buffer) {

    // Filter out access.log records that this script generated.
    if (strstr($line, 'GET /?last_line=')) {
      return;
    }

    $buffer .= $line;
  }

  /**
   * Get the last line number.
   *
   * @see getPayload()
   */
  public function getLastLine() {
    return $this->last_line_number;
  }
}
