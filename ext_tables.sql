#
# Table structure for table 'tx_disposableemail_list'
#
CREATE TABLE tx_disposableemail_list (
	uid int(11) NOT NULL auto_increment,

	domain varchar(255),

	PRIMARY KEY (uid, domain)
);
