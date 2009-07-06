## TODO                                                                                         
## 1. Deal with paths                                                                           
## Reads the raw TSE data on campaign contributions into R format                               
## saves a single Rdta file for each year with all contributions                                
## For 2006, this requires assembling data from different files. Note that original data are in 
##   subdirectories with the state acronyms which contain one zip file names RECEITAS_CANDIDATOS_XX.zip, 
##   which contains .txt and .xls files The .txt files are semicolon separated. Saved           
## Output:
## contrib2002.Rdta
## contrib2006.Rdta

# FOR 2002, pretty simple
rm(list=ls(all=TRUE))
run.from <-"C:/reps/CongressoAberto"
setwd(run.from)

    d<-read.delim(paste(run.from,"/Data/CampaignContributions/2002/ReceitaCandidato.txt",sep=""), header = FALSE, sep = "\t", quote="\"", dec=",",
           fill = TRUE, nrows = -1, strip.white = TRUE, as.is=TRUE, 
           colClasses=c("factor","factor","factor","character","numeric","character","character",
                        "factor","character","numeric","character"))
    #"
    names(d) <- c(
           "state",# - Sigla da unidade da federação - caracter (2)
           "party",# - sigla do partido - caracter (5)
           "office",# - Descrição do cargo - caracter (25)
           "name",# - Nome do candidato - caracter (70)
           "candno",# - Numero do candidato – número (5)
           "date",# - Data da realização da receita - DATA
           "cpfcgc",# - Inscrição no CPF ou CNPJ do doador - caracter (14)
           "donorstate",# - Sigla da unidade da federação do doador - caracter (2)
           "donorname",# - Nome do doador - caracter (64)
           "donation",# - Valor da doacao número (15,2)
           "donationtype"# - tipo do recurso - caracter (8)
            )
    save(d,file=paste(run.from,"/DATA/CampaignContributions/contrib2002.Rdta",sep=""))


### For 2006, slightly more complicated  ################################################
### Assumes raw data are in 2006/CANDIDATOS/RECEITAS
rm(list=ls(all=TRUE))
run.from <-"C:/reps/CongressoAberto"
setwd(run.from)
    ### Unzip all state data into a single temporary folder
    setwd(paste(run.from,"/Data/CampaignContributions/2006/CANDIDATOS/RECEITAS",sep=""))
    temp.dir <- paste(run.from,"/Data/CampaignContributions/2006/TEMP-receitas",sep="") 
    dir.create(temp.dir, showWarnings = TRUE, recursive = FALSE, mode = "0777")
    subdirs <- dir(getwd())[-grep("\\.",dir(getwd()))]  #find subdirectories with state data   
    root <- getwd()
    for(i in subdirs){
        setwd(paste(root,i,sep="/"))  #move to state subdirectory
        zip.file <- dir()[grep("zip",dir())] #identify zip file
        cat("Unziping",zip.file,"\n")
        zip.unpack(zip.file, dest=temp.dir)
        flush.console()
    }
    
    ### Assemble singe set of contributions to federal deputies
    setwd(temp.dir)
    txt.files <-  dir()[grep("TXT",dir())] 
    for(i in txt.files){ #rean in state by state
        d<-read.csv2(i,header=FALSE,strip.white = TRUE,
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
    if(txt.files[1]!=i){                #if not using first state, load previous states
            load("temp-contrib2006.Rdta")
            d<-rbind(contrib,d)         #merge
            }
    contrib <- d                            #rename the object to save
    save(contrib,file="temp-contrib2006.Rdta")#save all states together
    cat(nrow(contrib),"observations after reading",i,"\n")
    flush.console()
    rm(d,contrib)
    }
    load("temp-contrib2006.Rdta")
    save(contrib,file=paste(run.from,"/Data/CampaignContributions/contrib2006.Rdta",sep=""))
