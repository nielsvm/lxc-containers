<?PHP
namespace LXC\VirtualHost;
use \LXC\VirtualHost\Listing;
use \LXC\VirtualHost\Writer;
use \h2o;
use \Net_SSH2;

/**
 * Represents all projects in /var/www.
 */
class ApacheProvisioner {

  /**
   * The SSH user used to connect to the container.
   * @var string
   */
  private $ssh_user = '';

  /**
   * The SSH password used to connect to the container.
   * @var string
   */
  private $ssh_pass = '';

  /**
   * The name of the template file for the splash page.
   * @var string
   */
  private $tpl_splash = '';

  /**
   * The name of the template file for the vhost configuration files.
   * @var string
   */
  private $tpl_vhost = '';

  /**
   * The listing object.
   * @var LXC\VirtualHost\Listing
   */
  private $listing = NULL;

  /**
   * Constructor.
   */
  public function __construct($ssh_user, $ssh_pass, $tpl_splash, $tpl_vhost) {
    $this->ssh_user = $ssh_user;
    $this->ssh_pass = $ssh_pass;
    $this->tpl_splash = $tpl_splash;
    $this->tpl_vhost = $tpl_vhost;
    $this->listing = Listing::get();
  }

  /**
   * Update Apache's VirtualHost configuration.
   */
  public function reconfigure() {
    $ssh = new Net_SSH2('localhost');
    if ($ssh->login($this->ssh_user, $this->ssh_pass)) {
      $w = new Writer($this->tpl_vhost, $this->listing, $ssh);
      $w->write();
      $ssh->disconnect();
    }
    else {
      print 'Cannot connect to the container as "' . $this->ssh_user
        . '", "' . $this->ssh_pass . '" over SSH!';
    }
  }

  /**
   * Render the splash page, which will call us over AJAX.
   */
  public function renderSplashPage() {
    $template = new h2o($this->tpl_splash);
    print $template->render();
  }

  /**
   * Check if an uninstalled vhost was reached, and execute if needed.
   */
  public function checkAndExecute() {
    if ($this->uninstalledVhostReached() ) {
      if (isset($_GET['reconfigure'])) {
        $this->reconfigure();
      }
      else {
        $this->renderSplashPage();
      }
      die();
    }
  }

  /**
   * Detect if the current request didn't reach the right codebase.
   */
  public function uninstalledVhostReached() {
    foreach ($this->listing as $vhost) {
      if ($_SERVER['HTTP_HOST'] == $vhost->domain) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Construct the provisioner and check.
   */
  static public function check($ssh_user, $ssh_pass, $tpl_splash, $tpl_vhost) {
    $p = new ApacheProvisioner($ssh_user, $ssh_pass, $tpl_splash, $tpl_vhost);
    $p->checkAndExecute();
  }
}