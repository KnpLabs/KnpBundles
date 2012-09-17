* 2012-09-13
  * Split `User` entities into `Developer`, `Organization` and `Owner`,
    same as split for `Github\User`, also renamed `UserManager` to `OwnerManager`

* 2012-09-10
  * `BundleUtilsExtension#bundleActivity()` now accepts date also as string
  * `BundleController#searchAction()` no longer returns html when query is too short
    if request was made in other format

* 2012-09-07
  * Introduced new design!
  * Replaced `knp-components` with `pagerfanta`

* 2012-08-30
  * Removed `ScoreRepository#setScore()`

* 2012-08-28
  * Removed old `Detectors` code
  * Changed dependency of website from version `0.1` to `master`
    for `KnpLabs/github-api`
  * `Travis` now depends on `Buzz`
  * `Goutte` was removed, and finders were refactored to use `Buzz`
  * `TrendingBundleTwitterer` now depends on `HWIOAuthBundle` (only partially),
    as a addition, `InoriTwitterApp` and related was removed
  * Functionality of `BundleActivity` was merged into `BundleUtilsExtension`

* 2012-08-20
  * Bundle entity property `$symfonyVersion` string turned into `$symfonyVersions` array

* 2012-08-15
  * `BundleActivityTwigExtension` renamed to `BundleUtilsExtension`
