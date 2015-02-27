# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased][unreleased]

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

[unreleased]: https://github.com/boedah/robo-drush/compare/2.1.0...HEAD
[1.0.1]: https://github.com/boedah/robo-drush/compare/1.0.0...1.0.1
[1.0.2]: https://github.com/boedah/robo-drush/compare/1.0.1...1.0.2
[1.0.3]: https://github.com/boedah/robo-drush/compare/1.0.2...1.0.3
[2.0.0]: https://github.com/boedah/robo-drush/compare/1.0.3...2.0.0
[2.0.1]: https://github.com/boedah/robo-drush/compare/2.0.0...2.0.1
[2.0.2]: https://github.com/boedah/robo-drush/compare/2.0.1...2.0.2
[2.1.0]: https://github.com/boedah/robo-drush/compare/2.0.2...2.1.0
