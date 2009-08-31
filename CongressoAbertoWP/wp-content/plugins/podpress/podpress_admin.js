var podPressHttp_LoadFileInfo = getHTTPObject();
var podPressHttp_DetectLength = getHTTPObject();
var podPressHttp_DetectDuration = getHTTPObject();
var podPressHttp_TestStats = getHTTPObject();

var podPressLoadedMP3Info = '';
var podPressVarNum = '';
var podPressVarNum2 = '';

var podPressMediaFiles = new Array();
var podPressMediaContentNum = 0;

var podPressMediaFileForRSS = 'NOTSET';

var podPress_colorLocked = false;
var podPress_colorInput = '';

function podPressShowDirectoryPreview(directoryName) {
	if(directoryName != 'PodcastAlley') { 
		document.getElementById('PodcastAlleyPreviewDisplay').style.display='none'; 
	}
	if(directoryName != 'Yahoo') { 
		document.getElementById('YahooPreviewDisplay').style.display='none'; 
	}
	if(directoryName != 'iTunes') { 
		document.getElementById('iTunesPreviewDisplay').style.display='none'; 
	}
	if(directoryName != 'PodcastReady') { 
		document.getElementById('PodcastReadyPreviewDisplay').style.display='none'; 
	}
	if(directoryName != 'Blubrry') { 
		document.getElementById('BlubrryPreviewDisplay').style.display='none'; 
	}


	if(directoryName == 'PodcastAlley') { 
		document.getElementById('PodcastAlleyPreviewDisplay').style.display='block'; 
	}
	if(directoryName == 'Yahoo') { 
		document.getElementById('YahooPreviewDisplay').style.display='block'; 
	}
	if(directoryName == 'iTunes') { 
		document.getElementById('iTunesPreviewDisplay').style.display='block'; 
	}
	if(directoryName == 'PodcastReady') { 
		document.getElementById('PodcastReadyPreviewDisplay').style.display='block'; 
	}
	if(directoryName == 'Blubrry') { 
		document.getElementById('BlubrryPreviewDisplay').style.display='block'; 
	}
}

function podPressDetectLength(VarNum, params) {
	podPressVarNum = VarNum;
	document.getElementById('podPressMedia_'+podPressVarNum+'_size').value = 'DETECTING...';
	podPressHttp_DetectLength.open("GET", podPressBackendURL+'podpress_backend.php?action=getfilesize&filename='+escape(params), true);
	podPressHttp_DetectLength.onreadystatechange = podPressDetectLengthHandler;
	podPressHttp_DetectLength.send(null);
}

function podPressDetectLengthHandler() {
 	if (podPressHttp_DetectLength.readyState == 4) {
		var tmp = podPressHttp_DetectLength.responseText;
		if(podPressHttp_DetectLength.responseText == '' || podPressHttp_DetectLength.responseText == 'M' || podPressHttp_DetectLength.responseText == '4096' ) {
			if(podPressMediaFiles[podPressVarNum]['size'] == '') {
				podPressMediaFiles[podPressVarNum]['size'] = 'UNKNOWN';
			}
		} else {
			podPressMediaFiles[podPressVarNum]['size'] = podPressHttp_DetectLength.responseText;
		}
		document.getElementById('podPressMedia_'+podPressVarNum+'_size').value = podPressMediaFiles[podPressVarNum]['size'];
	}
}

function podPressDetectDuration(VarNum, params) {
	podPressVarNum = VarNum;
	document.getElementById('podPressMedia_'+podPressVarNum+'_duration').value = 'DETECTING...';
	podPressHttp_DetectDuration.open("GET", podPressBackendURL+'podpress_backend.php?action=getduration&filename='+escape(params), true);
	podPressHttp_DetectDuration.onreadystatechange = podPressDetectDurationHandler;
	podPressHttp_DetectDuration.send(null);
}

function podPressDetectDurationHandler() {
 	if (podPressHttp_DetectDuration.readyState == 4) {
		var tmp = podPressHttp_DetectDuration.responseText;
		if(podPressHttp_DetectDuration.responseText == '' || podPressHttp_DetectDuration.responseText == 'M' || podPressHttp_DetectDuration.responseText == '4096' ) {
			if(podPressMediaFiles[podPressVarNum]['duration'] == '') {
				podPressMediaFiles[podPressVarNum]['duration'] = 'UNKNOWN';
			}
		} else {
			podPressMediaFiles[podPressVarNum]['duration'] = podPressHttp_DetectDuration.responseText;
		}
		document.getElementById('podPressMedia_'+podPressVarNum+'_duration').value = podPressMediaFiles[podPressVarNum]['duration'];
	}
}

