## FAQ On Scoring Issues:

####How does the site calculate the score?

It does by considering the following factors:

* **1** point for each followers on **GitHub**. This is an indicator of the bundle's popularity in the community.
* **5** points if your `README` is more than 300 characters long. Encourages maintainer to write a proper `README`.
* **5** points if you use **Travis CI**, since it means you have a running test suite.
* **5** more points if your **Travis CI** build status is actually ok ;)
* **5** points if you provide a **Composer** package.
* **5** points per person recommending the bundle on **KnpBundles**.
* Small boost (the actual formula is `(30 - days) / 5`) for bundles with commits in the past 30 days. Active bundles get more points, but not much to avoid spoiling *stable* bundles.

For more details on the items above please refer to the remaining answers.

####Why my bundle is not ranking higher?

Along the months or years now it has been realized that tuning and coming
with the right metrics for the Symfony2 bundle ecosystem is not an easy thing.

The metrics and its threshold values are evolving with the feedback of users
and daily monitoring experience. Remember each day metrics change so they are
dynamic, sometimes we have to wait for the numbers to show up, sometimes we have
misunderstood the metric. Metrics are defined to display preference and usage
so you can take a well informed decision. Of course this should be just one
metric to ponder among others.

####What is the minimum score your bundle needs to have before it is regarded as trendy?

Currently a bundle is considered trendy at a given time if the change
in its score becomes greater than 25 points between consecutive inspections.

####How often a score is updated? What is the time between consecutive inspections?

Our system currently is monitoring bundles more than once a day, and scores could
be updated even withing few hours.

####Will points be given for failed travis builds?

There will be no points awarded for failed travis builds.

##### If you think we missed something important, don't hesitate to [open an issue and tell us](https://github.com/KnpLabs/KnpBundles/issues/new)!
