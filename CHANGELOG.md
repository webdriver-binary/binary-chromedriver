# Changelog

_This file has been auto-generated from the contents of changelog.json_

## 5.0.2 (2019-05-02)

### Fix

* driver version map updated to make sure wrong (newest) driver does not get downloaded for some relatively new (but not the newest) Chrome releases (versions: 70, 71)

### Maintenance

* introduced the use of Static Code Analysis tools
* code downgraded so to make the package installable on relatively old php versions


## 5.0.1 (2018-12-12)

### Fix

* bad unescaped path configured for browser binary for Windows which resulted in version polling not working


## 5.0.0 (2018-12-12)

### Breaking

* (code) started to rely on external general-issue library for most of the implementation; only configuration remains in this package

### Feature

* support for browser version detection added for Windows


## 4.0.1 (2018-12-05)

### Fix

* bad constant names used for config value references in code which caused latest version detection to fail


## 4.0.0 (2018-12-05)

### Breaking

* config: old config value (chromedriver-version) no longer supported/used; only extra/chromedrier/version respected
* code: use built-in Composer downloader and extractor logic instead of relying on a custom one

### Maintenance

* everything that relates to downloading and installing the driver now centrally configured in plugin configuration (rather than being hard-coded all over the place)


## 3.0.0 (2018-12-04)

### Breaking

* namespace changed to be under Vaimo
* class properties and functions switched from 'protected' to 'private' (no real need to keep them exposed)
* plugin class split in two (only directly plugin-bootstrapping related remain in Plugin class)

### Feature

* added information about what this package is replacing


## 2.36.0 (2018-12-03)

### Feature

* choose chromeDriver version based on installed Chrome Browser version (if it's available)