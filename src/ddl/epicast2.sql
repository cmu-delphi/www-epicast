/*
This file contains the data definitions needed to initialize Epicast's
database, which is, for legacy reasons, named "epicast2".
*/

/*
Analogous to `ec_fluv_regions`, table `ec_fluv_age_groups` stores the various
names and descriptions for the age groups used in hospitalization forecasting.

+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int(11)      | NO   | PRI | NULL    | auto_increment |
| flusurv_name | varchar(16)  | NO   | MUL | NULL    |                |
| name         | varchar(128) | NO   | MUL | NULL    |                |
| ages         | varchar(256) | NO   |     | NULL    |                |
+--------------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_age_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flusurv_name` varchar(16) NOT NULL,
  `name` varchar(128) NOT NULL,
  `ages` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `flusurv_name` (`flusurv_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_defaults` stores default values for user preferences. Entries in
`ec_fluv_user_preferences` override the non-user-specific values stored in this
table.

+-------+--------------+------+-----+---------+----------------+
| Field | Type         | Null | Key | Default | Extra          |
+-------+--------------+------+-----+---------+----------------+
| id    | int(11)      | NO   | PRI | NULL    | auto_increment |
| name  | varchar(64)  | NO   | UNI | NULL    |                |
| value | varchar(256) | YES  |     | NULL    |                |
+-------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_forecast` stores user predictions, both in draft form and submitted
versions. These two are distinguished by the presence of a corresponding entry
in the `ec_fluv_submissions` table.

+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| id          | int(11)  | NO   | PRI | NULL    | auto_increment |
| user_id     | int(11)  | NO   | MUL | NULL    |                |
| region_id   | int(11)  | NO   | MUL | NULL    |                |
| epiweek_now | int(11)  | NO   | MUL | NULL    |                |
| epiweek     | int(11)  | NO   | MUL | NULL    |                |
| wili        | float    | NO   |     | NULL    |                |
| date        | datetime | NO   |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_forecast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `epiweek_now` int(11) NOT NULL,
  `epiweek` int(11) NOT NULL,
  `wili` float NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`user_id`,`region_id`,`epiweek_now`,`epiweek`),
  KEY `user_id` (`user_id`),
  KEY `region_id` (`region_id`),
  KEY `epiweek_now` (`epiweek_now`),
  KEY `epiweek` (`epiweek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_forecast_hosp` is the hospitalization version of `ec_fluv_forecast`,
storing drafts and submissions.

+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| id          | int(11)  | NO   | PRI | NULL    | auto_increment |
| user_id     | int(11)  | NO   | MUL | NULL    |                |
| group_id    | int(11)  | NO   | MUL | NULL    |                |
| epiweek_now | int(11)  | NO   | MUL | NULL    |                |
| epiweek     | int(11)  | NO   | MUL | NULL    |                |
| value       | float    | NO   |     | NULL    |                |
| date        | datetime | NO   |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_forecast_hosp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `epiweek_now` int(11) NOT NULL,
  `epiweek` int(11) NOT NULL,
  `value` float NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`user_id`,`group_id`,`epiweek_now`,`epiweek`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`),
  KEY `epiweek_now` (`epiweek_now`),
  KEY `epiweek` (`epiweek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_mturk_tasks` is used to determine the set of locations that users are
asked to predict.

Despite the name containing "mturk", this table now appears to be used by the
main (non-mturk) version of Epicast.

+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| taskID      | int(11)      | NO   | PRI | NULL    | auto_increment |
| states      | varchar(255) | NO   |     | NULL    |                |
| numWorker   | int(11)      | NO   |     | NULL    |                |
| epiweek_now | int(11)      | YES  |     | NULL    |                |
| maxWorker   | int(11)      | YES  |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_mturk_tasks` (
  `taskID` int(11) NOT NULL AUTO_INCREMENT,
  `states` varchar(255) NOT NULL,
  `numWorker` int(11) NOT NULL,
  `epiweek_now` int(11) DEFAULT NULL,
  `maxWorker` int(11) DEFAULT NULL,
  PRIMARY KEY (`taskID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_regions` stores the various FluView-like region codes and
corresponding display names.

See also the table `ec_fluv_age_groups`, which has a similar purpose to this
table, but is specific to hospitalization forecasting.

+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int(11)      | NO   | PRI | NULL    | auto_increment |
| fluview_name | varchar(16)  | NO   | MUL | NULL    |                |
| name         | varchar(128) | NO   | MUL | NULL    |                |
| states       | varchar(256) | NO   |     | NULL    |                |
| population   | int(11)      | NO   |     | NULL    |                |
+--------------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fluview_name` varchar(16) NOT NULL,
  `name` varchar(128) NOT NULL,
  `states` varchar(256) NOT NULL,
  `population` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `fluview_name` (`fluview_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_round` stores weekly timing information which is used for many
purposes thoughout the website.

+---------------+----------+------+-----+---------+-------+
| Field         | Type     | Null | Key | Default | Extra |
+---------------+----------+------+-----+---------+-------+
| round_epiweek | int(11)  | NO   |     | NULL    |       |
| deadline      | datetime | NO   |     | NULL    |       |
+---------------+----------+------+-----+---------+-------+
*/

