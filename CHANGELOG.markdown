* 2012-08-20
  * Removed old Detectors code, as it's not used anymore
  * Changed dependency of website from version `0.1` to `master`
    for `KnpLabs/github-api`
  * `Travis` now depends on `Buzz`
  * `Goutte` was removed, and finders were refactored to use `Buzz`
  * `TrendingBundleTwitterer` now depends on `HWIOAuthBundle` resource owner,
    not on additional `InoriTwitterApp` and related
  * Funtionality of `BundleActivity` was merged into `BundleUtilsExtension`
  * Bundle entity property `$symfonyVersion` string turned into `$symfonyVersions` array

* 2012-08-15
  * `BundleActivityTwigExtension` renamed to `BundleUtilsExtension`
