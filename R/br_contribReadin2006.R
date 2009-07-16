#### To do:                                                                                                                                                     
#### Export output table to mySQL                                                               
#### Incorporate Leoni's techinque for a faster routine                                           
####                                                                                          
#### Jul 16: Fixed issue with empty string names, which caused wrong CPF assignment to these cases
####         and consequent misclassification of "OTHERS", and a few other errors.              
####                                                                                            
#### Reads campaign contributions data obtained by Leoni                                        
#### Uses only a subset of "Deputados Federais", but could run for all                          
#### 1-Cleans donor and candidate names                                                         
#### 2-Classifies type of donor based on CPF/CGC in PJ,PF and (based on donationtype2) PP or Other
####   Other is basically "rendimentos de aplicacoes financeiras".                              
####   Note that this misses very few diretorios that have a CNPJ and are classified as PJ by the 
###    TSE. This correction is done at the end of the routine                                   
####   At this point, we DO NOT store information regarding which level of the party donated funds 
#### 3-Finds invalid CPF/CGC that can be internally corrected                                   
####   invalid CPF/CGCs and "other" types of contributions are coded NAs in the cpfcgc column   
####   As many of the invalid CPF/CGCs are from "campaigns" that donate funds, this step        
###    includes:                                                                                
###     3.1 - A very lengthy routine that parses the donor-cmapaing data and looks up its cnpj  
####          in the full contrib database.                                                     
####    3.2 - A less lengthy routine that parses donor-committee data and looks up its cnpj     
####          in the contribcommittee database                                                  
#### 4-Corrects donor type classification after matching, and does appropriate identification   
####   of party donors, converting some PJs to PPs.                                             
#### 5-Assembles list of unique donors                                                          
#### 6-Replaces all variations of names with the most comon name for each CPF                   
####   Note that when there are 2 variations, the algorithms selects the "first" variation which
####   might be the misspelled one. There is no real consequence but it might be worth          
####   correcting this in the future (from the CPF/CGC online database?)                        
####
#### Inputs:                                                                                    
#### contrib2006.Rdta: just the raw data for all offices, read into R format                    
#### Outputs:                                                                                   
#### br_donorsunique2006fd.Rdta: unique donors with CPF/CGC for the 2006 federal deputy campaigns               
#### br_donorsvariations2006fd.Rdta: list with the same lenght as the above, with all the name  
####                                  variations for each donor                                 
#### br_contrib2006fd.csv:: donation data with corrected names and cpf/cgcs for fed deptuty 2006
####                        IMPORTANT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                             
####                        to READ THIS FILE: read.csv("br_contrib2006fd.csv",as.is=TRUE)      
####                        as.is is important because some cpfs and cgcs start with zeros!     
####                                                                                            
################################################################################################
library(reshape)
rm(list=ls(all=TRUE))
tmyear <- proc.time() 

## paths
rf <- function() {
  if (.Platform$OS.type!="unix") {
    "C:/reps/CongressoAberto/data/CampaignContributions" #added /data/CampaginContributions.... 
  } else {
    "~/reps/CongressoAberto/CampaignContributions"
  }
}
run.from <- rf()

setwd(run.from)
load("contrib2006.Rdta")
d <- contrib[contrib$office=="Deputado Federal",-14]
rm(contrib)
d$year <- 2006

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
d$donorname <- ifelse(d$donorname=="",NA,d$donorname) #convert "" to NA
d$donortype <- ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA, #what out for invalids with 11 and 14 chars.
               ifelse(nchar(d$cpfcgc)==11,"PF",
               ifelse(d$donationtype2=="RECURSOS DE PARTIDO POLÍTICO","PP",
               ifelse(d$donationtype2=="RECURSOS DE OUTROS CANDIDATOS/COMITÊ","PP",
               ifelse(nchar(d$cpfcgc)==14,"PJ",
                      ifelse(d$donationtype2=="Rendimentos de aplicações financeiras","Other",NA))))))
d$cpfcgc<- ifelse(d$cpfcgc=="",NA,#Use NA for invalid or no CPF-CGC
                  ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA,
                         ifelse(nchar(d$cpfcgc==11)|nchar(d$cpfcgc==14),as.character(d$cpfcgc),NA)))
                         
