* 2012-10-08
  * Added `Activity` entity and replace all text based ones with new approach
    in `Bundle` and `Developer` entities,
  * Added `OwnerRepository#findOneByUniqueFields()` to lookup for users in database,
  * Added new methods to `BundleUtilsExtension`: `#bundleActivityIcon`
    and `#bundleActivityMessage`,
  * Added `BundleManager` for easier creation of bundles,
  * Moved `OwnerManager` into own namespace,
  * Moved methods `#getGithubId()`, `#getSensioId()` from `Developer` entity
    to `Owner` entity
  * Replace dependency of `Github\Organization` from `OrganizationRepository`
    to `OwnerManager`,
  * Replaced dependency of `Update` from `OwnerManager` to `BundleManager`,
  * Removed `Updater#setUp` method, bundles are now loaded via `Pagerfanta`,
  * `UpdateBundleConsumer` no longer removes bundle on API failure,
  * Removed unused exception `UserNotFoundException`

* 2012-10-03
  * Removed `js` format from web API, we still support: `json` and `html`,
  * Moved code related to web API into controllers and removed all view templates
    used before for it
  * Removed `BaseController#recognizeRequestFormat()` method, use
    `Symfony\Component\HttpFoundation\Request#getRequestFormat()` instead,
  * URL query `?format=json` is no longer valid, use proper affix instead,
    i.e.: `/KnpLabs/KnpMenuBundle.json`

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
