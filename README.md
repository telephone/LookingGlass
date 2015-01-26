# Notice 2015-01-22

An RDNS XSS was disclosed which has been patched by a temporary fix (thanks [@ldrrp](https://github.com/ldrrp)). To patch, simply replace `LookingGlass/LookingGlass.php` with the patched version found here: [LookingGlass.php](https://raw.githubusercontent.com/telephone/LookingGlass/a421a8e36d548c1bf33d52e123eea5a232dfa01f/LookingGlass/LookingGlass.php)

A maintenance/security release will be issued before 2015-01-26, which will include a number of patches for v1.

# LookingGlass

## Overview

LookingGlass is a user-friendly PHP based looking glass that allows the public (via a web interface) to execute network 
commands on behalf of your server.

## Demo

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

1. Download [LookingGlass](https://github.com/downloads/telephone/LookingGlass/LookingGlass-1.2.0.zip) to the intended 
folder within your web directory (and unzip)
2. Navigate to the `LookingGlass` subdirectory in terminal
3. Run `bash configure.sh`
4. Follow the instructions and `configure.sh` will take care of the rest

_Forgot a setting? Simply run the `configure.sh` script again_

## Apache

An .htaccess is included which protects the rate-limit database, disables indexes, and disables gzip on test files.
Ensure `AllowOverride` is on for .htaccess to take effect.

Output buffering __should__ work by default.

For an HTTPS setup, please visit:
- [Mozilla SSL Configuration Generator](https://mozilla.github.io/server-side-tls/ssl-config-generator/)

## Nginx

To enable output buffering, and disable gzip on test files please refer to the provided configuration:

[HTTP setup](LookingGlass/lookingglass-http.nginx.conf)

The provided config is setup for LookingGlass to be on a subdomain/domain root.

For an HTTPS setup please visit:
- [Best nginx configuration for security](http://tautt.com/best-nginx-configuration-for-security/)
- [Mozilla SSL Configuration Generator](https://mozilla.github.io/server-side-tls/ssl-config-generator/)




## License

Code is licensed under MIT Public License.

* If you wish to support my efforts, keep the "Powered by LookingGlass" link intact.
