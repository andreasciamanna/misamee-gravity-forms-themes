<?php

class misamee_tools {
	static function showMessage( $message, $error = false ) {
		$template = '<div id="message" class="%2$s"><p>%1$s</p></div>';
		$class    = 'updated fade';
		if ( $error ) {
			$class = 'error';
		}
		echo sprintf( $template, $message, $class );
	}
}
