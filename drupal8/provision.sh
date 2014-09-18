#!/bin/bash

### BEGIN INIT INFO
# Provides:          provisioner
# Required-Start:    $all
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Start the container provisioning first time.
# Description:       Start provisioning and get the machine in ready state.
### END INIT INFO

#
# Source the variables the host provisioner gave us.
#
source /etc/lxc-containervars.sh

#
# Helper functions.
#
export DEBIAN_FRONTEND=noninteractive
export STATEFILE='/root/bootstrap-state'
export GIT_AUTHOR_NAME="$LXC_USER"
export GIT_AUTHOR_EMAIL=$LXC_USER@localhost
export GIT_COMMITTER_NAME="$GIT_AUTHOR_NAME"
export GIT_COMMITTER_EMAIL="$GIT_AUTHOR_EMAIL"

# Install APT package(s) as given in the first argument.
function addpkg {
  export DEBIAN_FRONTEND=noninteractive
  command="apt-get install --force-yes -y $1"
  eval $command
}

# Remove APT package(s) as given in the first argument.
function delpkg {
  export DEBIAN_FRONTEND=noninteractive
  command="apt-get remove -y $1"
  eval $command
}

# Commit changes to /etc and make a restore point.
function etc-save {
  currentdir=`pwd`
  cd /etc
  git add .
  git commit -a -m "$1"
  cd $currentdir
}

#
# Bootstrapping phases.
#

# Bootstrap phase 1: Install GIT and initialize a repository in /etc.
function bootstrap_1_etc_in_git_repo {
  apt-get update
  addpkg git
  cd /etc
  git init
  etc-save "Initial state of /etc..."
  touch /etc/.gitignore
  echo 'apache2/sites*' >> /etc/.gitignore
  etc-save "Added .gitignore"
}

# Bootstrap phase 2: Setup the user account.
function bootstrap_2_setup_user {
  useradd -U -m -d "$LXC_HOME" -s /bin/bash -u 1000 "$LXC_USER"
  passwd -q -e $LXC_USER
  etc-save "Added user $LXC_USER"
}

# Bootstrap phase 3: Setup the dotdeb.org repository and update APT.
function bootstrap_3_setup_dotdeb {
  addpkg wget
  etc-save "Installed wget"
  echo "deb http://packages.dotdeb.org wheezy-php55 all" >> /etc/apt/sources.list
  echo "deb-src http://packages.dotdeb.org wheezy-php55 all" >> /etc/apt/sources.list
  wget http://www.dotdeb.org/dotdeb.gpg
  apt-key add dotdeb.gpg
  apt-get update
  rm dotdeb.gpg
  etc-save "Setup of the dotdeb repo for PHP 5.5"
}

# Bootstrap phase 4: install PHP 5.5.
function bootstrap_4_install_apache_php {

  # Install packages.
  addpkg "apache2"
  etc-save "Vanilla Apache with worker MPM"
  addpkg "apache2-mpm-prefork"
  etc-save "Switched to Apache's prefork MPM."
  addpkg "libapache2-mod-php5 php5 php5-curl php5-apcu php5-mysqlnd php5-cli php5-gd php5-gmp"
  etc-save "Stock PHP 5.5 and Apache2 packages"
}

# Bootstrap phase 5: tune the server itself.
function bootstrap_5_tune_server {
  HOSTNAME=`hostname`

  # Add important records to /etc/hosts.
  echo "$LXC_IPV4_ADDRESS master" >> /etc/hosts
  echo "127.0.0.1       $HOSTNAME" >> /etc/hosts
  etc-save "Updated /etc/hosts."
}

# Bootstrap phase 6: tune PHP and its extensions.
function bootstrap_6_tune_php {

  # OPCACHE: enable the opcache as it doesn't seem to be by default.
#   zend_extension=opcache.so
  echo 'opcache.enable=On' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.memory_consumption=128' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.interned_strings_buffer=16' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.max_accelerated_files=4000' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.revalidate_freq=60' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.fast_shutdown=1' >> /etc/php5/mods-available/opcache.ini
  echo 'opcache.enable_cli=1' >> /etc/php5/mods-available/opcache.ini
  etc-save "php: enable opcache"

  # XDEBUG: enable pretty var_dump output.
  echo 'xdebug.var_display_max_depth=20' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.show_local_vars=on' >> /etc/php5/mods-available/xdebug.ini
  etc-save "php: enable xdebug var_dump output"

  # XDEBUG: setup remote debugging.
  echo 'xdebug.remote_enable=1' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.remote_handler=dbgp' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.remote_mode=req' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.remote_port=9000' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.remote_connect_back=1' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.idekey=debugme' >> /etc/php5/mods-available/xdebug.ini
  echo 'xdebug.remote_autostart=0' >> /etc/php5/mods-available/xdebug.ini
  etc-save "php: enable xdebug remote debugging."
}

