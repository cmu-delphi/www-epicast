# start with the `delphi_database` image
FROM delphi_database

# create the `epicast2` database
ENV MYSQL_DATABASE epicast2

# create the `epi` user account with a development-only password
ENV MYSQL_USER user
ENV MYSQL_PASSWORD pass

# provide DDL which will create empty tables at container startup
# note that files are executed in order of filename alphabetically, so here
# destination files are named such that table definitions are executed prior to
# data insertions
COPY repos/delphi/www-epicast/src/ddl/epicast2.sql /docker-entrypoint-initdb.d/0_epicast2.sql
COPY repos/delphi/www-epicast/src/ddl/development_data.sql /docker-entrypoint-initdb.d/1_development_data.sql

# ensure files are readable at runtime
RUN chmod o+r /docker-entrypoint-initdb.d/*
