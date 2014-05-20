# Drupal LookingGlass Module

## Overview

LookingGlass is a user-friendly PHP based looking glass that allows the public (via a web interface) to execute network 
commands on behalf of your server.

This fork is a simple Drupal module wrapper for this project.

This is not to be confused with the Drupal Nagios Looking Glass Module. That is a completely unrelated project.

To install:

cd to /sites/all/modules/ 

git clone  

Enable in Drupal Modules  

Create the Config.php file by running /sites/all/modules/LookingGlass/LookingGlass/configure.sh

Browse to http://mydomain.com/LookingGlass
 
There are no Drupal bells and whistles such as admin configuration and block functions.  Only the minimum modifications required to get it to work as a Drupal module. Those other things should be easy to add if you want to do that.


## Demo of original Non-Drupal version

[LookingGlass](http://lg.iamtelephone.com)

The demo is hosted on a 50MB (RAM) VPS. 502 errors may occur in events of high use.

## Features

* Automated install via bash script
* IPv4 & IPv6 support
* Live output via long polling
* Multiple themes
* Rate limiting of network commands

## Implemented commands

* host
* mtr
* mtr6 (IPv6)
* ping
* ping6 (IPv6)
* traceroute
* traceroute6 (IPv6)

__IPv6 commands will only work if your server has external IPv6 setup (or tunneled)__

## Requirements

* PHP >= 5.3
* PHP PDO
* SSH/Terminal access (able to install commands/functions if non-existent)

## Install

2. Navigate to the `LookingGlass` subdirectory in terminal
3. Run `bash configure.sh`
4. Follow the instructions and `configure.sh` will take care of the rest

_Forgot a setting? Simply run the `configure.sh` script again_



## License

Code is licensed under MIT Public License.

* If you wish to support my efforts, keep the "Powered by LookingGlass" link intact.