function podPressTestStats(params) {
	podPressHttp_TestStats.open("GET", params, true);
	podPressHttp_TestStats.onreadystatechange = podPressTestStatsHandler;
	podPressHttp_TestStats.send(null);
}

function podPressTestStatsHandler() {
 	if (podPressHttp_TestStats.readyState == 4) {
		var x = podPressHttp_TestStats.responseText;
		x = x.replace(/^\s*|\s*$/g,"");
		if(x == 'Worked') {
			document.getElementById('statTestResult').value = 'Test: Successful';
		} else {
			document.getElementById('statTestResult').value = 'Test: Failed';
		}
	}
}

function podPressLoadFileInfo(VarNum) {
	podPressVarNum2 = VarNum;
	var mp3filename = document.getElementById('podPressMedia_'+VarNum+'_URI').value;
	if(mp3filename != podPressLoadedMP3Info) {
		podPressLoadedMP3Info = mp3filename;
		document.getElementById('podPressMedia_'+VarNum+'_mp3_details').innerHTML='Loading ID tag information.<br/> If file is remote this could take several seconds.';
		podPressHttp_LoadFileInfo.open("GET", podPressBackendURL+'podpress_backend.php?action=showID3Contents&filename='+escape(mp3filename)+'&keynum='+VarNum, true);
 	 	podPressHttp_LoadFileInfo.onreadystatechange = podPressLoadFileInfoHandler;
		podPressHttp_LoadFileInfo.send(null);	
	}
}

/* MP3 info functions */
function podPressLoadFileInfoHandler() {
	if (podPressHttp_LoadFileInfo.readyState == 4) {
		document.getElementById('podPressMedia_'+podPressVarNum2+'_mp3_details').innerHTML=podPressHttp_LoadFileInfo.responseText;
	} 	
}

function podPressShowHideDiv(divname) {
	if(document.getElementById(divname).style.display == 'none') {
		document.getElementById(divname).style.display='block';
		if(document.getElementById(divname+'_text') != undefined) {
			document.getElementById(divname+'_text').innerHTML = '(Hide)';
		}
		if(document.getElementById(divname+'_button') != undefined) {
			document.getElementById(divname+'_button').value = 'Hide';
		}
	} else {
		document.getElementById(divname).style.display='none';
		if(document.getElementById(divname+'_text') != undefined) {
			document.getElementById(divname+'_text').innerHTML = '(Show)';
		}
		if(document.getElementById(divname+'_button') != undefined) {
			document.getElementById(divname+'_button').value = 'Show';
		}
	}
}

function podPressShowHideRow(rowname) {
	if(document.getElementById(rowname).style.display == 'none') {
		document.getElementById(rowname).style.display='';
	} else {
		document.getElementById(rowname).style.display='none';
	}
}

function podPressShowHideMP3Details(VarNum) {
	if(document.getElementById('podPressMedia_'+VarNum+'_mp3_details').style.display=='block') {
		document.getElementById('podPressMedia_'+VarNum+'_mp3_detailsbutton_').value='Show';
		document.getElementById('podPressMedia_'+VarNum+'_mp3_details').style.display='none';
		return;
	} else {
		document.getElementById('podPressMedia_'+VarNum+'_mp3_detailsbutton_').value='Hide';
		document.getElementById('podPressMedia_'+VarNum+'_mp3_details').style.display='block';
		podPressLoadFileInfo(VarNum);
	}
}

function podPressID3ToPost(VarNum) {
	document.getElementById('title').value = document.getElementById('podPressMedia_'+VarNum+'_tagTitle').innerHTML;
	document.getElementById('content').value = document.getElementById('podPressMedia_'+VarNum+'_tagDescription').innerHTML;
}

function podPressUpdateDimensions(VarNum, val) {
	var valArray = val.split(':');
	podPressMediaFiles[VarNum]['dimensionW'] = valArray[0];
	document.getElementById('podPressMedia_'+VarNum+'_dimensionW').value = valArray[0];
	podPressMediaFiles[VarNum]['dimensionH'] = valArray[1];
	document.getElementById('podPressMedia_'+VarNum+'_dimensionH').value = valArray[1];
	podPressShowPreviewImage(VarNum);
}

