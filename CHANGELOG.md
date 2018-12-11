# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased][unreleased]

## [4.2.0] - 2018-12-11

### Added

- `existingConfig` method for site installs (which sets `--existing-config` in the drush call)

## [4.1.0] - 2018-09-11

### Added

- possibility to test multiple drush versions

## [4.0.0] - 2018-09-11

Since people might have shell escaped these parameters already,
this version is a new major as it is potentially backwards incompatible. 

### Changed

- `accountPass` and `dbSuPw` in site install command are now shell escaped 

## [3.1.1] - 2017-08-21

### Added

- support for the upcoming Drush 9 (only use `drush version` to get the version)

## [3.1.0] - 2017-07-05

### Added

- support for `config-dir` property

## [3.0.2] - 2017-02-01

### Changed

- consolidation/robo dependency to `~1`

## [2.2.1] - 2015-11-27

### Changed

- Robo dependency to `>=0.5.2`, which means all future versions. As Robo is in pre-1.0 stage, this seems to make sense.

## [2.2.0] - 2015-09-20

### Added

- second parameter `assumeYes` (default `true`) to `exec()`

## [2.1.0] - 2015-02-27

This version is the first one working without strict errors using Robo >=0.5.2.

### Added

- Robo dependency to `~0.5.2` (and removed `conflict` section)

### Fixed

- PSR-4 autoloading (renamed `Drush.php` to `DrushStack.php`
- PHPUnit deprecation warning

## [2.0.2] - 2015-02-21

You have to use `"codegyre/robo": "dev-master"` in your composer.json,
since there is no new release which includes https://github.com/Codegyre/Robo/pull/114.

### Fixed

- strict warning about 'same property'

## [2.0.1] - 2015-01-25

### Fixed

- Robo version in `conflict` section of composer.json

## [2.0.0] - 2015-01-25

Release for Robo >=0.5 (not compatible with 0.4.*!).

### Changed

- Trait name to `\Boedah\Robo\Task\Drush\loadTasks` in line with Robo tasks
- Task name from `DrushStackTask` to `DrushStack`

## [1.0.3] - 2015-01-25

### Added

- `conflict` section in composer.json

## [1.0.2] - 2015-01-25

### Added
- This [change log](CHANGELOG.md) following [keepachangelog.com](http://keepachangelog.com/).
- Installation instructions to [README](README.md).

### Changed
- Robo version in composer dev dependencies is now 0.4.5, as 0.4.6. introduced a BC break.<br>
  Code will be updated soon to be compatible with Robo 0.5.

### Fixed
- phpdoc of `getVersion()`

## [1.0.1] - 2014-06-20

### Fixed
- Drush version is now fetched correctly in `updateDb()`

## 1.0.0 - 2014-06-06

### Added
- Initial commit

[unreleased]: https://github.com/boedah/robo-drush/compare/4.2.0...HEAD
[1.0.1]: https://github.com/boedah/robo-drush/compare/1.0.0...1.0.1
[1.0.2]: https://github.com/boedah/robo-drush/compare/1.0.1...1.0.2
[1.0.3]: https://github.com/boedah/robo-drush/compare/1.0.2...1.0.3
[2.0.0]: https://github.com/boedah/robo-drush/compare/1.0.3...2.0.0
[2.0.1]: https://github.com/boedah/robo-drush/compare/2.0.0...2.0.1
[2.0.2]: https://github.com/boedah/robo-drush/compare/2.0.1...2.0.2
[2.1.0]: https://github.com/boedah/robo-drush/compare/2.0.2...2.1.0
[2.2.0]: https://github.com/boedah/robo-drush/compare/2.1.0...2.2.0
[2.2.1]: https://github.com/boedah/robo-drush/compare/2.2.0...2.2.1
[3.0.2]: https://github.com/boedah/robo-drush/compare/2.2.1...3.0.2
[3.1.0]: https://github.com/boedah/robo-drush/compare/3.0.2...3.1.0
[3.1.1]: https://github.com/boedah/robo-drush/compare/3.1.0...3.1.1
[4.0.0]: https://github.com/boedah/robo-drush/compare/3.1.1...4.0.0
[4.1.0]: https://github.com/boedah/robo-drush/compare/4.0.0...4.1.0
[4.2.0]: https://github.com/boedah/robo-drush/compare/4.1.0...4.2.0
