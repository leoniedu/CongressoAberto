--- In order for gvstreamer towork, and for efficiency reasons as well, we will not have "text" fields in the database (unless it is sorely needed.)



SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"; -- Normally, you generate the next sequence number for the column by inserting either NULL or 0 into it. NO_AUTO_VALUE_ON_ZERO suppresses this behavior for 0 so that only NULL generates the next sequence number.

--
-- Database: `congressoaberto`;
--

-- We will use InnoDB engine, which allows for foreign keys. (That we might want to use.)
-- --------------------------------------------------------

use congressoaberto;

DROP TABLE IF EXISTS `br_partymedians`;
CREATE TABLE IF NOT EXISTS `br_partymedians` (
    `Partido` varchar(20),
    `coord1D` double default NULL,
    `initdate` date,
    `finaldate` date,
    PRIMARY KEY (Partido, initdate, finaldate)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `br_bio`;
CREATE TABLE  `br_bio` (
    `bioid` int COMMENT 'You can add a comment here',
    `namelegis` varchar(100),
    `name` varchar(100),
    `party` varchar(10),
    `birthdate` varchar(10),
    `birthplace` varchar(100),
    `legisserved` varchar(255),
    `prevparties` varchar(255),
    `mandates` varchar(255),
    `biofile` varchar(100),
    `imagefile` varchar(100),
    `nameindex` varchar(100),
    `state` varchar(2),
    PRIMARY KEY  (`bioid`) -- NOTE: A primary key has to be unique
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Holds the biography information available at the Camara site. 
CreatedBy=bioprocess.R
updatedBy=bioprocess.R
'
;

-- PARTITION BY HASH( YEAR(hired) )
-- ALTER TABLE br_bio ADD INDEX (columns_to_index)

DROP TABLE IF EXISTS `br_bioidname`;
CREATE TABLE  `br_bioidname` (
    `bioid` int,
    `name` varchar(100),
    `state` varchar(2),
    `legis` int,
    PRIMARY KEY  (`bioid`,`name`(100),`legis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between biography id and names. Used in the function reading roll calls to match bioid to the roll call id. 
CreatedBy=bioprocess.R
updatedBy=function readOne 
';

DROP TABLE IF EXISTS `br_parties`;
CREATE TABLE  `br_parties` (
    `number` int,
    `name` varchar(100),
    `party` varchar(10),
    `date` varchar(10),	
    `year_extinct` int,	
    `notes` varchar(100),
    PRIMARY KEY  (`party`,`name`,`number`,`year_extinct`,`notes`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
';

DROP TABLE IF EXISTS `br_parties_current`;
CREATE TABLE  `br_parties_current` (
    `number` int,
    `name` varchar(100),
    `party` varchar(10),
    `date` varchar(10),	
    PRIMARY KEY  (`party`,`name`,`number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
';

DROP TABLE IF EXISTS `br_leaders`;
CREATE TABLE  `br_leaders` (
    `rcvoteid` int,
    `block` varchar(100),
    `party` varchar(10),
    `rc` varchar(10),
    PRIMARY KEY  (`rcvoteid`,`block`,`party`)	
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=leaders vote table
CreatedBy=
updatedBy=';


DROP TABLE IF EXISTS `br_idbioid`;
CREATE TABLE  `br_idbioid` (
    `bioid` int,
    `id` int,
    `legis` int,
    PRIMARY KEY  (`bioid`,`id`,`legis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between biography id and the roll call id
CreatedBy=readOne
updatedBy=readOne';


DROP TABLE IF EXISTS `br_billidpostid`;
CREATE TABLE  `br_billidpostid` (
    `billid` int,		
    `postid` int,
    PRIMARY KEY  (`billid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between wordpress posts and billid
CreatedBy=
updatedBy=';	


DROP TABLE IF EXISTS `br_rcvoteidpostid`;
CREATE TABLE  `br_rcvoteidpostid` (
    `rcvoteid` int,		
    `postid` int,
    PRIMARY KEY  (`rcvoteid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between wordpress posts and rcvoteid
CreatedBy=
updatedBy=';	


DROP TABLE IF EXISTS `br_bioidpostid`;
CREATE TABLE  `br_bioidpostid` (
    `bioid` int,		
    `postid` int,
    PRIMARY KEY  (`bioid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between wordpress posts and bioid
CreatedBy=
updatedBy=';	



DROP TABLE IF EXISTS `br_votos`;
CREATE TABLE  `br_votos` (
    `id` int,
    `legis` int,
    `namelegis` varchar(255),
    `party` varchar(255),
    `state` varchar(2),
    `rc` varchar(20),
    `rcfile` varchar(30),
    `rcvoteid` int,
    `bioid` int,
    PRIMARY KEY  (`bioid`,`rcvoteid`,`legis`)
    ) 
-- ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COMMENT=''
-- PARTITION BY HASH(legis)
-- PARTITIONS 6
;
alter table br_votos add key rcfile_index(rcfile);
alter table br_votos add key rcvoteid_index(rcvoteid);


DROP TABLE IF EXISTS `br_votacoes`;
CREATE TABLE  `br_votacoes` (
    `rcvoteid` int default NULL,
    `session` varchar(9), 
    `billtext` varchar(255),
    `rcdate` varchar(255),
    `billproc` varchar(255),
    `billdescription` varchar(255),
    `bill` varchar(255),
    `legisyear` int,
    `rcyear` int,
    `billyear` int,
    `billno` varchar(255),
    `billtype` varchar(30),
    `legis` int,
    `rcfile` varchar(30),
    PRIMARY KEY  (`rcvoteid`,`legis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;
alter table br_votacoes add key legis_index(legis);
alter table br_votacoes add key date_index(rcdate);
alter table br_votacoes add key rcfile_index(rcfile);


DROP TABLE IF EXISTS `br_billid`;
CREATE TABLE  `br_billid` (
    `billyear` int,
    `billno` int,
    `billtype` varchar(10),
    `billid` int,
    --    `billurl` varchar(100),
    PRIMARY KEY  (`billtype`,`billyear`,`billno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;


DROP TABLE IF EXISTS `br_bills`;
CREATE TABLE  `br_bills` (
    -- `session` varchar(9), 
    `billyear` int,
    `billauthor` varchar(100),
    `billauthorid` int,
    `billdate` date,
    `billno` int,
    `billid` int,
    `propno` int,
    `billtype` varchar(10),
    --    `legis` int,
    `aprec` varchar(255),
    `tramit` varchar(255),
    `status` varchar(255),    
    `ementa` varchar(1000),
    `ementashort` varchar(1000),
    `indexa` varchar(1000),
    `lastaction` varchar(1000),
    `lastactiondate` date,
    PRIMARY KEY  (`billtype`,`billyear`,`billno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;



DROP TABLE IF EXISTS `br_tramit`;
CREATE TABLE  `br_tramit` (
    `billid` int,
    `id` int,
    `date` date,
    `event` varchar(1000),
    PRIMARY KEY  (`billid`,`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;









-- table to hold all deputados
DROP TABLE IF EXISTS `br_deputados`;
CREATE TABLE IF NOT EXISTS `br_deputados` (
  `namelegis` varchar(100),
  `party` varchar(10),
  `state` varchar(2),
  `type` varchar(1),
  `address` varchar(50),
  `building` varchar(11),
  `address2` varchar(50),
  `office` varchar(10),
  `address3` varchar(50),
  `phone` varchar(20),
  `fax` varchar(20),
  `birthmonth` int(2) default NULL,
  `birthdate` int(2) default NULL,
  `mailaddress` varchar(100),
  `namelegisclean` varchar(100),
  `title` varchar(50),
  `profession` varchar(200),
  `name` varchar(100),
  `loaddate` varchar(10),
  `bioid` int(10)	 
  --   PRIMARY KEY (`name`,`party`,`state`,`address`,`building`,`address2`,`office`	,`address3`,`phone`,`fax`,`birthmonth`,`birthdate`,`mailaddress`,`title`,`profession`)
  --  PRIMARY KEY (`name`,`party`,`state`,`bioid`)	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 -- Table with the current deputies
DROP TABLE IF EXISTS `br_deputados_current`;
CREATE TABLE br_deputados_current like br_deputados;	 


-- add primary indexes to br_deputados tables
alter table br_deputados_current add PRIMARY KEY (`bioid`);
alter table br_deputados add PRIMARY KEY (`bioid`,`loaddate`);
             

DROP TABLE IF EXISTS `br_bioidtse`;	
CREATE TABLE IF NOT EXISTS `br_bioidtse` (
  `state` varchar(2),
  `candidate_code` int(10),
  `bioid` int(10) default NULL,
  `year` int(4) default NULL,
  `office` varchar(30),
  PRIMARY KEY (`candidate_code`,`state`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  



-- ELECTORAL TABLES

DROP TABLE IF EXISTS `br_vote_section`;
CREATE TABLE  `br_vote_section` (
    `year` int(11) NOT NULL DEFAULT '0',
    `elec_round` int(11) DEFAULT NULL,
    `municipality` int(11) NOT NULL DEFAULT '0',
    `office` varchar(20) NOT NULL DEFAULT '',
    `type` int(11) DEFAULT NULL,
    `candidate_code` int(11) NOT NULL DEFAULT '0',
    `section` int(11) NOT NULL DEFAULT '0',
    `zone` int(11) NOT NULL DEFAULT '0',
    `votes` int(11) DEFAULT NULL,
    `state` varchar(2) DEFAULT NULL,
    PRIMARY KEY (`year`,`office`,`candidate_code`,`municipality`,`zone`,`section`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `br_vote_mun`;
CREATE TABLE  `br_vote_mun` (
    `year` int(11) NOT NULL DEFAULT '0',
    `elec_round` int(11) DEFAULT NULL,
    `municipality` int(11) NOT NULL DEFAULT '0',
    `office` varchar(20) NOT NULL DEFAULT '',
    `type` int(11) DEFAULT NULL,
    `candidate_code` int(11) NOT NULL DEFAULT '0',
    `votes` int(11) DEFAULT NULL,
    `state` varchar(2) DEFAULT NULL,
    PRIMARY KEY (`year`,`office`,`candidate_code`,`municipality`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table br_vote_mun add key yearround_index(year, elec_round);
alter table br_vote_mun add key office_index(year, office);


alter table br_vote_mun  PARTITION BY LIST(year) (
    PARTITION e1990 VALUES IN (1990),
    PARTITION e1992 VALUES IN (1992),
    PARTITION e1994 VALUES IN (1994),
    PARTITION e1996 VALUES IN (1996),
    PARTITION e1998 VALUES IN (1998),
    PARTITION e2000 VALUES IN (2000),
    PARTITION e2002 VALUES IN (2002),
    PARTITION e2004 VALUES IN (2004),
    PARTITION e2006 VALUES IN (2006),
    PARTITION e2008 VALUES IN (2008),
    PARTITION e2010 VALUES IN (2010),
    PARTITION e2012 VALUES IN (2012)
);





DROP TABLE IF EXISTS `br_vote_parties`;
CREATE TABLE  `br_vote_parties` (
    `party` int(11) NOT NULL DEFAULT '0',
    `partyl` varchar(10) DEFAULT NULL,
    `partyname` varchar(100) DEFAULT NULL,
    `year` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`party`,`year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `br_vote_geocode_addresses`;
CREATE TABLE `br_vote_geocode_addresses` (
  `date` bigint(10),
  `state` varchar(2),
  `municipality` bigint(20) DEFAULT NULL,
  `municipality_name` varchar(100),
  `bairro` varchar(100),
  `zipcode` bigint(10) DEFAULT NULL,
  `country` varchar(50),
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `geocode_status` varchar(20) DEFAULT NULL,
  `geocode_match` varchar(20) DEFAULT NULL,
  `address` varchar(100),
  PRIMARY KEY (date, municipality, address, bairro, zipcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- alter table br_locations add key yearround_index(year, elec_round);
-- alter table br_vote_mun add key office_index(year, office);

DROP TABLE IF EXISTS `br_vote_geocode_zipcodes`;
CREATE TABLE `br_vote_geocode_zipcodes` (
  `date` bigint(10),
  `state` varchar(2),
  `zipcode` bigint(10) DEFAULT NULL,
  `country` varchar(50),
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `geocode_status` varchar(20) DEFAULT NULL,
  `geocode_match` varchar(20) DEFAULT NULL,
  PRIMARY KEY (date, state, zipcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- alter table br_locations add key yearround_index(year, elec_round);
-- alter table br_vote_mun add key office_index(year, office);

DROP TABLE IF EXISTS `br_vote_geocode_municipalities`;
CREATE TABLE `br_vote_geocode_municipalities` (
  `date` bigint(10),
  `municipality` bigint(20) DEFAULT NULL,	
  `municipality_name` varchar(100),
  `state` varchar(2),
  `country` varchar(50),
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `geocode_status` varchar(20) DEFAULT NULL,
  `geocode_match` varchar(20) DEFAULT NULL,
  PRIMARY KEY (date, state, municipality)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- alter table br_locations add key yearround_index(year, elec_round);
-- alter table br_vote_mun add key office_index(year, office);

DROP TABLE IF EXISTS `br_vote_locations`;
CREATE TABLE `br_vote_locations` (
  `date` bigint(10),
  `cod` bigint(20) DEFAULT NULL,
  `state` varchar(2),
  `municipality` bigint(20) DEFAULT NULL,
  `location_code` varchar(20),
  `location_name` varchar(100),
  `municipality_name` varchar(100),
  `zone` bigint(20) DEFAULT NULL,
  `section` bigint(20) DEFAULT NULL,
  `bairro` varchar(100),
  `zipcode` bigint(10) DEFAULT NULL,
  `country` varchar(50),
  `address` varchar(100),
  PRIMARY KEY (date, state, zone, section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- alter table br_locations add key yearround_index(year, elec_round);
-- alter table br_vote_mun add key office_index(year, office);




---- FIX accents
-- update br_municipios set municipality_tse06=cast(cast(municipality_tse06 as binary) as char);
-- update br_municipios set nome_meso=cast(cast(nome_meso as binary) as char);
-- update br_municipios set nome_micro=cast(cast(nome_micro as binary) as char);



-- update br_vote_candidates set name=cast(cast(name as binary) as char);
-- update br_vote_candidates set name_short=cast(cast(name_short as binary) as char);
-- update br_vote_candidates set sit=cast(cast(sit as binary) as char);




















