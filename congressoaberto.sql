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
    `legisserved` text,
    `prevparties` text,
    `mandates` text,
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

--PARTITION BY HASH( YEAR(hired) )
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
    `namelegis` text,
    `party` text,
    `state` text,
    `rc` varchar(20),
    `rcfile` varchar(30),
    `rcvoteid` int,
    `bioid` int,
    PRIMARY KEY  (`bioid`,`rcfile`(30),`legis`)
    ) 
-- ENGINE=InnoDB 
DEFAULT CHARSET=utf8 COMMENT=''
PARTITION BY HASH(legis)
PARTITIONS 6
;
alter table br_votos add key rcfile_index(rcfile);


DROP TABLE IF EXISTS `br_votacoes`;
CREATE TABLE  `br_votacoes` (
    `rcvoteid` int default NULL,
    `session` varchar(9), 
    `billtext` text,
    `rcdate` text,
    `billproc` text,
    `billdescription` text,
    `bill` text,
    `legisyear` int,
    `rcyear` int,
    `billyear` int,
    `billno` text,
    `billtype` varchar(30),
    `legis` int,
    `rcfile` varchar(30),
    PRIMARY KEY  (`rcfile`(30),`legis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;
alter table br_votacoes add key legis_index(legis);

  res <- try(data.frame(## billtype=f(sigla), ##FIX GET FROM FILE
                        ## billno=f(numero),
                        ## billyear=f(ano),
                        billid=f(propno),
                        author=f(author),
                        date=f(date),
                        aprec=f(aprec),
                        tramit=f(tramit),
                        status=f(status),
                        ementa=f(ementa),
                        ementashort=f(ementashort),
                        indexa=f(indexa),
                        stringsAsFactors=FALSE))

DROP TABLE IF EXISTS `br_billid`;
CREATE TABLE  `br_billid` (
    `billyear` int,
    `billno` int,
    `billtype` varchar(10),
    `billid` int,
    `billurl` varchar(100),
    PRIMARY KEY  (`billtype`,`billyear`,`billno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;


DROP TABLE IF EXISTS `br_bills`;
CREATE TABLE  `br_bills` (
    `session` varchar(9), 
    `billyear` int,
    `billauthor` varchar(100),
    `billdate` varchar(10),
    `billno` int,
    `billtype` varchar(10),
    `legis` int,
    `aprec` text,
    `tramit` text,
    `status` text,    
    `ementa` text,
    `ementashort` text,
    `indexa` text,
    `url` varchar(100),
    PRIMARY KEY  (`billtype`,`billyear`,`billno`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
;



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






--- TODO BELOW HERE


CREATE TABLE  `br_cisidbioid` (
    `row_names` text,
    `bioid` text,
    `cisid` text,
    `dist` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE  `br_bioidtseid06` (
    `row_names` text,
    `bioid` text,
    `tseid` text,
    `dist` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `br_cis` (
    `row_names` text,
    `tseid` text,
    `id` text,
    `office` text,
    `state` text,
    `region` text,
    `name` text,
    `number` double default NULL,
    `party` text,
    `coalition` text,
    `coalitcomp` text,
    `birth` text,
    `birthyear` double default NULL,
    `age` double default NULL,
    `ageag` text,
    `sex` text,
    `married` text,
    `occupation` text,
    `occupationag` text,
    `education` text,
    `nationality` text,
    `citybith` text,
    `statebirth` text,
    `regionbirth` text,
    `situation` text,
    `spendmax` double default NULL,
    `spendmaxag` text,
    `wealth` double default NULL,
    `wealthag` text,
    `obs` text,
    `votes` double default NULL,
    `percovote` double default NULL,
    `outcome` text,
    `firstlast` text,
    `cisid` text,
    `cargo` text,
    `nome2` text,
    `nome` text,
    `uf` text,
    `situacao` text,
    `votos` double default NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `br_tse2006legis` (
    `uf` text,
    `municipio` text NOT NULL,
    `zona` bigint(20) default NULL,
    `cargo` text,
    `nome` text,
    `numero` bigint(20) default NULL,
    `nome_urna` text,
    `partido_sigla` text,
    `legenda` text,
    `votos` bigint(20) default NULL,
    `situacao` text,
    `office` text,
    `tseid` text NOT NULL,
    `zonachar` varchar(20) NOT NULL default '',
    PRIMARY KEY  (`tseid`(30),`municipio`(30),`zonachar`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `br_tse2006mun` (
    `uf` text,
    `municipio` text NOT NULL,
    `cargo` text,
    `nome` text,
    `numero` bigint(20) default NULL,
    `nome_urna` text,
    `partido_sigla` text,
    `legenda` text,
    `situacao` text NOT NULL,
    `votos` bigint(20) default NULL,
    `tseid` text NOT NULL,
    PRIMARY KEY  (`tseid`(30),`municipio`(40),`situacao`(10))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


