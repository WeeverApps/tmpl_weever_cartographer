<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter <rob@weeverapps.com>
*	Version: 	1.7
*   License: 	GPL v3.0
*
*   This extension is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   This extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details <http://www.gnu.org/licenses/>.
* 
*
*/
 
defined('_JEXEC') or die();

if( JRequest::getVar('wxdebug') )
	ini_set('error_reporting', E_ALL);
	
$user 	= JFactory::getUser();
	
if( JRequest::getVar('wxUserFeed') )
{

	header('Content-type: 	application/json');		
	header('Cache-Control: 	no-cache, must-revalidate');

	$callback	= JRequest::getVar('callback', 'callback');

	$feed 		= new R3SProfileMap;
	
	$feed->username		= null;
	$feed->id			= $user->id;
	$feed->name			= $this->user->name;
	$feed->images[]		= $this->user->avatar;
	$feed->description	= $this->user->profile->description;
	$feed->url			= $this->user->profile->url;
	
	$json 			= new jsonOutput;
	$json->results 	= $feed;
	$json 			= json_encode($json);
	
	echo 	$callback . "(" . $json . ");";
	
	jexit();

}
