# LookingGlass

## Overview

LookingGlass is a user-friendly PHP based looking glass that allows the public (via a web interface) to execute network 
commands on behalf of your server. It also features live output (long polling) of the network commands!

## Demo

[LookingGlass](http://lg.iamtelephone.com)

The demo is currently hosted on a 50MB VPS. 502 errors may occur in events of high use.

## Implemented commands

* host
* mtr
* mtr6 (IPv6)
* ping
* ping6 (IPv6)
* traceroute
* traceroute6 (IPv6)

_IPv6 commands will only work if your server has external IPv6 setup (or tunneled)_

## Requirements

* PHP >= 5.3
* SSH/Terminal access (able to install commands/functions if non-existent)

## Install

1. Download LookingGlass to the intended folder within your web directory
2. Navigate to the `LookingGlass` subdirectory in terminal
3. Run `bash configure.sh`
4. Follow the instructions and `configure.sh` will take care of the rest

_Forgot a setting? Simply run the `configure.sh` script again_

## Nginx

To enable output buffering on Nginx, please append the following to your PHP configuration:

```nginx
location ~ \.php$ {
    ...

    # Append the following
    fastcgi_buffer_size   1k;
    fastcgi_buffers       128 1k;
    fastcgi_max_temp_file_size 0;
    gzip off;
}
```

I recommend that you create a separate host file for LookingGlass OR a directory specific PHP "location". This is due 
to these settings not being optimal for conventional use.

## To-do

* Implement abuse protection
* Create a non-JS reliant page (index2.php)

## Quirks

* If you use IPv6, your IPv6 address must be entered everytime `configure.sh` is run
* Hostnames with only IPv4 will pass validation for IPv6 (it will display as an empty result)
* All test files are destroyed/recreated while running `configure.sh`
* Safari renders the select dropdown without an arrow

## License

Code is licensed under MIT Public License.

* Please keep the "Powered by LookingGlass" link intact to promote this script.