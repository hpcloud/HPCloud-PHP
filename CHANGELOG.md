# Release Notes

This changelog contains the relevant feature additions and bug fixes. To obtain a complete diff between versions you can got to https://github.com/hpcloud/HPCloud-PHP/compare/XXX...XXX where the XXX values are two different tagged versions of the library. For example, https://github.com/hpcloud/HPCloud-PHP/compare/1.0.0-beta6...1.0.0

* X.X.X (xxxx-xx-xx)

  * Removed DBaaS support. The support was for a private beta version with a now deprecated API.
  * Updated the documentation surrounding CDN and access/secret keys with HP Helion OpenStack and HP Helion Public Cloud.
  * #14: Fixed bug where transport.debug was ignored by the PHP Stream transport layer.
  * #13: Fixed, we think, a bug where setting the content length while using curl caused an issue with proxies.
  * #3: Fixed a bug where the curl transport layer failed under Windows and newer version of Linux and OS X.
  * #15: Fixed issue where posix_geteuid was being called, even in windows.
  * #13: Fixed issue where temp directory was assumed to be tmp which fails on Windows.

* 1.2.1 (2013-09-26)

  * Disabling HTTP 1.1 keep-alive for the PHP transport layer. PHP doesn't support keep alive.
  * Fixed CDN::newFromIdentity to pass on the region to enable multi-region support.
  * Updated the CREDITS and contact information for Matt Butcher.

* 1.2.0

  * ObjectStorage::newFromIdentity now works for multiple regions.
  * Added classes for DBaaS Flavor and FlavorDetails.
  * Fixed DBaaS Instance creation to use the new flavor setup.

* 1.1.0

  * DBaaS::newFromIdentity was modified to support the new base URL
    format.
  * All newFromIdentity() constructor functions now support $region
    settings.
  * Proxy configuration has been added to CURLTransport.
  * Fixed autoloader for Windows.

* 1.0.0 (2012-08-09)

  * This is the initial stable release for object storage, CDN, and identity services. DBaaS is currently in private beta as such the bindings for this component are still in beta.