function podPress_set_blognamePreview(val) {
	document.getElementById('iTunesBlognamePreview').innerHTML = val;
	document.getElementById('PCABlognamePreview').innerHTML = val;
	document.getElementById('YahooBlognamePreview').innerHTML = val;
	document.getElementById('PodcastReadyBlognamePreview').innerHTML = val;
	document.getElementById('BlubrryBlognamePreview').innerHTML = val;
}

function podPress_set_descriptionPreview(val) {
	document.getElementById('itunesDescriptionPreview').innerHTML = val;
	document.getElementById('PCADescriptionPreview').innerHTML = val;
	document.getElementById('YahooDescriptionPreview').innerHTML = val;
	document.getElementById('PodcastReadyDescriptionPreview').innerHTML = val;
	document.getElementById('BlubrryDescriptionPreview').innerHTML = val;
}

function podPress_set_FeedURLPreview(val) {
	document.getElementById('YahooFeedURLPreview').innerHTML = val;
	document.getElementById('PodcastReadyFeedURLPreview').href = 'http://www.podcastready.com/channel.php?action=subscribe&feedUrl='+val;
}

function podPress_set_AuthorPreview(val) {
	document.getElementById('itunesAuthorPreview').innerHTML = val;
	document.getElementById('YahooAuthorPreview').innerHTML = val;
	document.getElementById('BlubrryAuthorPreview').innerHTML = val;
}

function podPress_set_PreviewImage(val) {
	document.getElementById('iTunesPreviewImage').src = val;
	document.getElementById('YahooPreviewImage').src = val;
	document.getElementById('PodcastReadyPreviewImage').src = val;
	document.getElementById('BlubrryPreviewImage').src = val;
}

function podPress_set_RssLanguagePreview(val) {
	document.getElementById('iTunesRssLanguagePreview').innerHTML = val;
	document.getElementById('YahooRssLanguagePreview').innerHTML = val;
}

function podPress_set_CategoryPreview(val) {
	document.getElementById('itunesCategoryPreview').innerHTML = val;
	document.getElementById('PCACategoryPreview').innerHTML = val;
	document.getElementById('PodcastReadyCategoryPreview').innerHTML = val;
}

function podPress_updateFeedSettings() {
	podPress_set_blognamePreview(document.getElementById('blogname').value);

	if(document.getElementById('iTunesSummary').value == '') {
		if(document.getElementById('blogdescription').value == '') {
			podPress_set_descriptionPreview('NO DESCRIPTION IS SET. APPLE WILL REJECT THIS FEED.');
		} else {
			podPress_set_descriptionPreview(document.getElementById('blogdescription').value);
		}
	} else {
		podPress_set_descriptionPreview(document.getElementById('iTunesSummary').value);
	}

	podPress_set_FeedURLPreview(document.getElementById('podcastFeedURL').value);

	podPress_set_AuthorPreview(document.getElementById('iTunesAuthor').value);

	var iTunesImageURL = 'http://www.mightyseek.com/images/powered_by_podpress_large.png';
	if(document.getElementById('iTunesImage').value == '') {
		if (document.getElementById('rss_image').value != '') {
			iTunesImageURL = document.getElementById('rss_image').value;
		}
	} else {
		iTunesImageURL = document.getElementById('iTunesImage').value;
	}

	podPress_set_PreviewImage(iTunesImageURL);

	var rsslanguageIDX = document.getElementById('rss_language').selectedIndex;
	var rssLanguageParts = document.getElementById('rss_language').options[rsslanguageIDX].text.split("[");
	podPress_set_RssLanguagePreview(rssLanguageParts[0]);

	var catArray = document.getElementById('iTunesCategory_0').value.split(':');
	if (catArray[0] != undefined) {
		var cat = catArray[0];
	} else {
		/* var cat = catArray[1]; */
		var cat = '';
	}

	podPress_set_CategoryPreview(cat);

	if(document.getElementById('rss_image').value == '') {
		document.getElementById('rss_imagePreview').src = 'http://www.mightyseek.com/images/powered_by_podpress.png';
	} else {
		document.getElementById('rss_imagePreview').src = document.getElementById('rss_image').value;
	}

	if(document.getElementById('iTunesImage').value == '') {
		document.getElementById('iTunesImagePreview').src = 'http://www.mightyseek.com/images/powered_by_podpress_large.png';
	} else {
		document.getElementById('iTunesImagePreview').src = document.getElementById('iTunesImage').value;
	}
}

