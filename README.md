# binary-chromedriver

This is the composer dependency you want if you happen to do some Behat testing with Selenium. It 
will download the correct chromedriver version for your dev platform, whether it's Linux 
(32bits or 64bits), OSX or Windows.

By default either appropriate version (that matches with installed browser) or latest version of the 
driver will be downloaded.
    
## Configuring Extra

If you want a specific version of ChromeDriver, use:

```json
{
  "extra": {
    "chromedriver": {
      "version": "2.33"
    }
  }
}
```

If you don't specify ChromeDriver version, either appropriate version (that matches with installed 
browser) or latest version of the driver will be downloaded.
