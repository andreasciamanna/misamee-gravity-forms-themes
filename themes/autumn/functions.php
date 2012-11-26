<?php
	//$theme['dir']
	//$theme['url']
	//$theme['name']

	wp_enqueue_style("misamee-themed-form-$theme[name]", "$theme[url]css/misamee.themed.form.$theme[name].css");
	wp_enqueue_script('tooltipsy', "$theme[url]js/tooltipsy.min.js", array('jquery'));
	//wp_enqueue_script("misamee-themed-form-$theme[name]-js", "$theme[url]js/misamee.themed.form.$theme[name].js", array('jquery'));
