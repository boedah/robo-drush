# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased][unreleased]

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

[unreleased]: https://github.com/boedah/robo-drush/compare/2.0.1...HEAD
[1.0.1]: https://github.com/boedah/robo-drush/compare/1.0.0...1.0.1
[1.0.2]: https://github.com/boedah/robo-drush/compare/1.0.1...1.0.2
[1.0.3]: https://github.com/boedah/robo-drush/compare/1.0.2...1.0.3
[2.0.0]: https://github.com/boedah/robo-drush/compare/1.0.3...2.0.0
[2.0.1]: https://github.com/boedah/robo-drush/compare/2.0.1...2.0.1