###### TRY TO CORRECT INVALID CPF/CGCS ##############################################################################
#INTERNAL CHECK: Check for cases with  "invalid" CPF/CGCs that also appear with valid CPF/CGCs: #####################
#This is done before name replacement to make use of different spellings!!!!
unique.invalid <- na.omit(unique(d[is.na(d$cpfcgc),"donorname"]))    #invalid cpfcgc
cases.invalid <- nrow(d[is.na(d$cpfcgc),]) #just for on screen reporting
cat("Found",length(unique.invalid),"names/",sum(is.na(d$cpfcgc)),"cases, with invalid or missing CPF/CGC\n")
unique.valid <- na.omit(unique(d[is.na(d$cpfcgc)==FALSE,"donorname"])) #valid cpfcgc
unique.invalid.matched <- unique.invalid[is.element(unique.invalid,unique.valid)] #which are matched
for(i in unique.invalid.matched){
d$cpfcgc[which(is.na(d$cpfcgc)&d$donorname==i)] <- #replace missing CPFCGCs for matched name
            names(sort(table(d$cpfcgc[d$donorname==i]),decreasing=TRUE)[1]) #with most comon CPFCGC for that name
}
unique.invalid <- unique(d[is.na(d$cpfcgc),"donorname"])    #invalid cpfcgc after corrections
cat("\tINTERNAL CORRECTION: fixed",length(unique.invalid.matched),"names/",cases.invalid-nrow(d[is.na(d$cpfcgc),]),"cases with invalid CPF/CGC\n")
cases.invalid <- nrow(d[is.na(d$cpfcgc),]) #for reporting on screen
cat("\tTime elapsed:",((proc.time()-tmyear)[3])/60,"mins","\n")

#EXTERNAL CHECK: look the CNPJ of campaigns and committees in other databases  #######################################
unique.invalid.data <- unique(d[which(is.na(d$cpfcgc)&   #unique candidates or committee contributors missing cnpj
                              d$donationtype2=="RECURSOS DE OUTROS CANDIDATOS/COMITÊS"),"donorname"]  )

# CAMPAIGNS: Here we look up the CNPJ of campaigns that donated to other campaings       
#It is necessary to parse information form the donorname field to search for candidates in original contrib file                                                               
unique.invalid.cand <- unique.invalid.data[-grep("COMITE",
                                    unique.invalid.data)]#get rid of commitees, keep only campaigns
unique.invalid.cand <- data.frame(orig=as.character(unique.invalid.cand),  #parse candidate campaign info
           name=as.character(gsub("^(.*)\\s(\\d{2,5})\\s(\\w{2})$","\\1",unique.invalid.cand,perl=TRUE)),
           candno=as.character(gsub("^(.*)\\s(\\d{2,5})\\s(\\w{2})$","\\2",unique.invalid.cand,perl=TRUE)),
           state=as.character(gsub("^(.*)\\s(\\d{2,5})\\s(\\w{2})$","\\3",unique.invalid.cand,perl=TRUE)))
cat("\tFound",nrow(unique.invalid.cand),"campaings that donated and have missing CNPJ\n\t")
load("contrib2006.Rdta")
for(j in 1:nrow(unique.invalid.cand)){ #for each campaing donor with missing CNPJ
    the.cnpj <- names(sort(table(
                contrib$candcnpj[which(contrib$state==as.character(unique.invalid.cand$state[j])&
                contrib$candno==as.character(unique.invalid.cand$candno[j]))]
                ),decreasing=TRUE))[1] #get most common among all used cnp
    d[which(d$donorname==as.character(unique.invalid.cand$orig[j])),"cpfcgc"]<-
                            ifelse(is.null(the.cnpj),NA,the.cnpj) #replace missing CPFCGC with correct number
    if(round(j/100)-j/100==0){cat(j,"...")
                              flush.console()} #report advances periodically 
}

flush.console()
rm(contrib)
unique.invalid <- unique(d[is.na(d$cpfcgc),"donorname"])    #invalid cpfcgc after corrections, for future reference
cat("\tTime elapsed:",((proc.time()-tmyear)[3])/60,"mins","\n")

# COMMITTEESS Now, do something similar for COMMITTEEs, source of data is contrib2006committee.Rdta
unique.invalid.com <- unique.invalid.data[grep("COMITE",  #now, do something similar for committees
                                    unique.invalid.data)] 
unique.invalid.com <- gsub("\\d","",unique.invalid.com,perl=TRUE) #get rid of numbers left over
unique.invalid.com <- data.frame(orig=unique.invalid.com,  #parse campaign info
           committee=gsub("^(.*)\\s(P\\w{2,7})\\s(\\w{2})$","\\1",unique.invalid.com,perl=TRUE),
           party=gsub("\\s$","",gsub("^.*\\s(P\\D{1,7})\\s\\w{2}\\s?$","\\1",unique.invalid.com,perl=TRUE)),
           state=gsub("^.*\\s(\\w{2})\\s?$","\\1",unique.invalid.com,perl=TRUE))
unique.invalid.com$party <- ifelse(nchar(as.character(unique.invalid.com$party))>7,
            gsub(".*\\s(\\w*)$","\\1",as.character(unique.invalid.com$party),perl=TRUE),
            as.character(unique.invalid.com$party))
unique.invalid.com$state <- ifelse(nchar(as.character(unique.invalid.com$state))>2,
            "ES", #manual fix for one case
            as.character(unique.invalid.com$state))
