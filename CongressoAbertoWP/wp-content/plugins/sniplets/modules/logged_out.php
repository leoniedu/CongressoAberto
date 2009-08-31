<?php

if (!defined ('SNIPLETS'))
	die ('No direct access allowed');
	
/* Name: User is not logged in */
if (!is_user_logged_in ())
	echo $text;
?>