# Changelog

All Notable changes to `plug` will be documented in this file.

## [0.1.6] - 2016-03-21

### Added
- SelfValidating trait can fix rules for update query.
- StyleCI CS Fixes

### Deprecated
- Nothing

### Fixed
- Removed accidentally left extends `Illuminate\Database\Eloquent\Model` from `FixBelongsTo` and `FixMorphTo`.

### Removed
- Nothing

### Security
- Nothing

## [0.1.5] - 2016-03-17

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Removed `$incrementing` field from `UuidKey`. **Set this `protected $incrementing = false;` when using `UuidKey`.**

### Security
- Nothing

## [0.1.4] - 2016-03-17

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Removed accidentally left extends `Illuminate\Database\Eloquent\Model` from `FixBelongsTo`.

### Removed
- Nothing

### Security
- Nothing

## [0.1.3] - 2016-03-17

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Changed `class FixBelongsTo` to `trait FixBelongsTo`.

### Removed
- Nothing

### Security
- Nothing

## [0.1.2] - 2016-03-17

### Added
- Added `BelongsToThrough` relationship.

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## [0.1.1] - 2016-03-17

### Added
- Added `FixBelongsTo`, `FixMorphTo`, `SelfDecorating`, `SelfValidating` and `UuidKey` traits.

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

[Unpublished]: https://github.com/znck/plug/compare/v0.1.6...HEAD
[0.1.6]: https://github.com/znck/plug/compare/v0.1.5...v0.1.6
[0.1.5]: https://github.com/znck/plug/compare/v0.1.4...v0.1.5
[0.1.4]: https://github.com/znck/plug/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/znck/plug/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/znck/plug/compare/v0.1.1...v0.1.2