function podPress_updateCategoryCasting() {
	if(document.getElementById('categoryCasting').checked == false) { 
		document.getElementById('iTunesSpecificSettings').style.display='none'; 
	} else {
		document.getElementById('iTunesSpecificSettings').style.display='block';
	}

	if(document.getElementById('blognameChoice').value == 'Global') { 
		podPress_set_blognamePreview(document.getElementById('global_blogname').value);
	} else if(document.getElementById('blognameChoice').value == 'Append') { 
		podPress_set_blognamePreview(document.getElementById('global_blogname').value+' : '+document.forms['editcat'].cat_name.value);
	} else {
		podPress_set_blognamePreview(document.forms['editcat'].cat_name.value);
	}

	if(document.getElementById('blogdescriptionChoice').value == 'Global') { 
		podPress_set_descriptionPreview(document.getElementById('global_blogdescription').value);
	} else {
		podPress_set_descriptionPreview(document.forms['editcat'].category_description.value);
	}

	if(document.getElementById('iTunesSummaryChoice').value != 'Global') {
		podPress_set_descriptionPreview(document.getElementById('iTunesSummary').value);
		document.getElementById('iTunesSummaryWrapper').style.display=''; 
	} else {
		document.getElementById('iTunesSummaryWrapper').style.display='none'; 
		if(document.getElementById('global_iTunesSummary').value == '') {
			podPress_set_descriptionPreview(document.getElementById('global_iTunesSummary').value);
		}
	}

	if(document.getElementById('iTunesSubtitleChoice').value == 'Global') { 
		document.getElementById('iTunesSubtitleWrapper').style.display='none'; 
	} else {
		document.getElementById('iTunesSubtitleWrapper').style.display='';
	}

	if(document.getElementById('iTunesKeywordsChoice').value == 'Global') { 
		document.getElementById('iTunesKeywordsWrapper').style.display='none'; 
	} else {
		document.getElementById('iTunesKeywordsWrapper').style.display='';
	}

	if(document.getElementById('iTunesAuthorChoice').value == 'Custom') { 
		document.getElementById('iTunesAuthorWrapper').style.display='';
		podPress_set_AuthorPreview(document.getElementById('iTunesAuthor').value);
	} else {
		document.getElementById('iTunesAuthorWrapper').style.display='none'; 
		podPress_set_AuthorPreview(document.getElementById('global_iTunesAuthor').value);
	}

	if(document.getElementById('iTunesAuthorEmailChoice').value == 'Custom') { 
		document.getElementById('iTunesAuthorEmailWrapper').style.display='';
	} else {
		document.getElementById('iTunesAuthorEmailWrapper').style.display='none'; 
	}

	var iTunesImageURL = 'http://www.mightyseek.com/images/powered_by_podpress_large.png';
	if(document.getElementById('global_iTunesImage').value == '') {
		if (document.getElementById('global_rss_image').value != '') {
			iTunesImageURL = document.getElementById('global_rss_image').value;
		}
	} else {
		iTunesImageURL = document.getElementById('global_iTunesImage').value;
	}

	if(document.getElementById('iTunesImageChoice').value != 'Global') {
		if(document.getElementById('iTunesImage').value == '') {
			if (document.getElementById('rss_image').value != '') {
				iTunesImageURL = document.getElementById('rss_image').value;
			}
		} else {
			iTunesImageURL = document.getElementById('iTunesImage').value;
		}		
	}
	document.getElementById('iTunesPreviewImage').src = iTunesImageURL;
	document.getElementById('YahooPreviewImage').src = iTunesImageURL;

	if(document.getElementById('iTunesImageChoice').value == 'Custom') {
		document.getElementById('iTunesImageWrapper').style.display='';
		document.getElementById('itunes_image_display').src = document.getElementById('iTunesImage').value;
	} else {
		document.getElementById('iTunesImageWrapper').style.display='none';
		document.getElementById('itunes_image_display').src = document.getElementById('global_iTunesImage').value;
	}

	if(document.getElementById('rss_imageChoice').value == 'Custom') {
		document.getElementById('rss_imageWrapper').style.display='';
		document.getElementById('rss_image_Display').src = document.getElementById('rss_image').value;
	} else {
		document.getElementById('rss_imageWrapper').style.display='none';
		document.getElementById('rss_image_Display').src = document.getElementById('global_rss_image').value;
	}

	if(document.getElementById('rss_copyrightChoice').value == 'Custom') { 
		document.getElementById('rss_copyrightWrapper').style.display='';
	} else {
		document.getElementById('rss_copyrightWrapper').style.display='none'; 
	}

	if(document.getElementById('rss_language').value == '##Global##') {
		podPress_set_RssLanguagePreview(document.getElementById('global_rss_language').value);
	} else {
		var rsslanguageIDX = document.getElementById('rss_language').selectedIndex;
		var rssLanguageParts = document.getElementById('rss_language').options[rsslanguageIDX].text.split("[");
		podPress_set_RssLanguagePreview(rssLanguageParts[0]);
	}

	var catArray = document.getElementById('iTunesCategory_0').value.split(':');
	if (catArray[0] != undefined) {
		var cat = catArray[0];
	} else {
		/* var cat = catArray[1]; */
		var cat = '';
	}
	if(cat == '##Global##') {
		podPress_set_CategoryPreview(document.getElementById('global_iTunesCategory').value);
	} else {
		podPress_set_CategoryPreview(cat);
	}

}

