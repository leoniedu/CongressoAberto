#party.relabel:  Considers legislators from old and new labels as being from the same party (with new label)
#                   Merges observations of the same legislator in parties that switched labels              
#                   Legislators that were in either new or old party, but not in both, are considered as being in new label
party.relabel<-function(d,a,b){
    #d is dataframe
    #a is partyA
    #b is partyB
    
    d$name<- as.character(d$name)
    new.d <- d[sort(d$name,index.return=TRUE)$ix,]
    in.ab <-  which(new.d$party==a|new.d$party==b)
    ab <- new.d[in.ab,]         #data set with repeated observations
    new.d <- new.d[-in.ab,]     #delete repeated observations from new.d 
    doubles <- as.numeric(which(duplicated(ab$name)))
    
    #pre.doubles <- which(duplicated(new.d$name)&(new.d$party==b|new.d$party==a))  
    #doubles <- pre.doubles[which(new.d$party[pre.doubles-1]==a|new.d$party[pre.doubles-1]==b)]
    for(y in doubles){
    if(ab$name[y]==ab$name[y-1]){                             #If repated names are in PP/PPB
            xx<-as.numeric(ab[y-1,grep("vote",names(ab))])  #Chose votes for first time guy shows
            yy<-as.numeric(ab[y,grep("vote",names(ab))])  #Chose votes for second time guy shows
            ab[y-1,grep("vote",names(ab))]<-ab[y,grep("vote",names(ab))]<-ifelse(is.na(xx),yy,xx)
            ab[y-1,"party"]<-b
    }}
    ab$party<-as.character(ab$party)     
    ab<-ab[-doubles,]        #get rid of doubles
    new.d<- rbind(new.d,ab)     #merge back in new.d 
    new.d$party[which(new.d$party==a)]<-b #legislators in a, but not in b, are considered in b
    return(new.d)
}
comment(party.relabel)<-  c("d is dataframe","a is old label","b is new label")
