#
# Table structure for table 'tx_disposableemail_list'
#
CREATE TABLE tx_disposableemail_list (
	uid int(11) NOT NULL auto_increment,

	domain varchar(255),
	provider_type varchar(32) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	UNIQUE KEY uniq_domain_provider_type (domain, provider_type)
);
