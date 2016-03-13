<?php
/*
$themeName 	It's the name of the current theme
$themeData	It's the theme object containing:
			$themeData->$type 		= 'php'
			$themeData->$key     	= functions.php or the name of the current file, since you can add multiple php files
			$themeData->$file		= the full path of the current file
			$themeData->$themeUrl	= the url of the current theme
			$themeData->$deps		= not used yet
 */

/** @var $themeName string */
/** @var $themeData theme_data */

wp_enqueue_style("misamee-themed-form-$themeName", "{$themeData->themeUrl}css/misamee.themed.form.$themeName.css");
wp_enqueue_script('tooltipsy', "{$themeData->themeUrl}js/tooltipsy.min.js", array('jquery'), false, true);
wp_enqueue_script("misamee-themed-form-$themeName", Misamee_GF_Themes::getPluginUrl() . "js/misamee.themed.form.js", array('jquery'));