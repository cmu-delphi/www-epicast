# `delphi_database_epicast`

This image extends Delphi's database by:

- adding the `epi` user account
- adding the `epicast2` database
- creating and minimally populating tables in `epicast2`

To start a container from this image, run:

```bash
docker run --rm -p 13306:3306 \
  --network delphi-net --name delphi_database_epicast \
  delphi_database_epicast
```

For debugging purposes, you can interactively connect to the database inside
the container using a `mysql` client (either installed locally or supplied via
a docker image) like this:

```bash
mysql --user=user --password=pass --port 13306 --host 127.0.0.1 epicast2
```

Note that using host `localhost` may fail on some platforms as mysql will
attempt, and fail, to use a Unix socket. Using `127.0.0.1`, which implies
TCP/IP, works instead.
