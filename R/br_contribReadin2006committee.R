#### Assembles donations to "comites", instead of "candidates"
#### This is only used to help identify donors, as many donors are comites and info is missing
#### for some of these. 
#### Follows same basic routin as in br_contribReadin2006. See that file for details
#
# OUTPUT
# contribcommittee2006.Rdta

rm(list=ls(all=TRUE))
run.from <-"C:/reps/CongressoAberto/DATA/CampaignContributions"
#this portion of the code does not neet do be ran again
    ### Unzip all state data into a single temporary folder
    setwd(paste(run.from,"/2006/COMITÊS/RECEITAS",sep=""))
    temp.dir <- paste(run.from,"/2006/TEMP-receitas-comites",sep="")
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
                             "factor","factor","factor","character"))
    names(d) <- c(
            "donorname",#NOME_DOADOR
            "cpfcgc",#CPF_CNPJ_DOADOR
            "donation",#VALOR_DOAÇÃO
            "date",#DATA_DOAÇÃO
            "donationtype1",#TIPO_RECURSO
            "partyno",#NR_PARTIDO
            "party",#SIGLA_PARTIDO
            "state",#UF_COMITÊ
            "committeetype",#TIPO_COMITÊ
            "committeecnpj"#CNPJ_COMITEE
            )
    if(txt.files[1]!=i){                #if not using first state, load previous states
            load("temp-contribcommittee2006.Rdta")
            d<-rbind(contrib,d)         #merge
            }
    contrib <- d                            #rename the object to save
    save(contrib,file="temp-contribcommittee2006.Rdta")#save all states together
    cat(nrow(contrib),"observations after reading",i,"\n")
    flush.console()
    rm(d,contrib)
    }
    load("temp-contribcommittee2006.Rdta")


clean.text<-function(x){
    y<-toupper(x)
    y<-gsub("Â","A", y) 
    y<-gsub("Á","A", y)
    y<-gsub("Ã","A", y)
    y<-gsub("É","E", y)
    y<-gsub("Ê","E", y)
    y<-gsub("Í","I", y)
    y<-gsub("Ó","O", y)
    y<-gsub("Ô","O", y)
    y<-gsub("Õ","O", y)
    y<-gsub("Ú","U", y)
    y<-gsub("Ü","U", y)
    y<-gsub("Ç","C", y)
    y<-gsub("*","", y, fixed=TRUE)
    y<-gsub("'"," ", y)
    y<-gsub("."," ", y, fixed=TRUE)  
    y<-gsub("-"," ", y, fixed=TRUE)    
    y<-gsub("/","", y, fixed=TRUE)
    y<-gsub("  "," ", y)
    return(y)
}
contrib$name <- clean.text(contrib$name)
contrib$donorname <- clean.text(contrib$donorname) #clean donornames
contrib$donortype <- ifelse(is.element(contrib$cpfcgc,c("00000000000","00000000000000")),NA, #what out for invalids with 11 and 14 chars.
               ifelse(nchar(contrib$cpfcgc)==11,"PF",
               ifelse(contrib$donationtype2=="RECURSOS DE PARTIDO POLÍTICO","PP",
               ifelse(nchar(contrib$cpfcgc)==14,"PJ",NA))))
contrib$cpfcgc[is.na(contrib$donortype)]<-NA   #Use NA for invalid or no CPF-CGC
                                   #This category includes OTHER and INVALID
contrib$committeecnpj <- ifelse(nchar(contrib$committeecnpj)!=14,NA,contrib$committeecnpj)
save(contrib,file=paste(run.from,"/contribcommittee2006.Rdta",sep=""))
