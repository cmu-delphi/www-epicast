# Status

[![Deploy Status](https://delphi.midas.cs.cmu.edu/~automation/public/github_deploy_repo/badge.php?repo=cmu-delphi/www-epicast)](#)

# About

The Crowdcast website for collecting flu forecasts. (Previously known as
Epicast.)

The site is live at https://delphi.cmu.edu/crowdcast.

# Branches

The website is deployed to two separate environments: `dev` and `prod`. The
code for those environments is kept in the
[`dev`](https://github.com/cmu-delphi/www-epicast/tree/dev) and
[`prod`](https://github.com/cmu-delphi/www-epicast/tree/prod) branches,
respectively.

## `dev` branch

The `dev` branch is deployed to a Delphi-internal development environment where
we can iterate quickly without worry of breaking the production site.

## `prod` branch

The `prod` branch is deployed to a public-facing production environment. It
should contain only tested and reliable code.

## process

Changes are selectively merged into `prod` from `dev` after testing. However,
**`prod` should not be updated while a forecasting round is active** (i.e.
Friday through Monday), except in case of a critical bugfix.

In any case, all code commits to `prod` should only consist of merges from
`dev`, rather than direct commits. (An exception to this is configuration,
which differs between environments.)
