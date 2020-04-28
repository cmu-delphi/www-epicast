# Epicast Website Development Guide

**Prerequisite:** this guide assumes that you have read the
[frontend development guide](https://github.com/cmu-delphi/operations/blob/master/docs/frontend_development.md).

This guide describes how run a local development instance of the Epicast
website. For preliminary steps,
[install docker and create a virtual network](https://github.com/cmu-delphi/operations/blob/master/docs/frontend_development.md#setup).

# setup

For working on the Epicast website, you'll need the following two Delphi
repositories:

- [operations](https://github.com/cmu-delphi/operations)
- [www-epicast](https://github.com/cmu-delphi/www-epicast)

You likely won't need to modify the `operations` repo, so cloning directly from
`cmu-delphi` is usually sufficient. However, since you _are_ going to be
modifying `www-epicast` sources, you'll first need to fork the repository
and then clone your personal fork. For more details, see the Delphi-specific
[discussion on forking and branching](https://github.com/cmu-delphi/operations/blob/master/docs/backend_development.md#everyone).

Here's an example of how to setup your local workspace. Note that you will need
to use your own GitHub username where indicated.

```bash
# collect everything in a directory called "repos"
mkdir repos && cd repos

# delphi python (sub)packages
mkdir delphi && cd delphi
git clone https://github.com/cmu-delphi/operations
git clone https://github.com/Your-GitHub-Username/www-epicast
cd ..

# go back up to the workspace root
cd ..
```

Your workspace should now look like this:

```bash
tree -L 3 .
```

```
.
└── repos
    └── delphi
        ├── operations
        └── www-epicast
```

# branches and deployment

Note that Epicast follows a weekly release cadence whereby select changes in
the `dev` branch are
[cherry-picked](https://www.atlassian.com/git/tutorials/cherry-pick)
into the `prod` branch. The `prod` branch should never be committed to
directly, and changes shouldn't be merged in from `dev` during active
forecasting, which is nominally Friday through Monday each week.

The `HEAD` of the `dev` branch is continuously deployed to a staging server
which, with credentials, you can access at
https://app-mono-dev-01.delphi.cmu.edu/cc-test. Similarly, the `HEAD` of the
`prod` branch is continuously deployed to the production server which is
publicly accessible at https://delphi.cmu.edu/crowdcast/.

For additional details about the various branches, and the intended use-cases
of each, see [the top-level readme](../README.md). Notably, the `master` branch
has only documentation and not code. This is because documentation (e.g. this
guide) is generally not specific to any particular branch, whereas the master
branch has intentionally undefined deployment semantics and should not contain
any code.

# build images

For the purposes of this guide, which is broadly focused on the typical Epicast
development workflow, go ahead and checkout the `dev` branch which is where the
development version of the code lives. We will return to the `prod` branch
later in this guide.

```bash
git \
--git-dir repos/delphi/www-epicast/.git \
--work-tree repos/delphi/www-epicast \
checkout dev
```

We now need images for the Epicast web server and database. These are both
based on core Delphi images which are defined in the
[`operations` repo](https://github.com/cmu-delphi/operations) which you cloned
above. The base images are built first, followed by the derived
`epicast`-specific images.

- The [`delphi_web_epicast` image](../dev/docker/web/epicast/README.md) adds
  the Epicast website to the `delphi_web` image.
- The
  [`delphi_database_epicast` image](../dev/docker/database/epicast/README.md)
  adds the `epi` user account, `epicast2` (legacy name) database, and relevant
  tables to the `delphi_database` image.

From the root of your workspace, all of the images can be built as follows.
**Be sure to have checked out an appropriate branch as described above, as
branch `master` doesn't contain the requisite files.**

```bash
docker build -t delphi_web \
  -f repos/delphi/operations/dev/docker/web/Dockerfile .

docker build -t delphi_web_epicast \
  -f repos/delphi/www-epicast/dev/docker/web/epicast/Dockerfile .

docker build -t delphi_database \
  -f repos/delphi/operations/dev/docker/database/Dockerfile .

docker build -t delphi_database_epicast \
  -f repos/delphi/www-epicast/dev/docker/database/epicast/Dockerfile .
```

# develop

At this point you're ready to bring the stack online. To do that, just start
containers for the Epicast-specific web and database images. As an aside, the
output from these commands (especially the web server) can be very helpful for
debugging. For example, in separate terminals:

```bash
# launch the database
docker run --rm -p 13306:3306 \
  --network delphi-net --name delphi_database_epicast \
  delphi_database_epicast

# launch the web server
docker run --rm -p 10080:80 \
  --network delphi-net --name delphi_web_epicast \
  delphi_web_epicast
```

You should now be able to visit your own personal instance of the website,
backed by your own personal instance of the database, by visiting
http://localhost:10080/ in a web browser. Note that your user ID for login is
"00000000", as defined in `src/ddl/development_data.sql` (see the line like
`INSERT INTO ``ec_fluv_users`` ... `).

After making website changes, bring the `delphi_web_epicast` container down
(e.g. `docker stop delphi_web_epicast`), rebuild the image, and relaunch the
container. This stop-build-start cycle is necessary to pick up modifications to
your local source files. This is good for the sake of bundling and running a
self-contained and isolated unit of code, but it's suboptimal in terms of rapid
iteration and developer experience.

It's possible to [bind-mount](https://docs.docker.com/storage/bind-mounts/) a
local directory (say `repos/delphi/www-epicast/site/`) to a directory in a
container. This opens up your local filesystem to the container, which is not
normally granted such visibility. By doing it this way, local changes to source
files are immediately visible (subject to page refresh and caching, etc) in
your local instance of the website. The huge benefit of doing it this way is
that you don't have to stop, rebuild, and restart every time you want to test
out a code change. However, there are some drawbacks to keep in mind:

- your local filesystem is now exposed to code running in the container (this
isn't much of a practical concern in this case since we generally trust the
code written by fellow Delphi developers, but still something to be aware of)
- this is very tedious to specify on the command line, and changes to directory
structure can cause the mount flag to no longer work
- several mount flags, each of which is tedious, may be needed, depending on
how the code in the repo maps to code on the server (i.e. must replicate the
mapping defined in `deploy.json`)

Here's an example of how to bind-mount the website source files in your local
`www-epicast` repository into a `delphi_web_epicast` container:

```bash
# launch the web server, serving website files from your actual filesystem
# rather than from the image
docker run --rm -p 10080:80 \
  --mount type=bind,source="$(pwd)"/repos/delphi/www-epicast/site,target=/var/www/html,readonly \
  --mount type=bind,source="$(pwd)"/repos/delphi/www-epicast/dev/docker/web/epicast/assets/settings.php,target=/var/www/html/common/settings.php,readonly \
  --network delphi-net --name delphi_web_epicast \
  delphi_web_epicast
```

When development is finished, it's a good idea to test things out one final
time _without_ bind-mounting. This helps to ensure that all the code is working
as intended without depending on changes to other bind-mounted local files
which haven't captured by the image.

# updating `prod`

[Cherry-picking](https://www.atlassian.com/git/tutorials/cherry-pick) is the
process by which specific commits are taken from the `dev` branch and copied
over to the `prod` branch. Aside from production-specific configuration
changes, this is the only way by which code changes should be introduced to the
`prod` branch.

To begin, suppose there has been some sequence of commits made to the `dev`
branch, one of which we now want to port over to the `prod` branch. This can be
done from the root of your workspace, but for simplicity, change directory into
the `www-epicast` repo. Then, identify the commit by its hash, which can
determined by `git log`, for example:

```bash
# move into the repo to simplify subsequent git commands
cd repos/delphi/www-epicast

# checkout the dev branch
git checkout dev

# show recent commits
git log --oneline | head -5
```

```
965682a database ddl and local docker development
be9867e grant access during local docker development
6f3f719 remove unused css-as-php files
b61eb03 update advanced preferences, fixes #12
a27efc0 get latest issue from epidata api
```

Let's assume that we want to bring over the changes from "update advanced
preferences, fixes #12", with commit hash `b61eb03`.

Switch over to the `prod` branch, and make the cherry-pick:

```bash
# checkout the prod branch
git checkout prod

# cherry-pick the bugfix
git cherry-pick b61eb03
```

```
[prod f64268b] update advanced preferences, fixes #12
 Date: Tue Apr 28 15:05:18 2020 -0500
 1 file changed, 4 insertions(+), 4 deletions(-)
```

You can confirm that the commit was cherry-picked into `prod` by `git log`.

In case of merge conflicts, you will see a message similar to the following:

```
error: could not apply d43ef45... remove the caching service worker
hint: after resolving the conflicts, mark the corrected paths
hint: with 'git add <paths>' or 'git rm <paths>'
hint: and commit the result with 'git commit'
```

In this case, you'll have to manually resolve the merge conflict (use `git
status` to find the modified but unstaged file), add the resolved file(s), and
then finish up with `git commit`.

Finally, submit a pull request against the `prod` branch of the `www-epicast`
repo, where after quick sanity check review, the changes can be merged into the
`prod` branch and subsequently automatically deployed to the production
environment.
