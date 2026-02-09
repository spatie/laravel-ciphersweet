# Changelog

All notable changes to `laravel-ciphersweet` will be documented in this file.

## 1.7.3 - 2026-02-09

### What's Changed

* Append CIPHERSWEET_KEY to .env when variable doesn't exist yet in https://github.com/spatie/laravel-ciphersweet/pull/88
* Fix PHPStan configuration and update baseline

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.7.2...1.7.3

## 1.7.2 - 2025-09-18

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/spatie/laravel-ciphersweet/pull/81
* Update issue template by @AlexVanderbist in https://github.com/spatie/laravel-ciphersweet/pull/85
* Fix blind index WHERE clause with table prefix by @mihy in https://github.com/spatie/laravel-ciphersweet/pull/86
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/spatie/laravel-ciphersweet/pull/84
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/spatie/laravel-ciphersweet/pull/82

### New Contributors

* @AlexVanderbist made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/85
* @mihy made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/86

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.7.1...1.7.2

## 1.7.1 - 2025-04-29

### What's Changed

* Fix type declarations for PHP 8.4 and add PHP 8.3/8.4 to CI workflow by @alissn in https://github.com/spatie/laravel-ciphersweet/pull/80

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.7.0...1.7.1

## 1.7.0 - 2025-04-28

### What's Changed

* Add `EncryptedUniqueRule` for Encrypted Unique Validation by @alissn in https://github.com/spatie/laravel-ciphersweet/pull/78

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.5...1.7.0

## 1.6.5 - 2025-02-17

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/76
* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-ciphersweet/pull/77

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.4...1.6.5

## 1.6.4 - 2025-01-17

### What's Changed

* Revert PR #74 and fix getChanges() by @felabrecque in https://github.com/spatie/laravel-ciphersweet/pull/75

### New Contributors

* @felabrecque made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/75

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.3...1.6.4

## 1.6.3 - 2024-12-11

### What's Changed

* Bump dependabot/fetch-metadata from 1.6.0 to 2.1.0 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/66
* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/71
* Fixing model observers integration by @yormy in https://github.com/spatie/laravel-ciphersweet/pull/74

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.2...1.6.3

## 1.6.2 - 2024-07-18

### What's Changed

* Add AAD to CustomBackend test by @mdpoulter in https://github.com/spatie/laravel-ciphersweet/pull/70
* Catch correct exception when fields are missing during decryption by @mdpoulter in https://github.com/spatie/laravel-ciphersweet/pull/69

### New Contributors

* @mdpoulter made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/70

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.1...1.6.2

## 1.6.1 - 2024-03-13

### What's Changed

* Bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/64
* [feat] add table prefix to ciphersweet:encrypt command by @aryala7 in https://github.com/spatie/laravel-ciphersweet/pull/60

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.6.0...1.6.1

## 1.6.0 - 2024-03-13

### What's Changed

* Addition of `permit_empty` Configuration to README by @alissn in https://github.com/spatie/laravel-ciphersweet/pull/61
* Laravel 11.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-ciphersweet/pull/63

### New Contributors

* @alissn made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/61
* @laravel-shift made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/63

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.5.0...1.6.0

## 1.5.0 - 2023-11-07

### What's Changed

- Add option base64 to GenerateKeyCommand for generate key in base64. T… by @gawsoftpl in https://github.com/spatie/laravel-ciphersweet/pull/49
- Bump actions/checkout from 3 to 4 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/52
- Bump stefanzweifel/git-auto-commit-action from 4 to 5 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/54
- Ensure null values are not encrypted by @yormy in https://github.com/spatie/laravel-ciphersweet/pull/57

### New Contributors

- @gawsoftpl made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/49
- @yormy made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/57

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.4.1...1.5.0

## 1.4.1 - 2023-08-14

### What's Changed

- Fix tests & phpstan by @rjindael in https://github.com/spatie/laravel-ciphersweet/pull/48

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.4.0...1.4.1

## 1.4.0 - 2023-08-14

### What's Changed

- [feat] update readme.md by @aryala7 in https://github.com/spatie/laravel-ciphersweet/pull/46
- Revamp key generation command by @rjindael in https://github.com/spatie/laravel-ciphersweet/pull/47

### New Contributors

- @rjindael made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/47

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.3.0...1.4.0

## 1.3.0 - 2023-07-11

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.6 to 1.4.0 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/35
- Bump dependabot/fetch-metadata from 1.4.0 to 1.5.1 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/37
- Fix documentation regarding encryption key setup and use by @mgkimsal in https://github.com/spatie/laravel-ciphersweet/pull/40
- Bump dependabot/fetch-metadata from 1.5.1 to 1.6.0 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/41
- [feat] add permit_empty to config file by @aryala7 in https://github.com/spatie/laravel-ciphersweet/pull/43

### New Contributors

- @mgkimsal made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/40

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.2.0...1.3.0

## 1.2.0 - 2023-03-31

### What's Changed

- Implement custom backend by @stevenmaguire in https://github.com/spatie/laravel-ciphersweet/pull/29

### New Contributors

- @stevenmaguire made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/29

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.1.0...1.2.0

## 1.1.0 - 2023-03-30

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/25
- [feat] implement sortDir as an optional argument by @aryala7 in https://github.com/spatie/laravel-ciphersweet/pull/27
- Allow implementing a custom provider

### New Contributors

- @aryala7 made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/27

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.0.4...1.1.0

## 1.0.4 - 2023-01-26

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.3 to 1.3.4 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/19
- Bump dependabot/fetch-metadata from 1.3.4 to 1.3.5 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/20
- Update README.md by @TeddyBear06 in https://github.com/spatie/laravel-ciphersweet/pull/23
- Bump ramsey/composer-install from 1 to 2 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/22

### New Contributors

- @TeddyBear06 made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/23

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.0.3...1.0.4

## 1.0.3 - 2022-07-26

- Move deleting blind indexes to trait so it can be overridden

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.0.2...1.0.3

## 1.0.2 - 2022-07-15

- Blind indexes now use upserts instead of updateOrInsert

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.0.1...1.0.2

## 1.0.1 - 2022-07-11

### What's Changed

- Typo error in  README.md by @raksbisht in https://github.com/spatie/laravel-ciphersweet/pull/1
- Return type in model's observer methods by @raksbisht in https://github.com/spatie/laravel-ciphersweet/pull/2
- Typo error in GeneratekeyCommand.php info text. by @raksbisht in https://github.com/spatie/laravel-ciphersweet/pull/3
- Bump dependabot/fetch-metadata from 1.3.1 to 1.3.3 by @dependabot in https://github.com/spatie/laravel-ciphersweet/pull/7
- Add docblock to configureCipherSweet in README with usage instructions by @jeffersonmartin in https://github.com/spatie/laravel-ciphersweet/pull/11
- Fix Service Provider `buildBackend` method to use correct config array key by @jeffersonmartin in https://github.com/spatie/laravel-ciphersweet/pull/10
- Fix docblock formatting typo in config/ciphersweet.php by @jeffersonmartin in https://github.com/spatie/laravel-ciphersweet/pull/9

### New Contributors

- @raksbisht made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/1
- @dependabot made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/7
- @jeffersonmartin made their first contribution in https://github.com/spatie/laravel-ciphersweet/pull/11

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/1.0.0...1.0.1

## 1.0.0 - 2022-06-30

- Initial release! 🎉

**Full Changelog**: https://github.com/spatie/laravel-ciphersweet/compare/0.0.1...1.0.0

## 0.0.1 - 2022-06-30

- experimental release
