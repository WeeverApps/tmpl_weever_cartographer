<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter (rob@weeverapps.com)
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
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class wxTags {

	public static function parse($str)
	{
	
		$res = str_replace("[wxcolor::red]","<span style='color:red;'>", $str);
		$res = str_replace("[wxcolour::red]","<span style='color:red;'>", $res);
		
		$res = str_replace("[/wxcolor]","</span>", $res);
		$res = str_replace("[/wxcolour]","</span>", $res);
		
		return $res;
	
	}

}