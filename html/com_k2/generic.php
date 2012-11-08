<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter (rob@weeverapps.com)
*	Version: 	1.6.4.2
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

jimport('joomla.application.component.view');
jimport('joomla.environment.uri');

if( !class_exists('simple_html_dom_node') )
	require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php';
	
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'wxtags.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php');

	$lang =& JFactory::getLanguage();
    $mainframe = &JFactory::getApplication();
    $model = &$this->getModel('itemlist');
    $params = &JComponentHelper::getParams('com_k2');
    
    $ordering = $params->get('tagOrdering');
    
	// override K2's leading/primary/secondary/link lists
	JRequest::setVar('limit', 15);
	
	if( JRequest::getVar("start") )
		JRequest::setVar( "limitstart", JRequest::getVar("start") );
    
    if(JRequest::getVar("geotag") == "true")     
	     JRequest::setVar('limit', 150);
	     
	    if( JRequest::getVar('wxdebuglimit') )
	    	print_r( "\ngeneric: ". JRequest::getVar("limit") );
    
    $items = $model->getData($ordering);
    
    $feed = new R3SChannelMap;
    $feed->count = count($items);
    $feed->thisPage = 1;
    $feed->lastPage = 1;
    $feed->language = $lang->_default;
    $feed->sort = $ordering;
    $feed->url = JURI::root()."index.php?".$_SERVER['QUERY_STRING'];
    $feed->description = "test";
    $feed->name = "Content tagged \"".JRequest::getVar("tag")."\"";
    $feed->items = array();
	        
	$feed->url = str_replace("?template=weever_cartographer","",$feed->url);
	$feed->url = str_replace("&template=weever_cartographer","",$feed->url);
	        
	        
	foreach((array)$items as $k=>$v)
    	include('default/category_item.php');           	

    
	// Set the MIME type for JSON output.
	$document =& JFactory::getDocument();
	header('Content-type: application/json');		
	header('Cache-Control: no-cache, must-revalidate');
	
	$callback = JRequest::getVar('callback');		

	$json = json_encode($feed);
	
	if($callback)
		$json = $callback . "(". $json .")";
	
	print_r($json);
	jexit();