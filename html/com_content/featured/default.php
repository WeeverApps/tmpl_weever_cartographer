<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter <rob@weeverapps.com>
*	Version: 	1.6.4.1
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

jimport( 'joomla.application.component.view');
jimport( 'joomla.environment.uri' );

if( !class_exists('simple_html_dom_node') )
	require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php';
	
require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php';
require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php';


	$mainframe = &JFactory::getApplication();
	$lang =& JFactory::getLanguage();
	$items = $this->items;
	
	$version = new JVersion;
	$joomla = $version->getShortVersion();
	
	$geoArray = array();	$gps = false;	
	
	if( JRequest::getVar("geotag") == true && substr($joomla,0,3) != '1.5')
		$items = wxGeotag::getGeoData($items, "com_content", $gps, $geoArray);
	
	$feed = new R3SChannelMap;
	
	$feed->count = count($items);
	$feed->thisPage = 1;
	$feed->lastPage = 1;
	$feed->language = $lang->_default;
	$feed->sort = "normal";
	$feed->url = JURI::root()."index.php?".$_SERVER['QUERY_STRING'];
	$feed->description = "Home";
	$feed->name = $this->params->get('page_title');
	$feed->items = array();
	
	$feed->url = str_replace("?template=weever_cartographer","",$feed->url);
	$feed->url = str_replace("&template=weever_cartographer","",$feed->url);
		 
	foreach( (array) $items as $k=>$v )
	{
		$v->image = null;

		if( class_exists('SimpleHTMLDomHelper') )
			$html = SimpleHTMLDomHelper::str_get_html($v->text);
		else {
		
			if( function_exists('str_get_html') )
				$html = str_get_html($v->text);
			else 
				$html = null;
			
		}
		
		foreach(@$html->find('img') as $vv)
		{
			if($vv->src)
				$v->image = JURI::root().$vv->src;
		}
		
		if(!$v->image)
			$v->image = JURI::root()."media/com_weever/icon_live.png";
	
		$v->text = "";
		
		$feedItem = new R3SItemMap;
		
		$feedItem->type = "htmlContent";
		$feedItem->description = $v->text;
		$feedItem->name = $v->title;
		$feedItem->datetime["published"] = $v->created;
		$feedItem->datetime["modified"] = $v->modified;
		$feedItem->image["mobile"] = $v->image;
		$feedItem->image["full"] = $v->image;
		$feedItem->url = JURI::root()."index.php?option=com_content&view=article&id=".$v->id;
		$feedItem->author = $v->created_by;
		$feedItem->publisher = $mainframe->getCfg('sitename');
		
		if( isset($geoArray[$v->id]) && !$gps )
			$feedItem->geo = $geoArray[$v->id];
		elseif( $gps )
			$feedItem->geo = $geoArray[$k];
		
		$feedItem->url = str_replace("?template=weever_cartographer","",$feedItem->url);
		$feedItem->url = str_replace("&template=weever_cartographer","",$feedItem->url);
		
		$feed->items[] = $feedItem;
	}
		 
	// Set the MIME type for JSON output.
	header('Content-type: application/json');				
	header('Cache-Control: no-cache, must-revalidate');
	
	$callback = JRequest::getVar('callback');		

	$json = json_encode($feed);
	
	if($callback)
		$json = $callback . "(". $json .")";
	
	print_r($json);
	jexit();
