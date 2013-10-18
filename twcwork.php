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

// #############################################################################
// # TWC Dump
// #############################################################################

// A wrapper for J!Dump that falls back to email.

function twcdump($thing,$label = "",$emailOnly = false)
	{
	// # Get the Joomla User and Bail if not one.
	// #########################################################################

	$user =& JFactory::getUser();
	if(!$user->id) { return; }
	$ugroups = JAccess::getGroupsByUser($user->id);

	// # Get Plugin Parameters
	// #########################################################################

	$plugin = JPluginHelper::getPlugin('system', 'twcwork');
	$pluginParams = new JRegistry();
	$pluginParams->loadString($plugin->params);

	$email_addr = $pluginParams->get('email_to', 'matt@twcsec.com');
	$email_grp  = $pluginParams->get('email_group', '8');

	// # Confirm Overlapping Privilege Groups
	// #########################################################################

	if(!is_array($email_grp)) { $email_grp = array($email_grp); }
	$grpOverlap = array_intersect($ugroups,$email_grp);
	if(!count($grpOverlap)) { return; }

	// # Actually do the dump.
	// #########################################################################

	if(function_exists("dump") && !$emailOnly)
		{ dump($thing,$label); }
	else
		{ $emailOnly = true; }

	if($emailOnly) { twcmail($email_addr,$label,serialize($thing)); }
	}

// #############################################################################
// # TWC Mail
// #############################################################################

// I'm SURE there are better ways to do this... but this is fairly lightweight

function twcmail($to, $subject, $message, $orig_headers = "", $showReturn = false)
	{
	if(!$to || !$subject) { return; }
	if(!$orig_headers) { $orig_headers = "From: ".cfg("sitemail",false); }

	# Verify first that there is a to and that it is ASCII 32 through ASCII 126
	#############################################################################

	$NewTo = "";
	if(strlen(trim($to)) < 5) { if($showReturn) { echo "Send Mail: No Old To"; } return false; }

	for($i = 0; $i <= strlen($to); $i++)
		{
		$ord = ord($to[$i]);
		if(($ord >= 32) && ($ord <= 126)) { $NewTo .= $to[$i]; }
		}
	if(strlen(trim($NewTo)) < 5) { if($showReturn) { echo "Send Mail: No New To"; } return false; }

	# Clean up the Additional Headers - Same as above but allows New Lines
	#############################################################################

	if(strlen($orig_headers) > 1)
		{
		$headers = str_replace(array("\r\n","\r"),"\n",trim($orig_headers));
		for($i = 0; $i <= strlen($headers); $i++)
			{
			$ord = ord($headers[$i]);
			if(($ord == 10) || (($ord >= 32) && ($ord <= 126))) { $additional_headers .= $headers[$i]; }
			}
		$additional_headers = trim($additional_headers);
		unset($headers,$ord);
		}

	# Next we tear apart the Headers if they exist and rebuild them.
	#############################################################################

	if(strlen($additional_headers) > 1)
		{
		$headers = explode("\n",$additional_headers);
		for($i = 0; $i < count($headers); $i++)
			{
			$thisHeader = explode(":",$headers[$i]);
			if(count($thisHeader) == 2) { $AttHeaders[strtoupper(trim($thisHeader[0]))] = trim($thisHeader[1]); }
			}
		if(count($AttHeaders) < 1) { $NewHeaders = ""; } else
			{
			$h = "From";			if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; $From = $AttHeaders[$h]; } else { $From = ""; }
			$h = "Content-Type";	if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; }
			$h = "Date";			if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { $NewHeaders .= $h.": ".date("r")."\n"; }
			$h = "MIME-Version";	if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; }
			$h = "Reply-to";		if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { if(strlen($From)) { $NewHeaders .= $h.": ".$From."\n"; } }
			$h = "Return-Path";		if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { if(strlen($From)) { $NewHeaders .= $h.": ".$From."\n"; } }
			$h = "Sender";			if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { if(strlen($From)) { $NewHeaders .= $h.": ".$From."\n"; } }
			$h = "X-Sender";		if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { if(strlen($From)) { $NewHeaders .= $h.": ".$From."\n"; } }
			$h = "X-Mailer";		if(strlen($AttHeaders[strtoupper($h)]) > 1) { $NewHeaders .= $h.": ".$AttHeaders[strtoupper($h)]."\n"; } else { $NewHeaders .= $h.": PHP mnC 1.1\n"; }
			}
		}

	# Now we can send the message.
	#############################################################################

	if(strlen($NewHeaders) > 0)
		{ return mail($NewTo, $subject, $message, $NewHeaders); }
	else
		{ return mail($NewTo, $subject, $message); }

	}
