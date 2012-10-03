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

## License

Code is licensed under MIT Public License.

* If you wish to support my efforts, keep the "Powered by LookingGlass" link intact.