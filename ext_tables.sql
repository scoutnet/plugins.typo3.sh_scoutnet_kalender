#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_shscoutnetkalender_ids varchar(255) DEFAULT '' NOT NULL,
	tx_shscoutnetkalender_optids varchar(255) DEFAULT '' NOT NULL,
	tx_shscoutnetkalender_kat_ids varchar(255) DEFAULT '' NOT NULL
	tx_shscoutnetkalender_stufen_ids varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_shscoutnetkalender_scoutnet_username varchar(255) DEFAULT '' NOT NULL,
	tx_shscoutnetkalender_scoutnet_apikey varchar(255) DEFAULT '' NOT NULL,
);
