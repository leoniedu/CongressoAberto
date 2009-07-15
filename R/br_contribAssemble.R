## Reads the raw TSE data on campaign contributions into R format                               
## saves a single Rdta file for each year with all contributions                                
## For 2006, this requires assembling data from different files. Note that original data are in 
##   subdirectories with the state acronyms which contain one zip file names RECEITAS_CANDIDATOS_XX.zip, 
##   which contains .txt and .xls files The .txt files are semicolon separated. Saved           
## Output:
## contrib2002.Rdta
## contrib2006.Rdta

## FOR 2002, pretty simple
##rm(list=ls(all=TRUE))
rf <- function() {
  if (.Platform$OS.type!="unix") {
    "C:/reps/CongressoAberto"
  } else {
    "~/reps/CongressoAberto"
  }
}
run.from <- rf()

setwd(run.from)

d <- read.delim(paste(run.from,"/data/CampaignContributions/2002/ReceitaCandidato.txt",sep=""), header = FALSE, sep = "\t", quote="\"", dec=",",##skip=100,
              fill = TRUE, nrows = -1, strip.white = TRUE, as.is=TRUE, 
              colClasses=c("factor","factor","factor","character","numeric","character","character",
                "factor","character","numeric","character"),encoding="latin1")
##"
names(d) <- c(
              "state",# - Sigla da unidade da federacao - caracter (2)
              "party",# - sigla do partido - caracter (5)
              "office",# - Descricao do cargo - caracter (25)
              "name",# - Nome do candidato - caracter (70)
              "candno",# - Numero do candidato numero (5)
              "date",# - Data da realizacao da receita - DATA
              "cpfcgc",# - Inscricao no CPF ou CNPJ do doador - caracter (14)
              "donorstate",# - Sigla da unidade da federacao do doador - caracter (2)
              "donorname",# - Nome do doador - caracter (64)
              "donation",# - Valor da doacao numero (15,2)
              "donationtype"# - tipo do recurso - caracter (8)
              )
save(d,file=paste(run.from,"/DATA/CampaignContributions/contrib2002.Rdta",sep=""))

### For 2006, slightly more complicated  ################################################

### Assumes raw data are in 2006/CANDIDATOS/RECEITAS
## get list of zip files
temp.dir <- paste(run.from,"/data/CampaignContributions/2006/TEMP-receitas",sep="") 
lzips <- dir(paste(run.from,"/data/CampaignContributions/2006/CANDIDATOS/RECEITAS",sep=""),recursive=TRUE,pattern="zip",full.names=TRUE)
## unzip them to temp dir
lapply(lzips,unzip,junkpaths=TRUE,exdir=temp.dir,overwrite=FALSE)
## get list of txt files
txt.files <-  dir(temp.dir,pattern="TXT",full.names=TRUE)
## function to read one txt file
read1 <- function(i) {
  d <- read.csv2(i,header=FALSE,strip.white = TRUE,
                 colClasses=c("character","character","numeric","character","character","numeric","character",
              "factor","numeric","factor","factor","character","character"))
  names(d) <- c(
                "donorname",#NOME_DOADOR
                "cpfcgc",#CPF_CNPJ_DOADOR
                "donation",#VALOR_DOAÇÃO
                "date",#DATA_DOAÇÃO
                "donationtype1",#TIPO_RECURSO
                "candno",#NR_CANDIDATO
                "name",#NOME_CANDIDATO
                "office",#CARGO_ELETIVO
                "partyno",#NR_PARTIDO
                "party",#SIGLA_PARTIDO
                "state",#UF_CANDIDATO
                "candcnpj",#CNPJ_CAMPANHA_CANDIDATO
                "donationtype2"#TIPO_DOAÇÃO
                )
  d
}
## read them and put in a list
contrib.list <- lapply(txt.files,read1)
## rbind the list
contrib <- do.call(rbind,contrib.list)
## save
save(contrib,file=paste(run.from,"/Data/CampaignContributions/contrib2006.Rdta",sep=""))
