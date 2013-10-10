<?php
/**
 * J!Dump
 * @version      $Id$
 * @package      jdump
 * @copyright    Copyright (C) 2007 Mathias Verraes. All rights reserved.
 * @license      GNU/GPL
 * @link         https://github.com/mathiasverraes/jdump
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.event.helper' );

if(!function_exists("pecho"))
	{
	function pecho($thingy)
		{
		echo "<pre>\n";
		print_r($thingy);
		echo "</pre>\n";
		}
	}