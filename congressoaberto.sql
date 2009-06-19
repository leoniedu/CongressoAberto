SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"; -- Normally, you generate the next sequence number for the column by inserting either NULL or 0 into it. NO_AUTO_VALUE_ON_ZERO suppresses this behavior for 0 so that only NULL generates the next sequence number.

--
-- Database: `congressoaberto`
--

-- We will use InnoDB engine, which allows for foreign keys. (That we might want to use.)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `br_bio`;
CREATE TABLE IF NOT EXISTS `br_bio` (
    `bioid` int COMMENT 'You can add a comment here',
    `nameshort` varchar(100),
    `name` varchar(100),
    `partynow` varchar(10),
    `birth` varchar(10),
    `birthplace` varchar(100),
    `sessions` text,
    `parties` text,
    `mandates` text,
    `biofile` varchar(100),
    `imagefile` varchar(100),
    `nameindex` varchar(100),
    `state` varchar(2),
    PRIMARY KEY  (`bioid`) -- NOTE: A primary key has to be unique
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Holds the biography information available at the Camara site. 
CreatedBy=bioprocess.R
updatedBy=
';


DROP TABLE IF EXISTS `br_bioidname`;
CREATE TABLE IF NOT EXISTS `br_bioidname` (
    `bioid` int,
    `name` varchar(100),
    `state` varchar(2),
    `sessions` varchar(9),
    PRIMARY KEY  (`bioid`,`name`(100),`state`(2))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between biography id and names. Used in the function reading roll calls to match bioid to the roll call id. 
CreatedBy=bioprocess.R
updatedBy=function readOne 
';

DROP TABLE IF EXISTS `br_idbioid`;
CREATE TABLE IF NOT EXISTS `br_idbioid` (
    `bioid` int,
    `id` int,
    `sessions` varchar(9),
    PRIMARY KEY  (`bioid`,`id`,`session`(9))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='
Content=Link table between biography id and the roll call id
CreatedBy=readOne
updatedBy=readOne';



DROP TABLE IF EXISTS `br_votos`;
CREATE TABLE IF NOT EXISTS `br_votos` (
    `id` int,
    `sessions` varchar(9),
    `name` text,
    `party` text,
    `state` text,
    `voto` varchar(20),
    `filename` varchar(30),
    `voteid` int,
    `bioid` int,
    PRIMARY KEY  (`bioid`,`filename`(30))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';


--`session` belowe comes from rollcall metadata (e.g. CC02O008O). We should rename one or the other to avoid confusion
DROP TABLE IF EXISTS `br_votacoes2`;
CREATE TABLE IF NOT EXISTS `br_votacoes2` (
    `voteid` int default NULL,
    `session` varchar(9), 
    `bill` text,
    `data` text,
    `texordia` text,
    `descricao` text,
    `proposicao` text,
    `anolegislativo` int,
    `anovotacao` int,
    `ano` int,
    `numero` text,
    `tipo` varchar(30),
    `sessions` varchar(9),
    `filename` varchar(30),
    PRIMARY KEY  (`filename`(30))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--- TODO BELOW HERE


CREATE TABLE IF NOT EXISTS `br_cisidbioid` (
    `row_names` text,
    `bioid` text,
    `cisid` text,
    `dist` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `br_bioidtseid06` (
    `row_names` text,
    `bioid` text,
    `tseid` text,
    `dist` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `br_cis` (
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

CREATE TABLE IF NOT EXISTS `br_tse2006legis` (
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

CREATE TABLE IF NOT EXISTS `br_tse2006mun` (
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