function podPressDisplayMediaFiles() {
	for (var i=0; i < podPressMaxMediaFiles; i++) {
		if (podPressMediaFiles[i] != undefined && podPressMediaFiles[i]['showme'] == true) {
			document.getElementById('podPressMediaFileContainer_'+i).style.display = 'block';
		} else {
			document.getElementById('podPressMediaFileContainer_'+i).style.display = 'none';
		}
		podPressSetMediaFileValues(i);
	}
}

function podPressAddMediaFile(showme, txtURI, txtURI_torrent, txttitle, txttype, txtsize, duration, dimensionW, dimensionH, previewImage, rss, atom, feedonly, disablePlayer, content_level) {
	if(rss == 'new') {
		if(podPressMediaFiles.length == 0) {
			rss = true;
		} else if(podPressMediaFileForRSS =='NOTSET') {
			rss = true;
		} else {
			rss = false;
		}
	} 
	var newMediaFileData = new Array();
  newMediaFileData['showme'] = showme;
  newMediaFileData['URI'] = txtURI;
  newMediaFileData['URI_torrent'] = txtURI_torrent;
  newMediaFileData['title'] = txttitle;
	newMediaFileData['type'] = txttype;
	newMediaFileData['size'] = txtsize;
	newMediaFileData['duration'] = duration;
	if(dimensionW == '') {
		dimensionW = '320';
	}
	if(dimensionH == '') {
		dimensionH = '240';
	}
	newMediaFileData['dimensionW'] = dimensionW;
	newMediaFileData['dimensionH'] = dimensionH;
	if(previewImage == '') {
		previewImage = podPressDefaultPreviewImage;
	}
	newMediaFileData['previewImage'] = previewImage;
	newMediaFileData['rss'] = rss;
	newMediaFileData['atom'] = atom;
	newMediaFileData['feedonly'] = feedonly;
	newMediaFileData['disablePlayer'] = disablePlayer;
	newMediaFileData['content_level'] = content_level;
	podPressMediaFiles[podPressMediaContentNum] = newMediaFileData;
	podPressMediaContentNum++;
}

function podPressRemoveFile(VarNum) {
	var orig_podPressMediaFiles = podPressMediaFiles;
	if(podPressMediaFileForRSS == VarNum) {
		podPressMediaFileForRSS = 'NOTSET';
	}
  podPressMediaFiles = new Array();
	podPressMediaContentNum = 0;
	max = orig_podPressMediaFiles.length;
	for (var i=0; i<=max; i++) {
		if(i != VarNum) {
			podPressMediaFiles[podPressMediaContentNum] = orig_podPressMediaFiles[i];
			podPressMediaContentNum++;
		}
	}
}


