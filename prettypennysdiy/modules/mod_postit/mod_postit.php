<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
// Include the syndicate functions only once
require_once( dirname(__FILE__).'/helper.php' );
 
$hello = modPostitHelper::getPostit( $params );
require( JModuleHelper::getLayoutPath( 'mod_postit' ) );
?>