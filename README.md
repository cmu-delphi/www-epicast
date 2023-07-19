
# About

The Crowdcast website for collecting flu forecasts. (Previously known as
Epicast.)

The site is live at <https://delphi.cmu.edu/crowdcast>.

# Branches

The website is deployed to two separate environments: `staging` and `production`. The code for those environments is kept in the
[`dev`](https://github.com/cmu-delphi/www-epicast/tree/dev) and
[`main`](https://github.com/cmu-delphi/www-epicast/tree/main) branches,
respectively.

## `dev` branch

The `dev` branch is deployed to a Delphi-internal development environment where we can iterate quickly without worry of breaking the production site.

## `main` branch

The `main` branch is deployed to a public-facing production environment. It
should contain only tested and reliable code.

## Process

**`main` should not be updated while a forecasting round is active** (i.e.
Friday through Monday), except in case of a critical bugfix.

Basic develop changes/review in staging/release to production workflow:

- Start by creating a **[bug|fix|feature|etc]** branch based on `dev`.
- Make a PR and tag a reviewer with your changes against `dev`. Once apporved and merged this will trigger CI to deploy the application at https://staging.delphi.cmu.edu/crowdcast.
- Once staging is reviewed and deemed acceptable, make a PR against `main` and tag a reviewer. Once this is approved and merged the production version of the application will be available at https://delphi.cmu.edu/crowdcast

# Development

For developing the website, see the
[epicast development guide](docs/epicast_development.md).
