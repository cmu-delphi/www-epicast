/*
This file contains a minimally-useful set of values for development of the
Epicast website. Production tables contain much more data, which varies by
season and purpose (e.g. flu vs covid, wILI vs hospitalization, etc).

Edit this file as needed during development. This file is not used outside of
local testing.
*/

/* all age groups as of 2020-04-27 */
INSERT INTO `ec_fluv_age_groups` VALUES
  (0,'rate_age_0','Group #1','0-4 years old'),
  (0,'rate_age_1','Group #2','5-17 years old'),
  (0,'rate_age_2','Group #3','18-49 years old'),
  (0,'rate_age_3','Group #4','50-64 years old'),
  (0,'rate_age_4','Group #5','65+ years old'),
  (0,'rate_overall','Group #6','all ages');

/* all default preferences as of 2020-04-27 */
INSERT INTO `ec_fluv_defaults` VALUES
  (0,'advanced_pandemic','1'),
  (0,'email_notifications','1'),
  (0,'email_reminders','0'),
  (0,'skip_intro','0'),
  (0,'survey_epi','2'),
  (0,'survey_flu','2'),
  (0,'survey_ph','2'),
  (0,'survey_sml','2'),
  (0,'survey_vir','2'),
  (0,'_admin','0'),
  (0,'_debug','0'),
  (0,'advanced_leaderboard','0'),
  (0,'advanced_initials','-'),
  (0,'skip_instructions','0'),
  (0,'_delphi','0'),
  (0,'advanced_prior','0'),
  (0,'allLocation','0'),
  (0,'advanced_hospitalization','0'),
  (0,'hidden_seasons','0');

/* all task groups as of 2020-04-27 */
INSERT INTO `ec_fluv_mturk_tasks` VALUES
  (0,'1,13,14,17,19,20,23,32,38,41,56',2,201942,10),
  (0,'1,21,22,24,26,27,33,39,42,46,57',2,201942,10),
  (0,'1,15,18,34,48,50,51,54,58,59,61,64',3,201942,10),
  (0,'1,25,28,29,37,40,44,47,55,60,63,65',1,201942,10),
  (0,'1,12,16,31,35,36,43,45,49,52,53,62',1,201942,10),
  (0,'1,2,3,4,5,6,7,8,9,10,11',0,201942,10);

/* all locations as of 2020-04-27 */
INSERT INTO `ec_fluv_regions` VALUES
  (0,'nat','National','All U.S. States and Territories',313914040),
  (0,'hhs1','HHS Region 1','CT, MA, ME, NH, RI, VT',14562704),
  (0,'hhs2','HHS Region 2','NJ, NY',28434851),
  (0,'hhs3','HHS Region 3','DE, MD, PA, VA, WV',30238794),
  (0,'hhs4','HHS Region 4','AL, FL, GA, KY, MS, NC, SC, TN',62356916),
  (0,'hhs5','HHS Region 5','IL, IN, MI, MN, OH, WI',51945711),
  (0,'hhs6','HHS Region 6','AR, LA, NM, OK, TX',39510585),
  (0,'hhs7','HHS Region 7','IA, KS, MO, NE',13837604),
  (0,'hhs8','HHS Region 8','CO, MT, ND, SD, UT, WY',11157404),
  (0,'hhs9','HHS Region 9','AZ, CA, HI, NV',48745929),
  (0,'hhs10','HHS Region 10','AK, ID, OR, WA',13123542),
  (0,'PA','Pennsylvania','PA',12780000),
  (0,'GA','Georgia','GA',10310000),
  (0,'DC','Washington DC','DC',672228),
  (0,'TX','Texas','TX',27862596),
  (0,'OR','Oregon','OR',4093465),
  (0,'WY','Wyoming','WY',589713),
  (0,'CT','Connecticut','CT',3583134),
  (0,'ME','Maine','ME',1327472),
  (0,'NH','New Hampshire','NH',1335832),
  (0,'RI','Rhode Island','RI',1059080),
  (0,'VT','Vermont','VT',624592),
  (0,'NJ','New Jersey','NJ',8996351),
  (0,'ny_minus_jfk','NY (excluding NYC)','ny_minus_jfk',11483000),
  (0,'DE','Delaware','DE',965866),
  (0,'MD','Maryland','MD',6068511),
  (0,'VA','Virginia','VA',8492783),
  (0,'WV','West Virginia','WV',1834882),
  (0,'AL','Alabama','AL',4884115),
  (0,'KY','Kentucky','KY',4436974),
  (0,'MS','Mississippi','MS',2988726),
  (0,'NC','North Carolina','NC',10146788),
  (0,'SC','South Carolina','SC',4961119),
  (0,'TN','Tennessee','TN',6651194),
  (0,'IL','Illinois','IL',12801539),
  (0,'IN','Indiana','IN',6633053),
  (0,'MI','Michigan','MI',9928300),
  (0,'MN','Minnesota','MN',5519952),
  (0,'OH','Ohio','OH',11614373),
  (0,'WI','Wisconsin','WI',5778708),
  (0,'AR','Arkansas','AR',2988248),
  (0,'NM','New Mexico','NM',2081015),
  (0,'OK','Oklahoma','OK',3923561),
  (0,'MA','Massachusetts','MA',6873018),
  (0,'IA','Iowa','IA',3134693),
  (0,'KS','Kansas','KS',2907289),
  (0,'MO','Missouri','MO',6093000),
  (0,'NE','Nebraska','NE',1907116),
  (0,'CO','Colorado','CO',5540545),
  (0,'MT','Montana','MT',1042520),
  (0,'ND','North Dakota','ND',790701),
  (0,'SD','South Dakota','SD',865454),
  (0,'UT','Utah','UT',3051217),
  (0,'AZ','Arizona','AZ',6931071),
  (0,'CA','California','CA',39250017),
  (0,'HI','Hawaii','HI',1428557),
  (0,'NV','Nevada','NV',2940058),
  (0,'ID','Idaho','ID',1683140),
  (0,'AK','Alaska','AK',741204),
  (0,'WA','Washington','WA',7288000),
  (0,'LA','Louisiana','LA',4652581),
  (0,'PR','Puerto Rico','PR',2906871),
  (0,'VI','Virgin Islands','VI',107268),
  (0,'JFK','New York City','JFK',8623000);

/* the "current" week (change as needed for development), should be consistent
with `ec_fluv_season` below */
INSERT INTO `ec_fluv_round` VALUES
  (202016,'2020-04-27 08:00:00');

/* a high score for you (congratulations btw!) */
INSERT INTO `ec_fluv_scores` VALUES
  (0,1,12345.678,123.456,'2020-01-02 03:04:05');

/* the "current" flu season (change as needed for development), should be
consistent with `ec_fluv_round` above */
INSERT INTO `ec_fluv_season` VALUES
  (2019,201942,202035);

/* make yourself an admin */
INSERT INTO `ec_fluv_user_preferences` VALUES
  (0,1,'_admin',1,'2019-09-25 12:36:34');

/* your account details */
INSERT INTO `ec_fluv_users` VALUES
  (0,'00000000000000000000000000000000','Delphi Developer','fake_email_address',NULL,'2019-09-25 12:34:31','2019-09-25 12:34:31',NULL);
