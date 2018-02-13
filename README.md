# Chrome Driver packaged in Composer

This is the composer dependency you want if you happen to do some Behat testing with Selenium. It 
will download the adequat chromedriver version for your dev platform, whether it's Linux 
(32bits or 64bits), OSX or Windows.

Use it like a regular composer dependency, although it's a composer plugin.

    composer require lbaey/chromedriver 1.1
    
Feel free to contribute, PR's are read and welcome

If you use se/selenium-server-standalone, run it with

    ./bin/selenium-server-standalone -Dwebdriver.chrome.driver=$PWD/bin/chromedriver
 
## Configuring Extra

If your dev platform happens to be coherent with your test platform, you can by-pass 
platform selection with:

```json
{
  "extra": {
    "lbaey/chromedriver": {
      "bypass-select": true
    }
  }
}
```

If you want a specific version of ChromeDriver, use:

```json
{
  "extra": {
    "lbaey/chromedriver": {
      "version": "2.33"
    }
  }
}
```

If you don't specify ChromeDriver version, the latest available version will be downloaded.
