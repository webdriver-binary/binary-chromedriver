# binary-chromedriver

[![Latest Stable Version](https://poser.pugx.org/webdriver-binary/binary-chromedriver/v/stable)](https://packagist.org/packages/webdriver-binary/binary-chromedriver)
[![Total Downloads](https://poser.pugx.org/webdriver-binary/binary-chromedriver/downloads)](https://packagist.org/packages/webdriver-binary/binary-chromedriver)
[![Daily Downloads](https://poser.pugx.org/webdriver-binary/binary-chromedriver/d/daily)](https://packagist.org/packages/webdriver-binary/binary-chromedriver)
[![License](https://poser.pugx.org/webdriver-binary/binary-chromedriver/license)](https://packagist.org/packages/webdriver-binary/binary-chromedriver)

Downloads chromedriver binary for Linux (32bits or 64bits), macOS (Mac OS X) and Windows.

The binary version is determined by the following factors:

* what browser version is currently installed (if binary found from the system).
* specified/configured version (see below under 'Configuration' topic).
* latest available version (polled from remote, official end-point).

## Configuration

Although the binary downloader usually ends up positively detecting the appropriate 
driver version that needs to be downloaded, user still has an option to specify the 
version explicitly when needed.

```json
{
  "extra": {
    "chromedriver": {
      "version": "2.33"
    }
  }
}
```

