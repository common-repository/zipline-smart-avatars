# Plugin Starter Usage

## Replace plugin name and identifiers

Replace plugin name, identifiers, translation domain, package names, description etc. Search and replace can be used for
most.

* `zl-smart-avatars` is used for identifiers, translation domain, package name.
* `Zipline Smart Avatars` is used for the plugin name.
* `A plugin to choose users avatars.` is used for descriptions.

Example locations:
* bower.json
* package.json
* README.md

## Rename entry file.

Rename file `zl-smart-avatars.php` to relevant file name. e.g. `my-awesome-plugin.php`.

## Set plugin function shortname

Replace function short name `zl_smart_avatars` in `shortcut-function.php`.

## composer.json

* Set `config.autoloader-suffix`
* Set `autoload.classmap`, `autoload.psr-4`

## gulp.config.js

* Set `version.entryFile`
* Set `translations.textDomain`, `translations.outputFile`
* Set `build.release.filename`

## Set correct namespace

Replace all namespaces in plugin e.g. from `Zipline\ZLSmartAvatars` to `Zipline\MyAwesomePlugin`.

## Update filters and action names

Replace `zl_smart_avatars`.

## Update tests

* Rename `_manually_load_zl_smart_avatars_plugin` function and update `tests_add_filter` in `tests/bootsrap.php`
* Update phpdoc, rename class and assertions in `tests/test-base.php`

## Update Autoload

* Run `composer dump-autoload`

## Update Install Requirements

* Update `meets_requirements()` function.

