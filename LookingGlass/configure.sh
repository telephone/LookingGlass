#!/bin/bash
################################
# LookingGlass - User friendly PHP Looking Glass
#
# package     LookingGlass
# author      Nick Adams <nick@iamtelephone.com>
# copyright   2015 Nick Adams.
# link        http://iamtelephone.com
# license     http://opensource.org/licenses/MIT MIT License
# version     1.3.0
################################

#######################
##                   ##
##     Functions     ##
##                   ##
#######################

##
# Create Config.php
##
function createConfig()
{
  cat > "$DIR/$CONFIG" <<EOF
<?php
/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick@iamtelephone.com>
 * @copyright   2015 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 * @version     1.3.0
 */

// IPv4 address
\$ipv4 = '${IPV4}';
// IPv6 address (can be blank)
\$ipv6 = '${IPV6}';
// Rate limit
\$rateLimit = (int) '${RATELIMIT}';
// Site name (header)
\$siteName = '${SITE}';
// Site URL
\$siteUrl = '${URL}';
// Server location
\$serverLocation = '${LOCATION}';
// Test files
\$testFiles = array();
EOF

  for i in "${TEST[@]}"; do
    echo "\$testFiles[] = '${i}';" >> "$DIR/$CONFIG"
  done
  echo -e "// Theme\n\$theme = '${THEME}';" >> "$DIR/$CONFIG"

  sleep 1
}

##
# Create/Load config varialbes
##
function config()
{
  sleep 1
  # Check if previous config exists
  if [ ! -f $CONFIG ]; then
    # Create config file
    echo 'Creating Config.php...'
    echo ' ' > "$DIR/$CONFIG"
  else
    echo 'Loading Config.php...'
  fi

  sleep 1

  # Read Config line by line
  while IFS="=" read -r f1 f2 || [ -n "$f1" ]; do
    # Read variables
    if [ "$(echo $f1 | head -c 1)" = '$' ]; then
      # Set Variables
      if [ $f1 = '$ipv4' ]; then
        IPV4="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$ipv6' ]; then
        IPV6="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$rateLimit' ]; then
        RATELIMIT=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$serverLocation' ]; then
        LOCATION="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$siteName' ]; then
        SITE=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$siteUrl' ]; then
        URL=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$testFiles[]' ]; then
        TEST+=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$theme' ]; then
        THEME="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      fi
    fi
  done < "$DIR/$CONFIG"
}

##
# Create SQLite database
##
function database()
{
    if [ ! -f "${DIR}/ratelimit.db" ]; then
      echo
      echo 'Creating SQLite database...'
      sqlite3 ratelimit.db  'CREATE TABLE RateLimit (ip TEXT UNIQUE NOT NULL, hits INTEGER NOT NULL DEFAULT 0, accessed INTEGER NOT NULL);'
      sqlite3 ratelimit.db 'CREATE UNIQUE INDEX "RateLimit_ip" ON "RateLimit" ("ip");'
      read -e -p 'Enter the username of your webserver (E.g. www-data): ' USER
      read -e -p 'Enter the user group of your webserver (E.g. www-data): ' GROUP
      # Change owner of folder & DB
      if [[ -n $USER ]]; then
          if [[ -n $GROUP ]]; then
            chown $USER:$GROUP "${DIR}"
            chown $USER:$GROUP ratelimit.db
          else
            chown $USER:$USER "${DIR}"
            chown $USER:$USER ratelimit.db
          fi
      else
        cat <<EOF

##### IMPORTANT #####
Please set the owner of LookingGlass (subdirectory) and ratelimit.db
to that of your webserver:
chown user:group LookingGlass
chown user:group ratelimit.db
#####################
EOF
      fi
    fi
}

