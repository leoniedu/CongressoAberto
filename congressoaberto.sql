--- In order for gvstreamer towork, and for efficiency reasons as well, we will not have "text" fields in the database (unless it is sorely needed.)



SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"; -- Normally, you generate the next sequence number for the column by inserting either NULL or 0 into it. NO_AUTO_VALUE_ON_ZERO suppresses this behavior for 0 so that only NULL generates the next sequence number.

--
-- Database: `congressoaberto`;
--

-- We will use InnoDB engine, which allows for foreign keys. (That we might want to use.)
-- --------------------------------------------------------

use congressoaberto;

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
    PRIMARY KEY  (`bioid`,`rcfile`(30),`legis`)
    ) 
-- ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COMMENT=''
-- PARTITION BY HASH(legis)
-- PARTITIONS 6
;
alter table br_votos add key rcfile_index(rcfile);


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
    PRIMARY KEY  (`rcfile`(30),`legis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;
alter table br_votacoes add key legis_index(legis);


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
    `billdate` varchar(10),
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
    `lastactiondate` varchar(100),
    PRIMARY KEY  (`billtype`,`billyear`,`billno`)
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


DROP TABLE IF EXISTS `br_vote_parties`;
CREATE TABLE  `br_vote_parties` (
    `party` int(11) NOT NULL DEFAULT '0',
    `partyl` varchar(10) DEFAULT NULL,
    `partyname` varchar(100) DEFAULT NULL,
    `year` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`party`,`year`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;





