function podPressMoveFile(VarNum, Direction) {
	if(Direction == 'up') {
		var newVarNum	= VarNum - 1;
	}

	if(Direction == 'down') {
		var newVarNum	= VarNum + 1;
	}

	if(podPressMediaFiles[newVarNum] != undefined) {
		var holdme = podPressMediaFiles[newVarNum];
		podPressMediaFiles[newVarNum] = podPressMediaFiles[VarNum];
		podPressMediaFiles[VarNum] = holdme;
	}
}

function podPressSetMediaFileValues (VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		data = newMediaDefaults;
	} else {
		data = podPressMediaFiles[VarNum];
	}
	document.getElementById('podPressMedia_'+VarNum+'_URI').value = data['URI'];

	if(document.getElementById('podPressMedia_'+VarNum+'_URI').value != data['URI']) {
		var option = document.createElement('option');
		option.setAttribute('value', data['URI']);
		option.innerHTML = data['URI'];
		option.selected = true;
		document.getElementById('podPressMedia_'+VarNum+'_URI').appendChild(option);
	}

	if(document.getElementById('podPressMedia_'+VarNum+'_URItorrent') != undefined) {
		document.getElementById('podPressMedia_'+VarNum+'_URItorrent').value = data['URI_torrent']; 
	}
	document.getElementById('podPressMedia_'+VarNum+'_title').value = data['title']; 	
	document.getElementById('podPressMedia_'+VarNum+'_type').value = data['type']; 	
	document.getElementById('podPressMedia_'+VarNum+'_size').value = data['size']; 	
	document.getElementById('podPressMedia_'+VarNum+'_duration').value = data['duration']; 	
	document.getElementById('podPressMedia_'+VarNum+'_dimensionW').value = data['dimensionW']; 	
	document.getElementById('podPressMedia_'+VarNum+'_dimensionH').value = data['dimensionH'];
	document.getElementById('podPressMedia_'+VarNum+'_previewImage').value = data['previewImage'];
	document.getElementById('podPressMedia_'+VarNum+'_disablePlayer').checked = data['disablePlayer'];

	podPressMediaSetNonTextInputs(VarNum);
	podPressShowPreviewImage(VarNum);
}

function podPressShowPreviewImage(VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		data = newMediaDefaults;
	} else {
		data = podPressMediaFiles[VarNum];
	}

	var typeOfMedia = data['type'].substring(0,6);
	if(typeOfMedia != 'video_'){
		document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display = 'none';
		document.getElementById('podPressMediaPreviewImageDisplayWrapper_'+VarNum).style.display = 'none';
		document.getElementById('podPress_previewImageIMG_'+VarNum).src = '';
		document.getElementById('podPress_previewImageIMG_'+VarNum).width = '0';
		document.getElementById('podPress_previewImageIMG_'+VarNum).height = '0';
	} else {
		if(data['previewImage'] == '') {
			document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display = '';
			document.getElementById('podPressMediaPreviewImageDisplayWrapper_'+VarNum).style.display = 'none';
			document.getElementById('podPress_previewImageIMG_'+VarNum).src = '';
			document.getElementById('podPress_previewImageIMG_'+VarNum).width = '0';
			document.getElementById('podPress_previewImageIMG_'+VarNum).height = '0';
		} else {
			document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display = '';
			document.getElementById('podPressMediaPreviewImageDisplayWrapper_'+VarNum).style.display = '';
			document.getElementById('podPress_previewImageIMG_'+VarNum).src = data['previewImage'];
			document.getElementById('podPress_previewImageIMG_'+VarNum).width = data['dimensionW'];
			document.getElementById('podPress_previewImageIMG_'+VarNum).height = data['dimensionH'];
		}
	}
}

function podPressMediaSetNonTextInputs(VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		data = newMediaDefaults;
	} else {
		data = podPressMediaFiles[VarNum];
	}
	document.getElementById('podPressMedia_'+VarNum+'_type').value = data['type']; 	
	podPressAdjustMediaFieldsBasedOnType(VarNum);

	podPressSetSingleRSS(VarNum);
	document.getElementById('podPressMedia_'+VarNum+'_atom').checked = data['atom']; 
	document.getElementById('podPressMedia_'+VarNum+'_feedonly').checked = data['feedonly']; 
	if(document.getElementById('podPressMedia_'+VarNum+'_content_level') != undefined) {
		document.getElementById('podPressMedia_'+VarNum+'_content_level').value = data['content_level'];
	}
}

