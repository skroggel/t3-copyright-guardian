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

	PRIMARY KEY (uid),
	KEY              parent (pid)
);
