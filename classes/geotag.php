<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter (rob@weeverapps.com)
*	Version: 	1.5
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

	
	public static function addGeoData(&$feedItem, &$item) {
	
		$version = new JVersion;
		$joomlaVersion = substr($version->getShortVersion(), 0, 3);
	
		$extraFieldsFields = array(0=>"latitude",1=>"longitude",2=>"altitude",3=>"address",4=>"label",5=>"marker",6=>"kml");
		
		if(!$item->plugins)
			return false;
				
		if($joomlaVersion == '1.5')
		{
		
			// K2 for Joomla 1.5 stores $item->plugin as INI string rather than JSON
			// ... and Joomla 1.5 has its own INI parsing class, JRegistry.
		
			$registry	= new JRegistry();
			$registry->loadINI($item->plugins);
			$geoData	= $registry->toObject( );
			
		}
		else 
		{
		
			// K2 for Joomla 1.6+ is normal.
		
			$geoData = json_decode($item->plugins);
			
		}
		
		if(!$geoData->weevermapsk2latitude_item)
			return false;
			
		if(JRequest::getVar("wxdebug"))
		{
			print_r($item->plugins);
			echo "\n\n";
			print_r($geoData);
			die();
		}

		$geoLatArray = 		explode( 	";", rtrim( $geoData->weevermapsk2latitude_item, 	";") 	);
		$geoLongArray = 	explode( 	";", rtrim( $geoData->weevermapsk2longitude_item, 	";") 	);
		$geoAddressArray = 	explode( 	";", rtrim( $geoData->weevermapsk2address_item, 	";") 	);
		$geoLabelArray = 	explode( 	";", rtrim( $geoData->weevermapsk2label_item, 		";") 	);
		$geoMarkerArray = 	explode( 	";", rtrim( $geoData->weevermapsk2marker_item, 		";") 	);

		
		foreach ( (array) $geoLatArray as $key=>$value )
		{
		
			$feedItem->geo[$key][$extraFieldsFields[0]] = $geoLatArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[1]] = $geoLongArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[3]] = $geoAddressArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[4]] = $geoLabelArray[$key];
			$feedItem->geo[$key][$extraFieldsFields[5]] = $geoMarkerArray[$key];

		}
		
		if($geoData->weevermapsk2kml_item)
			$feedItem->geo[$key+1][$extraFieldsFields[6]] = $geoData->weevermapsk2kml_item;
	
	}


	// detect old method of geotagging K2 content
	// to fail detection, delete the "geo" extra field group

	public static function isLegacy() {
	
		$db = &JFactory::getDBO();					
		$query = "SELECT * FROM #__k2_extra_fields_groups WHERE name = ".$db->Quote("geo");
		$db->setQuery($query);
		$results = @$db->loadObjectList();
		
		if( empty($results) )
			return false;

		return true;	
	
	}
	
	
	public static function addLegacyGeoData(&$feedItem, &$v) {
	
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

	}


}