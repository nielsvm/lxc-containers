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

  # We are done, shut off the machine so it can reboot with all mounts in place.
  poweroff
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
    phases="1_etc_in_git_repo 2_setup_user ordinary_boot_rc"
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
