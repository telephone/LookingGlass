#!/bin/bash
################################
# MIT License
# ===========
#
# Copyright (c) 2012 Nick Adams <nick89@zoho.com>
#
# Permission is hereby granted, free of charge, to any person obtaining
# a copy of this software and associated documentation files (the
# "Software"), to deal in the Software without restriction, including
# without limitation the rights to use, copy, modify, merge, publish,
# distribute, sublicense, and/or sell copies of the Software, and to
# permit persons to whom the Software is furnished to do so, subject to
# the following conditions:
#
# The above copyright notice and this permission notice shall be included
# in all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
# OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
# IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
# CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
# TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
# SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# package     LookingGlass
# author      Nick Adams <nick89@zoho.com>
# copyright   2012 Nick Adams.
# link        http://iamtelephone.com
# version     1.1.0
################################

#######################
##                   ##
##     Functions     ##
##                   ##
#######################

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
    INSTALL='yum'
  else
    echo 'Skipping script requirements.'
    return
  fi

  # Array of required functions
  REQUIRE=( host iputils-ping mtr traceroute )

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
        echo ''
      fi
    # Fix ping
    elif [ $i = 'iputils-ping' ]; then
      echo 'Checking for ping...'
      if [ ! -f "/bin/ping" ]; then
        ${INSTALL} -y install ${i}
        echo ''
      fi
    # Check both bin and sbin
    elif [ $i = 'traceroute' ]; then
      echo "Checking for $i..."
      if [ ! -f "/usr/bin/$i" ]; then
        if [ ! -f "/usr/sbin/$i" ]; then
          ${INSTALL} -y install ${i}
          echo ''
        fi
      fi
    else
      echo "Checking for $i..."
      if [ ! -f "/usr/bin/$i" ]; then
        ${INSTALL} -y install ${i}
        echo ''
      fi
    fi
    sleep 1
  done
}

##
# Create Config.php
##
function createConfig()
{
  cat > "$DIR/$CONFIG" <<EOF
<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 Nick Adams <nick89@zoho.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick89@zoho.com>
 * @copyright   2012 Nick Adams.
 * @link        http://iamtelephone.com
 * @version     1.1.0
 */

// IPv4 address
\$ipv4 = '${IPV4}';
// IPv6 address (can be blank)
\$ipv6 = '${IPV6}';
// Site name (header)
\$siteName = '${SITE}';
// Server location
\$serverLocation = '${LOCATION}';
// Test files
\$testFiles = array();
EOF

  for i in "${TEST[@]}"; do
    echo "\$testFiles[] = '${i}';" >> "$DIR/$CONFIG"
  done

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
      elif [ $f1 = '$serverLocation' ]; then
        LOCATION="$(echo $f2 | awk -F\' '{print $(NF-1)}')"
      elif [ $f1 = '$testFiles[]' ]; then
        TEST+=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      elif [ $f1 = '$siteName' ]; then
        SITE=("$(echo $f2 | awk -F\' '{print $(NF-1)}')")
      fi
    fi
  done < "$DIR/$CONFIG"
}

##
# Setup parameters for PHP file creation
##
function setup()
{
  sleep 1

  # Local vars
  local IP4=''
  local IP6=''
  local LOC=''
  local T=''
  local S=''

  # User input
  read -p "Enter your website name (Header/Logo) [${SITE}]: " S
  read -p "Enter the servers location [${LOCATION}]: " LOC
  read -p "Enter the test IPv4 address [${IPV4}]: " IP4
  read -p "Enter the test IPv6 address (Re-enter everytime this script is run) [${IPV6}]: " IP6
  read -p "Enter the size of test files in MB (Example: 25MB 50MB 100MB) [${TEST[*]}]: " T

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
  if [[ -n $T ]]; then
    echo ''
    echo 'Removing old sparse files:'
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
    echo ''
    echo 'Creating new sparse files:'
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
      dd if=/dev/zero of="../${i}.test" bs=1 count=0 seek=${i} >/dev/null 2>&1
      A=$((A+1))
      sleep 1
    fi
  done

  # No sparse files were created
  if [ $A = 0 ]; then
    echo 'Sparse files already exist...'
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
      echo '##### IMPORTANT #####'
      echo 'You are not root. Please log into root and run:'
      echo 'chmod 4755 /usr/sbin/mtr'
      echo 'and'
      echo 'ln -s /usr/sbin/mtr /usr/bin/mtr'
      echo '#####################'
    fi
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
# http://github.com/telephone
#
########################################

EOF

read -p "Do you wish to install LookingGlass? (y/n): " ANSWER

if [[ "$ANSWER" = 'y' ]] || [[ "$ANSWER" = 'yes' ]]; then
  cat <<EOF

###              ###
# Starting install #
###              ###

EOF
  sleep 1
else
  echo 'Installation stopped :('
  echo ''
  exit
fi

# Global vars
CONFIG='Config.php'
DIR="$(cd "$(dirname "$0")" && pwd)"
IPV4=''
IPV6=''
LOCATION=''
TEST=()
SITE=''

# Install required scripts
echo 'Checking script requirements:'
requirements
echo ''
# Read Config file
echo 'Checking for previous config:'
config
echo ''
# Create test files
echo 'Creating sparse test files:'
testFiles
echo ''
# Follow setup
cat <<EOF

###                    ###
# Starting configuration #
###                    ###

EOF
echo 'Running setup:'
setup
echo ''
# Create Config.php file
echo 'Creating Config.php'
createConfig

# All done
cat <<EOF

Installation is complete...

EOF

# Check for RHEL mtr
if [ "$INSTALL" = 'yum' ]; then
  mtrFix
  echo ''
fi