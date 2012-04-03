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
*/
 
defined('_JEXEC') or die();

class R3SProfileMap {

	public 		$name;
	public 		$images			= array();
	public 		$tags			= array();
	public		$geo			= array();
	public 		$url;
	public 		$id;
	public 		$properties;
	public		$description;
	public 		$generator		= "Weever Cartographer R3S Template for Joomla";
	public 		$copyright;
	public 		$rating;
	public 		$r3sVersion		= "0.8.2";
	public 		$relationships;

}

class R3SHtmlContentDetailsMap {

	public 		$html;
	public 		$name;
	public 		$datetime		= array("published"=>"","modified"=>"");
	public 		$image			= array("mobile"=>"","full"=>"");
	public 		$tags			= array();
	public		$geo			= array();
	public		$language;
	public 		$url;
	public 		$uuid;
	public 		$author;
	public 		$publisher;
	public 		$generator		= "Weever Cartographer R3S Template for Joomla";
	public 		$copyright;
	public 		$rating;
	public 		$r3sVersion		= "0.8.1";
	public 		$license;
	public 		$relationships;

}

class R3SItemMap {

	public 		$type;
	public 		$description;
	public 		$name;
	public 		$datetime		= array("published"=>"","modified"=>"","start"=>"","end"=>"");
	public 		$image			= array("mobile"=>"","full"=>"");
	public 		$tags			= array();
	public		$geo			= array();
	public 		$url;
	public 		$uuid;
	public 		$author;
	public 		$publisher;
	public 		$relationships;

}

class R3SChannelMap {

	public 		$thisPage;
	public 		$lastPage;
	public 		$count;
	public 		$type			= "htmlContent";
	public 		$sort;
	public 		$language;
	public 		$copyright;
	public 		$license;
	public 		$generator		= "Weever Cartographer R3S Template for Joomla";
	public 		$image			= array("mobile"=>"","full"=>"");
	public 		$publisher;
	public 		$rating;
	public 		$url;
	public 		$description;
	public		$geo			= array();
	public 		$name;
	public 		$r3sVersion		= "0.8.1";
	public 		$relationships;
	public 		$items;

}

class geoLocationalRelation {

	public 		$longitude;
	public		$latitude;
	public		$altitude;
	public		$address;
	
	public function __construct($lat, $long, $alt, $add) 
	{
	
		$this->longitude = $long;
		$this->latitude = $lat;
		$this->altitude = $alt;
		$this->address = $add;
	
	}
	
}

class jsonOutput {

	public $results;

}