function podPressSetSingleRSS(VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		data = newMediaDefaults;
	} else {
		data = podPressMediaFiles[VarNum];
	}
	if(podPressMediaFileForRSS == VarNum && data['rss'] == false) {
		podPressMediaFileForRSS = 'NOTSET';
		document.getElementById('podPressMedia_'+VarNum+'_rss').checked = data['rss']; 	
	}
	if(podPressMediaFileForRSS == 'NOTSET' && data['rss'] == true) {
		podPressMediaFileForRSS = VarNum;
	}
	if(podPressMediaFileForRSS != VarNum && podPressMediaFileForRSS != 'NOTSET' && data['rss'] == true) {
		if(podPressMediaFiles[podPressMediaFileForRSS] != undefined) {
			podPressMediaFiles[podPressMediaFileForRSS]['rss'] = false;
		}
		if(document.getElementById('podPressMedia_'+podPressMediaFileForRSS+'_rss') != undefined) {
			document.getElementById('podPressMedia_'+podPressMediaFileForRSS+'_rss').checked = podPressMediaFiles[podPressMediaFileForRSS]['rss']; 	
		}
		podPressMediaFileForRSS = VarNum;
	}
	if(podPressMediaFileForRSS == VarNum && data['rss'] == true) {
		document.getElementById('podPressMedia_'+VarNum+'_rss').checked = true; 
	}
}

function podPressAdjustMediaFieldsBasedOnType(VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		data = newMediaDefaults;
	} else {
		data = podPressMediaFiles[VarNum];
	}
	var typeOfMedia = data['type'].substring(0,6);
	if(typeOfMedia == 'audio_' || typeOfMedia == 'video_'){
		document.getElementById('podPressMediaDurationWrapper_'+VarNum).style.display=''; 	
 } else {
		document.getElementById('podPressMediaDurationWrapper_'+VarNum).style.display='none'; 	
 }

 if(typeOfMedia == 'video_'){
		document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display='';
		document.getElementById('podPressMediaDimensionWrapper_'+VarNum).style.display='';
		podPressShowPreviewImage(VarNum);
 } else if(typeOfMedia == 'embed_') {
		document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display='';
		document.getElementById('podPressMediaPreviewImageDisplayWrapper_'+VarNum).style.display='';
		document.getElementById('podPressMediaDimensionWrapper_'+VarNum).style.display='none'; 	
		document.getElementById('podPressMediaDurationWrapper_'+VarNum).style.display='none'; 	
		document.getElementById('podPressMediaSizeWrapper_'+VarNum).style.display='none'; 	
 		podPressShowPreviewImage(VarNum);
	} else {
		document.getElementById('podPressMediaPreviewImageWrapper_'+VarNum).style.display='none';
		document.getElementById('podPressMediaPreviewImageDisplayWrapper_'+VarNum).style.display='none';
		document.getElementById('podPressMediaDimensionWrapper_'+VarNum).style.display='none'; 	
 }
}

