<?PHP
namespace LXC\VirtualHost;
use Net_SSH2;
use h2o;

/**
 * The name of the vhost files.
 */
define('FILEFORMAT', 'generated-%s');

/**
 * Writes vhost configuration files to /etc/apache2.
 */
class Writer {

  /**
   * The name of the template file to use.
   * @var string
   */
  private $template = NULL;

  /**
   * The listing object.
   * @var LXC\VirtualHost\Listing
   */
  private $listing = NULL;

  /**
   * The Net_SSH2 instance.
   * @var Net_SSH2
   */
  private $ssh = NULL;

  /**
   * Constructor.
   */
  public function __construct($template, Listing $listing, Net_SSH2 $ssh) {
    $this->template = $template;
    $this->listing = $listing;
    $this->ssh = $ssh;
  }

  /**
   * Write the virtual hosts and restart Apache2.
   */
  public function write() {
    $this->prepareFiles();
    $this->moveAndEnable();
    $this->ssh->exec('/etc/init.d/apache2 reload');

    // Deliberate delay to allow Apache to reload properly.
    sleep(10);
  }

  /**
   * Stage the files in /tmp, which we can access without SSH.
   */
  private function prepareFiles() {
    foreach ($this->listing as $vhost) {
      $template = new h2o($this->template, array('cache_dir'=>'/tmp'));
      file_put_contents(
        sprintf('/tmp/' . sprintf(FILEFORMAT, $vhost->domain)),
        $template->render(
          array('path' => $vhost->path, 'domain' => $vhost->domain))
      );
    }
  }

  /**
   * Move the files into /etc/apache2 and enable the vhosts.
   */
  private function moveAndEnable() {
    $glob = sprintf(FILEFORMAT, '*');
    $this->ssh->exec("rm /etc/apache2/sites-available/$glob");
    $this->ssh->exec("rm /etc/apache2/sites-enabled/$glob");

    foreach ($this->listing as $vhost) {
      $file = sprintf(FILEFORMAT, $vhost->domain);
      $this->ssh->exec("chown root:root /tmp/$file");
      $this->ssh->exec("mv /tmp/$file /etc/apache2/sites-available/");
      $this->ssh->exec("a2ensite $file");
    }
  }
}