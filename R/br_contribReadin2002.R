#### To do:                                                                                     
#### Deal with paths                                                                            
#### Export output table to mySQL  
####
#### Reads campaign contributions data obtained by Leoni
#### Uses only a subset of "Deputados Federais", but could run for all
#### 1-Cleans donor and candidate names
#### 2-Classifies type of donor based on CPF/CGC in PJ,PF
#### 3-Finds invalid CPF/CGC that can be internally corrected
####   invalid CPF/CGCs and "other" types of contributions are coded NAs in the cpfcgc column
#### 4-Assembles list of unique donors
#### 5-Redoes donortype classification:
####   6.1 Separates (roughly) "other" type of donation from "invalid" CPF/CGC entreies, 
####   6.2 Identifies party donations
####   so type is PF,PF,Other,PP,NA
#### 6-Replaces all variations of names with the most comon name for each CPF
####   Note that when there are 2 variations, the algorithms selects the "first"
####   The first variation, in thsese cases, might be the misspelled one. There is no real consequence
####   but it might be worth correcting this in the future (from the CPF/CGC online database?)
####
#### Inputs:
#### contrib2002.Rdta: just the raw data for all offices, read into R format
####
#### Outputs:
#### br_donorsunique2002fd.Rdta: unique donors for the 2002 federal deputy campaigns
#### br_donorsvariations2002fd.Rdta: list with the same lenght as the above, with all the name variations for each donor
#### br_contrib2002fd.csv:: donation data with corrected names and cpf/cgcs for fed. deptuty 2002
####                        to read these data use read.csv("br_contrib2002fd.csv",as.is=TRUE)
####
####
rm(list=ls(all=TRUE))
run.from <-"C:/reps/CongressoAberto/DATA/CampaignContributions"
setwd(run.from)

load("contrib2002.Rdta")
d <- d[d$office=="Deputado Federal",]
d$year <- 2002

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
d$name <- clean.text(d$name)
d$donorname <- clean.text(d$donorname) #clean donornames
d$donortype <- ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA, #what out for invalids with 11 and 14 chars.
               ifelse(nchar(d$cpfcgc)==11,"PF",
               ifelse(nchar(d$cpfcgc)==14,"PJ",NA)))
d$cpfcgc[is.na(d$donortype)]<-NA   #Use NA for invalid or no CPF-CGC
                                   #This category includes OTHER and INVALID

#Check for cases with  "invalid" CPF/CGCs that also appear with valid CPF/CGCs: 
#This is done before name replacement to make use of different spellings!!!!
unique.invalid <- unique(d[is.na(d$cpfcgc),"donorname"])    #invalid cpfcgc
cases.invalid <- nrow(d[is.na(d$cpfcgc),]) #just for on screen reporting
cat("Found",length(unique.invalid),"names/",sum(is.na(d$cpfcgc)),"cases, with invalid CPF/CGC\n")
unique.valid <- unique(d[is.na(d$cpfcgc)==FALSE,"donorname"]) #valid cpfcgc
unique.invalid.matched <- unique.invalid[is.element(unique.invalid,unique.valid)] #which are matched
for(i in unique.invalid.matched){
d$cpfcgc[which(is.na(d$cpfcgc)&d$donorname==i)] <- #replace missing CPFCGCs for matched name
            names(sort(table(d$cpfcgc[d$donorname==i]))[1]) #with most comon CPFCGC for that name
}
unique.invalid <- unique(d[is.na(d$cpfcgc),"donorname"])    #invalid cpfcgc after corrections
cat("\t",length(unique.invalid.matched),"names/",cases.invalid-nrow(d[is.na(d$cpfcgc),]),"cases with invalid CPF/CGC were corrected\n")

#cat("\t",length(unique.other),"'other' types of donor were identified\n")
#cat("\t",length(unique.invalid)-length(unique.other),"names/",sum(is.na(d$donortype)),"cases, with invalid CPF/CGC remain\n")
#cat("\t Invalid CPF/CGC correspond to",round(sum(d[is.na(d$donortype),"donation"])/sum(d$donation)*100,2),"% of total donations\n")

#Assemble unique donors             ###############################################
unique.donors <- data.frame(donor=NA,
                            cpfcgc=as.character(na.omit(unique(d$cpfcgc))), #drop NA's
                            variations=NA,
                            e2002=TRUE) #create dataframe to store unique donor info
variations <- list() #create object to store different spellings
for(i in 1:nrow(unique.donors)){
    donors <- sort(table(as.character(d$donorname[d$cpfcgc==unique.donors$cpfcgc[i]])),decreasing=TRUE)#find all name variations for a given cpfcgc
    unique.donors$donor[i] <- names(donors)[1]      #use most comon name variation
    unique.donors$variations[i] <- length(donors)   #take note of number of different spellings
    variations[[i]] <- names(donors)                #store, in a separate object, all different spellings
    if(round(i/500)-i/500==0){cat("Done with first",i,"unique donors\n")} #report advances periodically
    flush.console()
}
write.csv(unique.donors,file="br_donorsunique2002fd.csv",row.names=FALSE)
save(variations,file="br_donorsvariations2002fd.Rdta")

#Redo "donortype" classification
d$donortype <- ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA, #what out for invalids with 11 and 14 chars.
               ifelse(nchar(d$cpfcgc)==11,"PF",
               ifelse(nchar(d$cpfcgc)==14,"PJ",NA)))
party.donors <- union(union(union(union(     #Classify party donations are classified as such  
                 grep("COMITE",d$donorname),
                 grep("DIRETORIO",d$donorname)),
                 grep("PARTIDO",d$donorname)),
                 grep("CANDIDATO",d$donorname)),
                 grep("DIRECAO",d$donorname))
d$donortype[party.donors]<-"PP"
other <- union(grep("EVENTOS",d$donorname),grep("APLICACOES",d$donorname))#Separate INVALIDS from OTHER sources  #
d$donortype[other] <- "Other" #change donortype from NA to "OTHER" 


### Standarize: One name for each CPF.
d$donor<- d$donorname #create new name field, typically the same as old names
for(i in which(unique.donors$variations>1)){ #replace newnames of cases with variations with the most comon name
d$donor[which(as.character(d$cpfcgc)==as.character(unique.donors$cpfcgc[i]))] <- as.character(unique.donors$donor[i])
}
write.csv(d,file="br_contrib2002fd.csv",row.names=FALSE)

#dd<-read.csv("br_contrib2002fd.csv",as.is=TRUE)
