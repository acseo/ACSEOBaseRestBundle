# ACSEOBaseRestBundle
Prodive a base REST Bundle that can be easily extended

## Purpose

This BUndle exposes a Base Rest Bundle that can be easily extended to provide access to your entities.

## Installation

1) Add the bundle to you composer.json file :
```
composer require 'acseo/base-rest-bundle:dev-master'
```

2) Enable the Bundle
```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
        //...
        new ACSEO\BaseRestBundle\ACSEOCBaseRestdBundle(),
        //...
```

TODO : doc.