##
# Fix MTR on REHL based OS
##
function mtrFix()
{
  # Check permissions for MTR & Symbolic link
  if [ $(stat --format="%a" /usr/sbin/mtr) -ne 4755 ] || [ ! -f "/usr/bin/mtr" ]; then
    if [ $(id -u) = "0" ]; then
      echo 'Fixing MTR permissions...'
      chmod 4755 /usr/sbin/mtr
      ln -s /usr/sbin/mtr /usr/bin/mtr
    else
      cat <<EOF

##### IMPORTANT #####
You are not root. Please log into root and run:
chmod 4755 /usr/sbin/mtr
ln -s /usr/sbin/mtr /usr/bin/mtr
#####################
EOF
    fi
  fi
}

##
# Check and install script requirements
##
function requirements()
{
  sleep 1
  # Check for apt-get/yum
  if [ -f /usr/bin/apt-get ]; then
    # Check for root
    if [ $(id -u) != "0" ]; then
      INSTALL='sudo apt-get'
    else
      INSTALL='apt-get'
    fi
  elif [ -f /usr/bin/yum ]; then
    # Check for root
    if [ $(id -u) != "0" ]; then
      INSTALL='sudo yum'
    else
      INSTALL='yum'
    fi
  else
    cat <<EOF

##### IMPORTANT #####
Unknown Operating system. Install dependencies manually:
host mtr iputils-ping traceroute sqlite3
#####################
EOF
    return
  fi

  # Array of required functions
  local REQUIRE=(host mtr iputils-ping traceroute sqlite3)

  # Loop through required & install
  for i in "${REQUIRE[@]}"; do
    # Fix host for CentOS
    if [ $i = 'host' ]; then
      echo 'Checking for host...'
      if [ ! -f "/usr/bin/$i" ]; then
        if [ $INSTALL = 'yum' ]; then
          ${INSTALL} -y install "bind-utils"
        else
          ${INSTALL} -y install ${i}
        fi
        echo
      fi
    # Fix ping
    elif [ $i = 'iputils-ping' ]; then
      echo 'Checking for ping...'
      if [ ! -f "/bin/ping" ]; then
        ${INSTALL} -y install ${i}
        echo
      fi
    # Check both bin and sbin
    elif [ $i = 'traceroute' ]; then
      echo "Checking for $i..."
      if [ ! -f "/usr/bin/$i" ]; then
        if [ ! -f "/usr/sbin/$i" ]; then
          ${INSTALL} -y install ${i}
          echo
        fi
      fi
    else
      echo "Checking for $i..."
      if [ ! -f "/usr/bin/$i" ]; then
        ${INSTALL} -y install ${i}
        echo
      fi
    fi
    sleep 1
  done
}

