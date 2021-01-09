[![Build Status](https://jenkins.scoutnet.eu/buildStatus/icon?job=scoutnet/plugins.typo3.sh_scoutnet_kalender/master)](https://jenkins.scoutnet.eu/job/scoutnet/job/plugins.typo3.sh_scoutnet_kalender/job/master/)
[![Packagist](https://img.shields.io/packagist/v/scoutnet/sh-scoutnet-kalender.svg)](https://packagist.org/packages/scoutnet/sh-scoutnet-kalender)
[![Packagist](https://img.shields.io/packagist/dt/scoutnet/sh-scoutnet-kalender.svg?label=packagist%20downloads)](https://packagist.org/packages/scoutnet/sh-scoutnet-kalender)
[![Packagist](https://img.shields.io/packagist/l/scoutnet/sh-scoutnet-kalender.svg)](https://packagist.org/packages/scoutnet/sh-scoutnet-kalender)
---
# ScoutNet Kalender Plugin for TYPO3

This is the official ScoutNet.de Kalender Plugin for TYPO3. If you are a scouting Group from Germany you can use our Service and manage your events on our Servers.

## Installation
To install You can either use the version from the TER, or install this git repo to 

<TYPO3 Dir>/typo3conf/ext/sh_scoutnet_kalender

For the Kalender Plugin to work, you need the sh_scoutnet_webservice extension in a Version > 4.0.

alternatively you can use composer:

`composer require scoutnet/sh-scoutnet-kalender`


### Setup
For the Backend function to work, you have to set your SSID (the id of your Scouting Group) in the extension.

For the Frontend to work, you need to include the static file into your template. Then you can add a new content Element. 
There you can choose which ssids to be shown, which additional calenders to show and which kategories you want to display.

If you want to use nice looking URLs, you should include the Routes Script into your Site Configuration
```yaml
imports:
  - { resource: "EXT:sh_scoutnet_kalender/Configuration/Routes/Default.yaml" }
```

## Development
If you want to contribute, feel free to do so. The Repo is located here:

https://github.com/scoutnet/plugins.typo3.sh_scoutnet_kalender

just run `make composerInstall`

### Testing

Needed: GnuMake, PHP, Docker and docker-compose

Init:

`make init`

To Run all the Tests call:

`make test`

you can use the -phpx suffix to indicate which php version you want to check e.g. `make test-php73`

for only testing a special function or php version there are different suffixes. For Example:

- `make lintTest-php73`
- `make unitTest-php74`
- `make unitTest`        Will call Unit tests with php7.3 and php 7.4

Testing with PhpStorm: Setup new remote PHP interpreter.
Docker-Compose:
 - compose file: `Tests/Build/docker-compose.yml`
 - service: ` functional_mariadb`
 
Set up new Test Framework:
 - path to phpunit: `bin/phpunit`
 - default config: `vendor/typo3/testing-framework/Resources/Core/Build/UnitTests.xml`
 - add path mappings: `<abs. Path to this dir>` -> `<abs. Path to this dir>` (all paths mapped like on your host)
 
Set up new Run Configuration for `Unit Tests`:
 - Test Scope: `<abs. Path to this dir>/Tests/Unit`
 - Custom Working Directory: `<abs. Path to this dir>/.Build/`
 
Set up new Run Configuration for `Functional Tests`:
 - Test Scope: `<abs. Path to this dir>/Tests/Functional`
 - Custom Working Directory: `<abs. Path to this dir>/.Build/`
 - Use alternative configuration File: `<aps. Path to this dir>/.Build/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml`
 - Environment variables: `typo3DatabaseUsername=root;typo3DatabasePassword=funcp;typo3DatabaseHost=mariadb10;typo3DatabaseName=func_test`
 
Happy Testing

### Update
#### 2.x->3.0
If you update from a Version < 3.0 please note, that the whole extension was rewritten. And is now based on Extbase. 
You need to resetup the frontend Plugin and all templates do not work anymore.

You can easily change the CSS with the constant editor. If you want to change the Templates, please set how to change templates with extbase.
The be_user database fields are changed, so you need to update your database. And reconnect all backend accounts. But this should be done by the 
Backend users themself.

#### 4.x->5.0


### Author
If you have any questions regarding this software, you can send me an email to muetze@scoutnet.de

### TODO


### License
(c) 2020 Stefan "Mütze" Horst <muetze@scoutnet.de>
All rights reserved

This script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This copyright notice MUST APPEAR in all copies of the script!
