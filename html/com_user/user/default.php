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


	$output		= new stdClass();
	$callback	= JRequest::getVar('callback', 'callback');
	
	header('Content-type: 	application/json');		
	header('Cache-Control: 	no-cache, must-revalidate');

	if( !$this->user->get('username') )
	{
	
		$output->error			= true;
		$output->error_code		= 403;
		
		$json 				= new stdClass();
		$json->results[]	= $output;
		
		$json			= json_encode($json);
		
		echo $callback . "(" . $json . ");";
		
		jexit();
	
	}

	$output->username	= $this->user->get('username');
	$output->name		= $this->user->get('name');
	$output->email		= $this->user->get('email');
	
	$json 				= new stdClass();
	$json->results[]	= $output;
	
	$json			= json_encode($json);
	
	echo $callback . "(" . $json . ");";
	
	jexit();