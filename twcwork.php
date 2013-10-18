<?php
/**
 * TWCWork
 * @version      $Id$
 * @package      twcwork
 * @copyright    Copyright (C) 2013 Thimbleweed Consulting. All rights reserved.
 * @license      GNU/GPL
 * @link         https://github.com/TWCSec/twcwork
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