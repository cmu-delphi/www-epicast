# `delphi_web_epicast`

This image starts with Delphi's web server and adds the sources necessary for
hosting the Epicast website. It further extends Delphi's web server by:

- enabling the `mod_rewrite` extension
- enabling the `mod_headers` extension
- creating an empty `htpasswd` file

This image includes the file
[`settings.php`](assets/settings.php), which points to a local
container running the
[`delphi_database_epicast` image](../../database/epicast/README.md).

To start a container from this image, run:

```bash
docker run --rm -p 10080:80 \
  --network delphi-net --name delphi_web_epicast \
  delphi_web_epicast
```

You should be able to login and interact with the website (e.g. submitting
predictions) by visiting `http://localhost:10080/` in a web browser.