CREATE TABLE `ec_fluv_round` (
  `round_epiweek` int(11) NOT NULL,
  `deadline` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_scores` stores weekly and total prediction scores for each user.

+---------+----------+------+-----+---------+----------------+
| Field   | Type     | Null | Key | Default | Extra          |
+---------+----------+------+-----+---------+----------------+
| id      | int(11)  | NO   | PRI | NULL    | auto_increment |
| user_id | int(11)  | NO   | UNI | NULL    |                |
| total   | float    | NO   | MUL | NULL    |                |
| last    | float    | NO   | MUL | NULL    |                |
| updated | datetime | NO   |     | NULL    |                |
+---------+----------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total` float NOT NULL,
  `last` float NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `total` (`total`),
  KEY `last` (`last`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_season` contains timing information used in various displays showing,
for example, the number of weeks remaining in the season.

+---------------------+---------+------+-----+---------+-------+
| Field               | Type    | Null | Key | Default | Extra |
+---------------------+---------+------+-----+---------+-------+
| year                | int(11) | NO   |     | NULL    |       |
| first_round_epiweek | int(11) | NO   |     | NULL    |       |
| last_round_epiweek  | int(11) | NO   |     | NULL    |       |
+---------------------+---------+------+-----+---------+-------+
*/

CREATE TABLE `ec_fluv_season` (
  `year` int(11) NOT NULL,
  `first_round_epiweek` int(11) NOT NULL,
  `last_round_epiweek` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_submissions` indicates which forecasts (in `ec_fluv_forecast`) are
"submitted", and not just in draft form.

+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| id          | int(11)  | NO   | PRI | NULL    | auto_increment |
| user_id     | int(11)  | NO   | MUL | NULL    |                |
| region_id   | int(11)  | NO   |     | NULL    |                |
| epiweek_now | int(11)  | NO   |     | NULL    |                |
| date        | datetime | NO   |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `epiweek_now` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`region_id`,`epiweek_now`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_submissions_hosp` is the hospitalization version of
`ec_fluv_submissions`, indicating which forecasts are submitted.

+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| id          | int(11)  | NO   | PRI | NULL    | auto_increment |
| user_id     | int(11)  | NO   | MUL | NULL    |                |
| group_id    | int(11)  | NO   |     | NULL    |                |
| epiweek_now | int(11)  | NO   |     | NULL    |                |
| date        | datetime | NO   |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_submissions_hosp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `epiweek_now` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`,`epiweek_now`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_user_preferences` stores user preferences and other responses to
options provided on the preferences page. If not specified in this table, the
value for a particular preference name is taken from the table
`ec_fluv_defaults`.

Notable special, non-user controlled preferences:

- "_admin" indicates that the user is an Epicast admin, which grants access to
  various administrative views
- "_delphi" indicates that the user is a member of the Delphi research group,
  which is used only retrospectively for informational purposes
- "_debug" indicates that the account is only for debugging; inputs from such
  accounts will not be used to produce forecasts

+---------+--------------+------+-----+---------+----------------+
| Field   | Type         | Null | Key | Default | Extra          |
+---------+--------------+------+-----+---------+----------------+
| id      | int(11)      | NO   | PRI | NULL    | auto_increment |
| user_id | int(11)      | NO   | MUL | NULL    |                |
| name    | varchar(64)  | NO   |     | NULL    |                |
| value   | varchar(256) | YES  |     | NULL    |                |
| date    | datetime     | NO   |     | NULL    |                |
+---------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
`ec_fluv_users` stores information about Epicast users.

+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int(11)      | NO   | PRI | NULL    | auto_increment |
| hash       | char(32)     | NO   | UNI | NULL    |                |
| name       | varchar(256) | NO   |     | NULL    |                |
| email      | varchar(256) | NO   | UNI | NULL    |                |
| instance   | varchar(255) | YES  |     | NULL    |                |
| first_seen | datetime     | NO   |     | NULL    |                |
| last_seen  | datetime     | NO   |     | NULL    |                |
| task_group | int(11)      | YES  |     | NULL    |                |
+------------+--------------+------+-----+---------+----------------+
*/

CREATE TABLE `ec_fluv_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` char(32) NOT NULL,
  `name` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `instance` varchar(255) DEFAULT NULL,
  `first_seen` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  `task_group` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
