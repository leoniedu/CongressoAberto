#This function cleans party names: does the necessary changes to account for label changes over time
#Handles both matrices and dataframes, with one column, and names.

fix.parties <- function(x,tt){
        if(class(x)=="matrix"){
        rownames(x)[which(rownames(x)=="PCB")]<-"PPS"
        rownames(x)[which(is.element(rownames(x),c("PDS","PPR","PPB","PPB")))]<-"PPB"
        if(tt=="2005"){rownames(x)[which(rownames(x)=="PP")]<-"PPB"}
        return(rownames(x))
        }else{       
        names(x)[which(names(x)=="PCB")]<-"PPS"
        names(x)[which(is.element(names(x),c("PDS","PPR","PPB","PPB")))]<-"PPB"
        if(tt=="2005"){names(x)[which(names(x)=="PP")]<-"PPB"}
        return(names(x))
        }}  
