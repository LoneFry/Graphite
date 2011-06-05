CREATE TABLE IF NOT EXISTS `g_Logins` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `loginname` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `realname` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',

  `sessionStrength` tinyint(1) NOT NULL DEFAULT '2',
  `lastIP` int(11) NOT NULL DEFAULT '0',
  `UA` varchar(40) NOT NULL DEFAULT '',

  `dateModified` int(11) NOT NULL DEFAULT '0',
  `dateActive` int(11) NOT NULL DEFAULT '0',
  `dateLogin` int(11) NOT NULL DEFAULT '0',
  `dateLogout` int(11) NOT NULL DEFAULT '0',
  `dateCreated` int(11) NOT NULL DEFAULT '0',

  `referrer_id` int(11) NOT NULL DEFAULT '0',
  `disabled` bit NOT NULL DEFAULT 0,
  `flagChangePass` bit NOT NULL DEFAULT 0,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `loginname` (`loginname`)
);
INSERT INTO g_Logins(`login_id`,`loginname`,`password`,`flagChangePass`) VALUES (1,'root','d033e22ae348aeb5660fc2140aec35850c4da997',1);
--password 'abc'

CREATE TABLE IF NOT EXISTS `g_Roles_Logins` (
  `role_id` int(11) NOT NULL DEFAULT '0',
  `login_id` int(11) NOT NULL DEFAULT '0',
  `grantor_id` int(11) NOT NULL DEFAULT '0',
  `dateCreated`  int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`,`login_id`)
);

CREATE TABLE `g_Roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `creator_id` int(11) NOT NULL DEFAULT '0',
  `disabled` bit NOT NULL DEFAULT 0,
  `dateModified` int(11) NOT NULL DEFAULT '0',
  `dateCreated` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `label` (`label`)
);

CREATE TABLE `g_LoginLog` (
  `pkey` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `iDate` int(11) NOT NULL,
  PRIMARY KEY (`pkey`)
);

CREATE TABLE `g_ContactLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(255) NOT NULL,
  `date` int NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `login_id` int NOT NULL DEFAULT '0',
  `flagDismiss` bit NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `flagDismiss` (`flagDismiss`)
);