function podPressDetectType(VarNum) {
	var data = new Array();
	if(podPressMediaFiles[VarNum] == undefined) {
		return;
	}
	var lenOfMedia = podPressMediaFiles[VarNum]['URI'].length;

	if(podPressMediaFiles[VarNum]['URI'].substring(0, 24) == 'http://youtube.com/watch') {
		var extOfMedia = 'youtube';
	} else if(podPressMediaFiles[VarNum]['URI'].substring(0, 28) == 'http://www.youtube.com/watch') {
		var extOfMedia = 'youtube';
	} else if(podPressMediaFiles[VarNum]['URI'].substring(lenOfMedia-8, lenOfMedia) == '.torrent') {
		var extOfMedia = 'torrent';
	} else if(podPressMediaFiles[VarNum]['URI'].substring(lenOfMedia-3, lenOfMedia-2) == '.') {
		var extOfMedia = podPressMediaFiles[VarNum]['URI'].substring(lenOfMedia-2, lenOfMedia);
	} else if(podPressMediaFiles[VarNum]['URI'].substring(lenOfMedia-4, lenOfMedia-3) == '.') {
		var extOfMedia = podPressMediaFiles[VarNum]['URI'].substring(lenOfMedia-3, lenOfMedia);
	} else {
		var extOfMedia = '';
	}
	extOfMedia = extOfMedia.toLowerCase();
	var result = '';
	switch(extOfMedia) {
		case 'mp3': result = 'audio_mp3'; break
		case 'ogg': result = 'audio_ogg'; break
		case 'm4a': result = 'audio_m4a'; break
		case 'aa':  result = 'audio_aa'; break
		case 'm3u': result = 'audio_m3u'; break
		case 'mp4': result = 'video_mp4'; break
		case 'm4v': result = 'video_m4v'; break
		case 'mov': result = 'video_mov'; break
		case 'qt':  result = 'video_qt'; break
		case 'avi': result = 'video_avi'; break
		case 'mpg': result = 'video_mpg'; break
		case 'peg': result = 'video_mpg'; break
		case 'asf': result = 'video_asf'; break
		case 'wmv': result = 'video_wmv'; break
		case 'flv': result = 'video_flv'; break
		case 'swf': result = 'video_swf'; break
		case 'pdf': result = 'ebook_pdf'; break
		case 'torrent': result = 'misc_torrent'; break
		case 'youtube': result = 'embed_youtube'; break
		default: result = 'misc_other';
	}
	document.getElementById('podPressMedia_'+VarNum+'_type').value=result; 	
	podPressMediaFiles[VarNum]['type'] = result;
	podPressAdjustMediaFieldsBasedOnType(VarNum);
}

function podPress_colorSet(hval) {
	if (!podPress_colorLocked) {
		if(document.getElementById(podPress_colorInput) != undefined) {
			document.getElementById(podPress_colorInput).value = hval;
			document.getElementById(podPress_colorInput).style.background = hval;
		}
	}
}

function podPress_switchColorInputs(idname) {
	if(idname != podPress_colorInput) {
		podPress_colorInput = idname;
		podPress_colorLocked = false;
	}
}
	
function podPress_colorLock() {
	if (podPress_colorLocked)
	{
		podPress_colorLocked = false;
	} else {
		podPress_colorLocked = true;
	}
}

function podPress_colorReset() {
	document.getElementById('playerSettings_bg_').value = '#F8F8F8';
	document.getElementById('playerSettings_bg_').style.background = '#F8F8F8';
	document.getElementById('playerSettings_leftbg_').value = '#F8F8F8';
	document.getElementById('playerSettings_leftbg_').style.background = '#F8F8F8';
	document.getElementById('playerSettings_text_').value = '#666666';
	document.getElementById('playerSettings_text_').style.background = '#666666';
	document.getElementById('playerSettings_leftbg_').value = '#EEEEEE';
	document.getElementById('playerSettings_leftbg_').style.background = '#EEEEEE';
	document.getElementById('playerSettings_lefticon_').value = '#666666';
	document.getElementById('playerSettings_lefticon_').style.background = '#666666';
	document.getElementById('playerSettings_rightbg_').value = '#CCCCCC';
	document.getElementById('playerSettings_rightbg_').style.background = '#CCCCCC';
	document.getElementById('playerSettings_rightbghover_').value = '#999999';
	document.getElementById('playerSettings_rightbghover_').style.background = '#999999';
	document.getElementById('playerSettings_righticon_').value = '#666666';
	document.getElementById('playerSettings_righticon_').style.background = '#666666';
	document.getElementById('playerSettings_righticonhover_').value = '#FFFFFF';
	document.getElementById('playerSettings_righticonhover_').style.background = '#FFFFFF';
	document.getElementById('playerSettings_slider_').value = '#666666';
	document.getElementById('playerSettings_slider_').style.background = '#666666';
	document.getElementById('playerSettings_track_').value = '#FFFFFF';
	document.getElementById('playerSettings_track_').style.background = '#FFFFFF';
	document.getElementById('playerSettings_loader_').value = '#9FFFB8';
	document.getElementById('playerSettings_loader_').style.background = '#9FFFB8';
	document.getElementById('playerSettings_border_').value = '#666666';
	document.getElementById('playerSettings_border_').style.background = '#666666'; 
}

function podPress_customSelectVal(select_elm, prompt_text){
	var val = prompt(prompt_text, '');
	var option = document.createElement('option');
	option.setAttribute('value', val);
	option.innerHTML = val;
	option.selected = true;
	select_elm.appendChild(option);
}
