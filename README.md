# TYPO3 Extension nxsimplecdn

[![stability-beta](https://img.shields.io/badge/stability-beta-33bbff.svg)](https://github.com/netlogix/nxsimplecdn)
[![TYPO3 V12](https://img.shields.io/badge/TYPO3-12-orange.svg)](https://get.typo3.org/version/12)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![GitHub CI status](https://github.com/netlogix/nxsimplecdn/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/netlogix/nxsimplecdn/actions)

# Installation

Via composer :
```shell script
composer require netlogix/nxsimplecdn
```

That's all !

# Add cdn domain to your site configuration
```yaml
base: 'https://www.example.com/'
cdnBase: 'https://cdn.example.com/'
errorHandling:
  ...
languages:
  ...
settings:
  ...
```
