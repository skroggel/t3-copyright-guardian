#
# Table structure for table 'sys_file_metadata'
#
CREATE TABLE sys_file_metadata
(
	tx_copyrightguardian_creator varchar(255) DEFAULT '' NOT NULL,
	tx_copyrightguardian_source  int(11) DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'sys_file_reference'
#
CREATE TABLE sys_file_reference
(
	tx_copyrightguardian_images_no_copyright tinyint(1) DEFAULT '0' NOT NULL,
);


#
# Table structure for table 'tx_copyrightguardian_domain_model_mediasource'
#
CREATE TABLE tx_copyrightguardian_domain_model_mediasource
(

	uid              int(11) NOT NULL auto_increment,
	pid              int(11) DEFAULT '0' NOT NULL,

	name             varchar(255) DEFAULT '' NOT NULL,
	url              varchar(255) DEFAULT '' NOT NULL,
	internal         tinyint(1) DEFAULT '0' NOT NULL,

	tstamp           int(11) DEFAULT '0' NOT NULL,
	crdate           int(11) DEFAULT '0' NOT NULL,
	cruser_id        int(11) DEFAULT '0' NOT NULL,
	deleted          tinyint(4) DEFAULT '0' NOT NULL,
	hidden           tinyint(4) DEFAULT '0' NOT NULL,
	starttime        int(11) DEFAULT '0' NOT NULL,
	endtime          int(11) DEFAULT '0' NOT NULL,

	t3ver_oid        int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid       int(11) DEFAULT '0' NOT NULL,
	t3ver_label      varchar(255) DEFAULT '' NOT NULL,
	t3ver_state      tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage      int(11) DEFAULT '0' NOT NULL,
	t3ver_count      int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp     int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id    int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent      int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource  mediumblob,

	PRIMARY KEY (uid),
	KEY              parent (pid),
	KEY              t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);
