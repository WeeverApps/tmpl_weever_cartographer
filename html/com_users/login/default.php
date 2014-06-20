<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter <rob@weeverapps.com>
*	Version: 	1.9
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


	$output		= new stdClass();
	$callback	= JRequest::getVar('callback', 'callback');
	$user		= JFactory::getUser();
	
	header('Content-type: 	application/json');		
	header('Cache-Control: 	no-cache, must-revalidate');
	
	if( JRequest::getVar('ajaxGetUserInfo') == 1 )
	{
	
		//$output->data->username		= $user->username;
		$output->data->user_id			= $user->id;
		$output->data->display_name		= $user->name;
		//$output->data->name			= $user->name;
		$output->data->user_email		= $user->email;
		$output->data->user_login		= $user->username;
		$output->data->user_registered	= $user->registerDate;
		$output->roles					= array();
		
		
		foreach ($user->groups as $groupId => $value){
		
		    $db = JFactory::getDbo();
		    $db->setQuery(
		        'SELECT `title`' .
		        ' FROM `#__usergroups`' .
		        ' WHERE `id` = '. (int) $groupId
		    );
		    $groupName = $db->loadResult();
		    
		    if ( strtolower($groupName) == 'non-member' || strtolower($groupName) == 'member' ) {
		    	$output->roles[] = strtolower($groupName);
		    }
		    
		}
		
		$json = json_encode($output);
		
		print_r($json);
		jexit();
	
	}
	
	if( $user->get('guest') == 1 )
	{
	
		$output->error			= true;
		$output->error_code		= 403;
		
		$json 				= new stdClass();
		$json->results[]	= $output;
		
		$json			= json_encode($json);
		
		if($callback)
			$json = $callback . "(". $json .")";
		
		print_r($json);
		jexit();
	
	}

	$output->username	= $user->username;
	$output->name		= $user->name;
	$output->email		= $user->email;
	$output->groups		= $user->groups;
	
	$json 				= new stdClass();
	$json->results[]	= $output;
	
	$json			= json_encode($json);
	
	if($callback)
		$json = $callback . "(". $json .")";
	
	print_r($json);
	jexit();