# Bootstrap phase 7: tune Apache and its modules
function bootstrap_7_tune_apache {

  # Stop apache before all the surgery.
  /etc/init.d/apache2 stop

  # Disable modules we do not want to have around.
  a2dismod status cgi negotiation autoindex reqtimeout
  etc-save "apache2: disabled several unnecessary modules"

  # Enable modules that we do want to have enabled.
  a2enmod rewrite php5
  etc-save "apache2: enabling several modules that we do need."

  # Tune prefork a bit better.
  echo '' >> /etc/apache2/apache2.conf
  echo '# NIELS: TUNE PREFORK' >> /etc/apache2/apache2.conf
  echo '<IfModule mpm_prefork_module>' >> /etc/apache2/apache2.conf
  echo '  StartServers          5' >> /etc/apache2/apache2.conf
  echo '  MinSpareServers       5' >> /etc/apache2/apache2.conf
  echo '  MaxSpareServers      10' >> /etc/apache2/apache2.conf
  echo '  MaxClients          150' >> /etc/apache2/apache2.conf
  echo '  MaxRequestsPerChild   0' >> /etc/apache2/apache2.conf
  echo '</IfModule>' >> /etc/apache2/apache2.conf
  etc-save "Apache2: tuned prefork"

  # Let Apache run as the $LXC_USER user.
  chown -Rfv $LXC_USER:$LXC_USER /var/lock/apache2
  echo '' >> /etc/apache2/envvars
  echo "# NIELS: FORCE APACHE TO RUN AS $LXC_USER" >> /etc/apache2/envvars
  echo "export APACHE_RUN_USER=$LXC_USER" >> /etc/apache2/envvars
  echo "export APACHE_RUN_GROUP=$LXC_USER" >> /etc/apache2/envvars
  etc-save "apache2: run as $LXC_USER"

  # Start apache2 ordinarily.
  /etc/init.d/apache2 start

  # We are done, shut off the machine so it can reboot with all mounts in place.
  poweroff
}

# Bootstrap phase 8: install Composer and Drush globally.
function bootstrap_8_composer_drush {
  addpkg curl
  etc-save "Installed curl"

  # Install composer into /usr/local/bin.
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer

  # Install Drush and symlink to it into /usr/local/bin.
  git clone https://github.com/drush-ops/drush.git
  mv drush /usr/local
  chmod +x /usr/local/drush/drush
  ln -s /usr/local/drush/drush /usr/local/bin/drush

  # Install the Composer dependencies.
  cd /usr/local/drush
  /usr/local/bin/composer install
}

# Last bootstrap phase and repeated on every boot.
function bootstrap_ordinary_boot_rc {

  # Repeat this process on every boot, bootstrap will never end.
  return 1
}

#
# LSB compliant sysvinit behavior.
#
case "$1" in

  # Upon system start we want to go through all bootstrap stages not done yet.
  start)
    phases="1_etc_in_git_repo 2_setup_user 3_setup_dotdeb 4_install_apache_php  5_tune_server 6_tune_php 7_tune_apache ordinary_boot_rc"
    state=0
    s=1

    # If the state-file exists, retrieve the current state from there.
    if [ -f $STATEFILE ]; then
      state=`cat $STATEFILE`
    fi

    # Loop all phases and execute the callback for each of the phases.
    for phase in $phases
    do
      if [[ $s > $state ]] ;then
        cmd="bootstrap_$phase"
        echo
        echo " Bootstrapping phase: $phase ($s)"
        echo "====================================================================="
        sleep 5

        # Invoke the callback and collect the exit code.
        eval $cmd # E-codes: 0 (continue), 1 (halt+repeat), 2 (halt now, continue next time)
        exitcode=$?

        # Handle exit strategies and phase storage in the state file.
        if [ $exitcode -ne 0 ]; then
          if [ $exitcode -eq 2 ]; then
            echo "$s" > $STATEFILE
            echo "[EXIT]... exiting (phase advances next time)."
          else
            echo "[EXIT]... exiting (phase repeats next time)."
          fi
          return 0
        else
          sleep 1
          echo "$s" > $STATEFILE
          state=$(($state + 1))
        fi
      fi
      s=$(($s + 1))
    done
    ;;

  restart)
    echo "PROVISIONER RESTART"
    ;;

  force-reload)
    echo "PROVISIONER FORCE-RELOAD"
    ;;

  status)
    echo "PROVISIONER STATUS"
    ;;

  stop)
    echo "PROVISIONER STOP"
    ;;

  *)
    echo "Usage: /etc/init.d/provisioner {start|stop}"
    exit 1
    ;;
esac

exit 0