cat("\tFound",nrow(unique.invalid.com),"committees that donated and have missing CNPJ\n\t")
load("contribcommittee2006.Rdta")
contrib$committeecnpj <- ifelse(nchar(as.character(contrib$committeecnpj))!=14,NA,as.character(contrib$committeecnpj))
for(j in 1:nrow(unique.invalid.com)){ #for each campaing donor with missing CNPJ
    the.cnpj <- names(sort(table(
                contrib$committeecnpj[which(contrib$state==as.character(unique.invalid.com$state[j])&
                contrib$party==as.character(unique.invalid.com$party[j]))]
                ),decreasing=TRUE))[1] #find most comonly used cnpj
    if(unique.invalid.com$orig[j]==""){next}#skip if committee names is empty
    d[which(d$donorname==as.character(unique.invalid.com$orig[j])),"cpfcgc"]<-
                            ifelse(is.null(the.cnpj),NA,the.cnpj) #replace missing CPFCGC with correct number
    if(round(j/20)-j/20==0){cat(j,"...")} #report advances periodically 
}
flush.console()
rm(contrib)
cat("\tTime elapsed:",((proc.time()-tmyear)[3])/60,"mins","\n")
unique.invalid.data.2 <- unique(d[which(is.na(d$cpfcgc)&   #unique candidates or committee contributors missing cnpj
                              d$donationtype2=="RECURSOS DE OUTROS CANDIDATOS/COMITÊS"),"donorname"]  )
cat("\n\tEXTERNAL CORRECTION for Committees & Campaigns fixed",
            length(unique.invalid.data)-length(unique.invalid.data.2),"names\n")


# Redo donortype classification, after previous corrections of CPF/CGC  ###########################################
d$donortype <- ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA, #what out for invalids with 11 and 14 chars.
               ifelse(nchar(d$cpfcgc)==11,"PF",
               ifelse(d$donationtype2=="RECURSOS DE PARTIDO POLÍTICO","PP",
                 ifelse(d$donationtype2=="RECURSOS DE OUTROS CANDIDATOS/COMITÊ","PP",
               ifelse(nchar(d$cpfcgc)==14,"PJ",
               ifelse(d$donationtype2=="Rendimentos de aplicações financeiras","Other",
               ifelse(d$donationtype2=="Recursos próprios","Self",NA)))))))
party.donors <- union(union(  #aditional criteria to make sure party donations are classified as such
                grep("COMITE|Ê",d$donorname),grep("DIRETORIO",d$donorname))
                ,grep("PARTIDO",d$donorname))
d$donortype[party.donors]<-"PP"
d$cpfcgc<- ifelse(d$cpfcgc=="",NA,#Use NA for invalid or no CPF-CGC
           ifelse(is.element(d$cpfcgc,c("00000000000","00000000000000")),NA,
           ifelse(nchar(d$cpfcgc==11)|nchar(d$cpfcgc==14),as.character(d$cpfcgc),NA)))
by(d$donation,d$donortype,sum) 
cat("\t Invalid or missing CPF/CGC correspond to",round(100*sum(d$donation[is.na(d$cpfcgc)])/sum(d$donation),2),"% of total donations\n")
cat("\t If Political Parties are excluded, missingness is reduced to",round(sum(d[is.na(d$donortype),"donation"])/sum(d$donation)*100,2),"% of total donations\n")

#Assemble unique donors   ################################################################################################
cat("Assembling list of unique donors")
unique.donors <- data.frame(donor=NA,
                            cpfcgc=as.character(na.omit(unique(d$cpfcgc))), #drop NA's
                            variations=NA,
                            e2006=TRUE) #create dataframe to store unique donor info
variations <- list() #create object to store different spellings
for(i in 1:nrow(unique.donors)){
    donors <- sort(table(as.character(d$donorname[d$cpfcgc==unique.donors$cpfcgc[i]])),decreasing=TRUE)#find all name variations for a given cpfcgc
    unique.donors$donor[i] <- ifelse(is.null(names(donors)),"UNKOWN",names(donors)[1])   #use most comon name variation
                                                                                        #IFELSE is necessary because there might be empty name (NA) with valid CPF
    unique.donors$variations[i] <- length(donors)   #take note of number of different spellings
    variations[[i]] <- names(donors)                #store, in a separate object, all different spellings
    if(round(i/500)-i/500==0){cat(i,"...")
                              flush.console()} #report advances periodically
}
write.csv(unique.donors,file="br_donorsunique2006fd.csv",row.names=FALSE)
save(variations,file="br_donorsvariations2006fd.Rdta")


### Standarize: One name for each CPF.###################################################################################
cat("Standarizing names")
flush.console()
d$donor<- d$donorname #create new name field, typically the same as old names
for(i in which(unique.donors$variations>1)){ #replace newnames of cases with variations with the most comon name
d$donor[which(as.character(d$cpfcgc)==as.character(unique.donors$cpfcgc[i]))] <- as.character(unique.donors$donor[i])
}
write.csv(d,file="br_contrib2006fd.csv",row.names=FALSE)



cat("\tTime elapsed:",((proc.time()-tmyear)[3])/60,"mins","\n")
