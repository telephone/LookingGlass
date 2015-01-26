* 1.3.0 (2015-01-25)
  * Fix RDNS XSS
  * Fix '&nbsp;' being escaped by temporary patch (SHA a421a8e)
  * Fix 'REQUEST_URI' XSS (URL is now hard-coded via config)
  * Catch error when using IPv6 hostname with IPv4 command, and vice versa
  * Added .htaccess (fixes readable subdirectory)
  * Added sample Nginx configuration (fixes readable subdirectory)
  * GNU shred to create test files (fixes gzip and ssl compression)
  * Update configure.sh (add site url, sudo for centOS, and user:group chown)
  * Update cerulean and united to Bootstrap v2.3.2
  * Update readable and spacelab to Bootstrap v2.2.1
  * Update Jquery to v1.11.2
  * Update XMLHttpRequest.js

* 1.2.0 (2012-10-01)
  * Multiple themes
  * Rate limiting

* 1.1.0 (2012-09-24)
  * Added --report-wide to MTR
  * Fix MTR on RHEL OS'

* 1.0.0 (2012-09-23)
  * Added network commands
  * Automated install via bash script
  * Long polling via output buffering