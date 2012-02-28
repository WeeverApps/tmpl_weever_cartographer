<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2012 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter <rob@weeverapps.com>
*	Version: 	1.6.2
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

jimport('joomla.application.component.view');
jimport('joomla.environment.uri');

require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'wxtags.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php');

	if( JRequest::getVar('wxdebug') )
		ini_set('error_reporting', E_ALL);

	$lang 			= &JFactory::getLanguage();
    $document 		= &JFactory::getApplication();
    $model 			= &$this->getModel('itemlist');
    $params 		= &JComponentHelper::getParams('com_k2');
    $category 		= &JTable::getInstance('K2Category', 'Table');
    $id 			= JRequest::getInt('id');
    
    JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

    $category->load($id);

    //Merge params
    $cparams = new JParameter($category->params);
    
    if ($cparams->get('inheritFrom')) 
    {
        $masterCategory = &JTable::getInstance('K2Category', 'Table');
        $masterCategory->load($cparams->get('inheritFrom'));
        $cparams = new JParameter($masterCategory->params);
    }
    
    $params->merge($cparams);
    
    $ordering = $params->get('catOrdering');
    
    if(JRequest::getVar("geotag") == "true") 
    {
	    
	    $extraFieldsFields = array(0=>"latitude",1=>"longitude",2=>"altitude",3=>"address",4=>"label",5=>"marker",6=>"kml");

	    JRequest::setVar('limit', 150);
	    
    } else {

    	JRequest::setVar('limit', 15);
    
    }
    
    $items = $model->getData($ordering);
    
    if( JRequest::getVar('wxdebug') )
    	print_r($items);
    
    $geoArray = array();	$gps = false;
    
    if( JRequest::getVar("latitude") && JRequest::getVar("longitude") )
    	$items = wxGeotag::getGeoData($items, "com_k2", $gps, $geoArray);
    
    if(!$category->image)
    	$category->image = JURI::root()."media/com_weever/icon_live.png";
    else 
    	$category->image = JURI::root()."media/k2/categories/".$category->image;
    
    $feed 					= new R3SChannelMap;
    $feed->count 			= count($items);
    $feed->thisPage 		= 1;
    $feed->lastPage 		= 1;
    $feed->language 		= $lang->_default;
    $feed->sort 			= $ordering;
    $feed->url 				= JURI::root()."index.php?".$_SERVER['QUERY_STRING'];
    $feed->description 		= "test";
    $feed->name 			= $category->name;
    $feed->image["mobile"] 	= $category->image;
    $feed->image["full"] 	= $category->image;
    $feed->items 			= array();
	        
	$feed->url = str_replace("?template=weever_cartographer","",$feed->url);
	$feed->url = str_replace("&template=weever_cartographer","",$feed->url);  
	        
	foreach( (array) $items as $k=>$v )
    {
    	include('category_item.php');           	
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