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

if( JRequest::getVar('wxdebug') )
	ini_set('error_reporting', E_ALL);

jimport( 'joomla.application.component.view');
jimport( 'joomla.environment.uri' );

if( !class_exists('simple_html_dom_node') )
	require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php';
	
require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php';
require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php';


	$mainframe = JFactory::getApplication();
	$lang =& JFactory::getLanguage();
	
	$version = new JVersion;
	$joomla = $version->getShortVersion();
	
	if(substr($joomla,0,3) == '1.5')  // ### 1.5 only
	{
	
		$items = $this->getItems();

		if( strstr($this->category->image, "/") )
			$this->category->image = JURI::root().$this->category->image;
		else 
			$this->category->image = JURI::root()."images/stories/".$this->category->image;

	}
	else // 1.6+
	{ 
	
		$items = $this->items;

		if( strstr( $this->category->getParams()->get('image'), "/" ) )
			$this->category->image = JURI::root().$this->category->getParams()->get('image');
			
		else 
			$this->category->image = JURI::root()."images/stories/".$this->category->getParams()->get('image');
		
	}
	
	$geoArray = array();	$gps = false;
	
	if( JRequest::getVar("geotag") == true && substr($joomla,0,3) != '1.5' )
		$items = wxGeotag::getGeoData($items, "com_content", $gps, $geoArray);
		
	$feed = new R3SChannelMap;
	
	$feed->count 			= count($items);
	$feed->thisPage 		= 1;
	$feed->lastPage 		= 1;
	$feed->language 		= isset($lang->_default) ? $lang->_default : null;
	$feed->sort 			= "normal";
	$feed->url				= JURI::root()."index.php?".$_SERVER['QUERY_STRING'];
	$feed->description 		= $this->category->description;
	$feed->image["mobile"] 	= $this->category->image;
	$feed->image["full"] 	= $this->category->image;
	$feed->name 			= $this->params->get('page_title');
	$feed->items 			= array();
	
	$feed->url = str_replace("?template=weever_cartographer","",$feed->url);
	$feed->url = str_replace("&template=weever_cartographer","",$feed->url);
	
	if(substr($joomla,0,3) != '1.5') 
	{
	
		if( JRequest::getVar("wxdebug") )
			print_r( $this->children );
	
		foreach( (array) $this->children as $k=>$v )
		{
		
			$feedItem = new R3SItemMap;
		
			$feedItem->type 					= "channel";
			$feedItem->description 				= $v->description;
			$feedItem->name 					= $v->title;
			$feedItem->datetime["published"] 	= "";
			$feedItem->datetime["modified"] 	= "";
			$feedItem->images[] 				= "";
			$feedItem->url 						= JURI::root()."index.php?option=com_content&view=category&id=".$v->id;
			$feedItem->author 					= $mainframe->getCfg('sitename');
			$feedItem->publisher 				= $mainframe->getCfg('sitename');
		
		
		}	
	
	}
		 
	foreach( (array) $items as $k=>$v )
	{
	
		$v->image = null;
		
		$v->text = $v->introtext;

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
			if(!$v->image && $vv->src){
			
				if (strpos( $vv->src, "http://" ) !== false || strpos( $vv->src, "https://" ) !== false) {
					
					$v->image = $vv->src;
					
				} else {
					
					$v->image = JURI::root().$vv->src;
					
				}
				
			}
		}
	
		$v->text = "";
		
		$feedItem = new R3SItemMap;
		
		$feedItem->type 					= "htmlContent";
		$feedItem->description 				= "";
		$feedItem->name 					= $v->title;
		$feedItem->datetime["published"] 	= $v->created;
		$feedItem->datetime["modified"] 	= $v->modified;
		$feedItem->image["mobile"] 			= $v->image;
		$feedItem->image["full"] 			= $v->image;
		$feedItem->url 						= JURI::root()."index.php?option=com_content&view=article&id=".$v->id;
		$feedItem->author 					= $v->created_by;
		$feedItem->publisher 				= $mainframe->getCfg('sitename');
		
		if( isset($geoArray[$v->id]) && !$gps )
			$feedItem->geo 	= $geoArray[$v->id];
			
		elseif( $gps )
			$feedItem->geo 	= $geoArray[$k];
		
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