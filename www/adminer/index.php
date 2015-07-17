<?php

/**
 * Define a AdminerSoftware object providing the LXC credentials.
 */
function adminer_object() {
  class AdminerSoftware extends Adminer {

    function permanentLogin() {
      return "permanent";
    }

    function credentials() {
      require "/etc/lxc-containervars.php";
      return array($lxc_mysqlclient_host, $lxc_mysqlclient_user, $lxc_mysqlclient_password);
    }
  }

  return new AdminerSoftware;
}

/**
 * Fake ?username, which gets us a UI instantly.
 */
$_GET['username'] = '';

/**
 * Load Adminer.
 */
require "adminer-4.1.0.php";
