# Changelog

All Notable changes to `plug` will be documented in this file.

## [0.1.6] - 2016-03-21

### Added
- SelfValidating trait can fix rules for update query.
- StyleCI CS Fixes

### Fixed
- Removed accidentally left extends `Illuminate\Database\Eloquent\Model` from `FixBelongsTo` and `FixMorphTo`.

## [0.1.5] - 2016-03-17

### Removed
- Removed `$incrementing` field from `UuidKey`. **Set this `protected $incrementing = false;` when using `UuidKey`.**

## [0.1.4] - 2016-03-17

### Fixed
- Removed accidentally left extends `Illuminate\Database\Eloquent\Model` from `FixBelongsTo`.

## [0.1.3] - 2016-03-17

### Fixed
- Changed `class FixBelongsTo` to `trait FixBelongsTo`.

## [0.1.2] - 2016-03-17

### Added
- Added `BelongsToThrough` relationship.

## [0.1.1] - 2016-03-17

### Added
- Added `FixBelongsTo`, `FixMorphTo`, `SelfDecorating`, `SelfValidating` and `UuidKey` traits.

[Unpublished]: https://github.com/znck/plug/compare/v0.1.6...HEAD
[0.1.6]: https://github.com/znck/plug/compare/v0.1.5...v0.1.6
[0.1.5]: https://github.com/znck/plug/compare/v0.1.4...v0.1.5
[0.1.4]: https://github.com/znck/plug/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/znck/plug/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/znck/plug/compare/v0.1.1...v0.1.2
