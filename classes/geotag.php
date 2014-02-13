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
 
// no direct access
defined('_JEXEC') or die('Restricted access');

class wxGeotag {

	public static function getGeoData($items, $_com = "com_content", &$gps = false, &$geoArray) {
		
		$db = JFactory::getDBO();
		$order = " "; $distance = " ";
		$itemIds = array();
		
		foreach( (array) $items as $k=>$v )
		{
			$itemIds[] = $v->id;
			$itemKeys[$v->id] = $k;
		}
			
		$itemIdList = implode(",",$itemIds);
		
		if( JRequest::getVar("latitude") && JRequest::getVar("longitude") )
		{
		
			$latitude 	= JRequest::getVar("latitude");
			$longitude 	= JRequest::getVar("longitude");
		
			$order 		= " ORDER BY distance ";
			$distance 	= ", glength( linestringfromwkb( linestring( 
								GeomFromText('POINT(".$latitude." ".$longitude.")'), 
								location ) ) ) as 'distance', 'rad' as 'distanceUnit' ";
			$gps 		= true;
			
		}
		
		$query = "SELECT component_id, AsText(location) AS location, address, label, kml, marker".$distance.
				"FROM
					#__weever_maps ".
				"WHERE
					component = ".$db->quote($_com)." AND component_id IN (".$itemIdList.") ".
				$order;

		$db->setQuery($query);

		$results 	= $db->loadObjectList();
		
		foreach( (array) $results as $k=>$v ) {
			
			self::convertToLatLong( $v );
			
			// make geo markers unique when sorting by distance
			
			if($gps == true)
				$geoArrayUnique[] = $v;
			else 
				$geoArray[$v->component_id][] = $v;
		
		}
		
		
		if($gps == true) {
		
			$contentItems = $items;
			$items = array();
			$i = 0;
		
			// rebuild $items to sort by marker distance, using geo markers as unique IDs now 
			// (multiple results of same $item will now be possible)
			
			foreach( (array) $geoArrayUnique as $k=>$v ) {
				
				$i++;
				
				$items[$i] = $contentItems[ $itemKeys[$v->component_id] ];
				$geoArray[$i] = $v;
				
				unset($geoArray[$i]->component_id);
				unset($geoArray[$i]->location);
			
			}
			
		}

		foreach( (array) $geoArray as $k=>$v ) {
		
			if( $v->marker[0] == "\\" || $v->marker[0] == "/" )
				$v->marker = JURI::root() . $v->marker;
		
		}
		
		if(JRequest::getVar("wxdebug"))
		{
			print_r($items);
			echo "\n\n";
			echo $query;
			echo $distance;
			echo "Lat: ".$latitude;
			echo "\n\nTEST";
			print_r($geoArray);
			jexit();
		}
			
			
		return $items;
		
	}
	
	
	public static function getK2PluginGeoData(&$feedItem, &$item) {
	
		$version = new JVersion;
		$joomlaVersion = substr($version->getShortVersion(), 0, 3);
	
		$extraFieldsFields = array(0=>"latitude",1=>"longitude",2=>"altitude",3=>"address",4=>"label",5=>"marker",6=>"kml");
		
		if(!$item->plugins)
			return false;

		$geoData = json_decode($item->plugins);


		if(!$geoData->weevermapsk2latitude_item)
			return false;
			
		$geoLatArray = 		explode( 	";", rtrim( $geoData->weevermapsk2latitude_item, 	";") 	);
		$geoLongArray = 	explode( 	";", rtrim( $geoData->weevermapsk2longitude_item, 	";") 	);
		$geoAddressArray = 	explode( 	";", rtrim( $geoData->weevermapsk2address_item, 	";") 	);
		$geoLabelArray = 	explode( 	";", rtrim( $geoData->weevermapsk2label_item, 		";") 	);
		$geoMarkerArray = 	explode( 	";", rtrim( $geoData->weevermapsk2marker_item, 		";") 	);

		foreach ( (array) $geoLatArray as $key=>$value )
		{
		
			if( @substr($geoMarkerArray[$key], 0, 1) == "\\" || @substr($geoMarkerArray[$key], 0, 1) == "/" )
				$geoMarkerArray[$key] = JURI::root() . $geoMarkerArray[$key]; 
		
			$feedItem->geo[$key][$extraFieldsFields[0]] = @$geoLatArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[1]] = @$geoLongArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[3]] = @$geoAddressArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[4]] = @$geoLabelArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[5]] = @$geoMarkerArray[$key];

		}
		
		if($geoData->weevermapsk2kml_item)
			$feedItem->geo[$key+1][$extraFieldsFields[6]] = $geoData->weevermapsk2kml_item;
	
	}


	// detect old method of geotagging K2 content
	// to fail detection, delete the "geo" extra field group

	public static function isLegacy() {
	
		$db = JFactory::getDBO();					
		$query = "SELECT * FROM #__k2_extra_fields_groups WHERE name = ".$db->Quote("geo");
		$db->setQuery($query);
		$results = @$db->loadObjectList();
		
		if( empty($results) )
			return false;

		return true;	
	
	}
	
	
	public static function getK2LegacyGeoData(&$feedItem, &$v) {
	
		$extraFields = json_decode($v->extra_fields);
		
		$extraFieldsFields = array(0=>"latitude",1=>"longitude",2=>"altitude",3=>"address",4=>"label",5=>"marker",6=>"kml");
		
		
		foreach ((array)$extraFields as $key=>$extraField)
		{
		
			if(strpos($extraField->value, ";"))
			{
				$values = explode(";",$extraField->value);
				
				foreach((array)$values as $kk=>$vv)
				{
					$feedItem->geo[$kk][$extraFieldsFields[$key]] = trim($vv, "\r\n");
				}
				
			}
			else 
				$feedItem->geo[0][$extraFieldsFields[$key]] = $extraField->value;
				
		}
		
		if(JRequest::getVar("wxdebugk2"))
		{
			print_r($extraFields);
			jexit();
		}

	}
	
	
	public static function convertToLatLong(&$obj) {
	
		$point = rtrim( ltrim( $obj->location, "(POINT" ), ")" );
		$point = explode(" ", $point);
		$obj->latitude = $point[0];
		$obj->longitude = $point[1];
	
	}


}