##
# Setup parameters for PHP file creation
##
function setup()
{
  sleep 1

  # Local vars
  local IP4=
  local IP6=
  local LOC=
  local S=
  local T=
  local U=

  # User input
  read -e -p "Enter your website name (Header/Logo) [${SITE}]: " S
  read -e -p "Enter the public URL to this LG (including http://) [${URL}]: " U
  read -e -p "Enter the servers location [${LOCATION}]: " LOC
  read -e -p "Enter the test IPv4 address [${IPV4}]: " IP4
  read -e -p "Enter the test IPv6 address (Re-enter everytime this script is run) [${IPV6}]: " IP6
  read -e -p "Enter the size of test files in MB (Example: 25MB 50MB 100MB) [${TEST[*]}]: " T
  read -e -p "Do you wish to enable rate limiting of network commands? (y/n): " RATE

  # Check local vars aren't empty; Set new values
  if [[ -n $IP4 ]]; then
    IPV4=$IP4
  fi
  # IPv6 can be left blank
  IPV6=$IP6
  if [[ -n $LOC ]]; then
    LOCATION=$LOC
  fi
  if [[ -n $S ]]; then
    SITE=$S
  fi
  if [[ -n $U ]]; then
    URL=$U
  fi
  # Rate limit
  if [[ "$RATE" = 'y' ]] || [[ "$RATE" = 'yes' ]]; then
    read -e -p "Enter the # of commands allowed per hour (per IP) [${RATELIMIT}]: " RATE
    if [[ -n $RATE ]]; then
      if [ "$RATE" != "$RATELIMIT" ]; then
        RATELIMIT=$RATE
      fi
    fi
  else
    RATELIMIT=0
  fi
  # Create test files
  if [[ -n $T ]]; then
    echo
    echo 'Removing old test files:'
    # Delete old test files
    local REMOVE=($(ls ../*.test 2>/dev/null))
    for i in "${REMOVE[@]}"; do
      if [ -f "${i}" ]; then
        echo "Removing ${i}"
        rm "${i}"
        sleep 1
      fi
    done
    TEST=($T)
    echo
    echo 'Creating new test files:'
    # Create new test files
    testFiles
  fi
}

##
# Create test files
##
function testFiles()
{
  sleep 1

  # Local var/s
  local A=0

  # Check for and/or create test file
  for i in "${TEST[@]}"; do
    if [[ -n i ]] && [ ! -f "../${i}.test" ]; then
      echo "Creating $i test file"
      shred --exact --iterations=1 --size="${i}" - > "../${i}.test"
      A=$((A+1))
      sleep 1
    fi
  done

  # No test files were created
  if [ $A = 0 ]; then
    echo 'Test files already exist...'
  fi
}

##
# Choose default theme
##
function defaultTheme()
{
  # Set default theme
  if [[ "$THEME" = '' ]]; then
    THEME='cerulean'
  fi

  # Change theme
  read -e -p "Would you like to choose a different theme? (y/n): " NEWTHEME
  if [[ "$NEWTHEME" = 'y' ]] || [[ "$NEWTHEME" = 'yes' ]]; then
    cat <<EOF

#########################################
#
# Themes:
#
# 1) cerulean
# 2) readable
# 3) spacelab
# 4) united
#
# Demo: http://lg.iamtelephone.com/themes
#
#########################################

EOF
  MATCH=
  while [[ -z $MATCH ]]; do
    themeChange
  done
  fi
}

##
# Loop to change theme
##
function themeChange()
{
  read -e -p "Enter the name of the theme (case sensitive) [${THEME}]: " NEWTHEME

  if [[ -n $NEWTHEME ]]; then
    # Check for valid theme
    VALID=(cerulean readable spacelab united)
    MATCH=$(echo "${VALID[@]:0}" | grep -o $NEWTHEME)
    if [[ ! -z $MATCH ]]; then
      THEME=$NEWTHEME
    fi
  else
    MATCH=' '
  fi
}


###########################
##                       ##
##     Configuration     ##
##                       ##
###########################

# Clear terminal
clear

# Welcome message
cat <<EOF
########################################
#
# LookingGlass is a user-friendly script
# to create a functional Looking Glass
# for your network.
#
# Created by Nick Adams (telephone)
# http://iamtelephone.com
#
########################################

EOF

read -e -p "Do you wish to install LookingGlass? (y/n): " ANSWER

if [[ "$ANSWER" = 'y' ]] || [[ "$ANSWER" = 'yes' ]]; then
  cat <<EOF

###              ###
# Starting install #
###              ###

EOF
  sleep 1
else
  echo 'Installation stopped :('
  echo
  exit
fi

# Global vars
CONFIG='Config.php'
DIR="$(cd "$(dirname "$0")" && pwd)"
IPV4=
IPV6=
LOCATION=
RATELIMIT=
SITE=
URL=
TEST=()
THEME=

# Install required scripts
echo 'Checking script requirements:'
requirements
echo
# Read Config file
echo 'Checking for previous config:'
config
echo
# Create test files
echo 'Creating test files:'
testFiles
echo
# Follow setup
cat <<EOF

###                    ###
# Starting configuration #
###                    ###

EOF
echo 'Running setup:'
setup
echo
# Theme
defaultTheme
echo
# Create Config.php file
echo 'Creating Config.php...'
createConfig
# Create DB
database
# Check for RHEL mtr
if [ "$INSTALL" = 'yum' ]; then
  mtrFix
fi
# All done
cat <<EOF

Installation is complete

EOF
sleep 1
