# binary-chromedriver

This is the composer dependency you want if you happen to do some Behat testing with Selenium. It 
will download the adequat chromedriver version for your dev platform, whether it's Linux 
(32bits or 64bits), OSX or Windows.

By default, latest version of the Chrome Driver will be downloaded
    
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
