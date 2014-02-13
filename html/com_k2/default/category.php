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

if( !defined('DS') )
	define( 'DS', DIRECTORY_SEPARATOR );

if( JRequest::getVar('wxdebug') )
	ini_set('error_reporting', E_ALL);

jimport('joomla.application.component.view');
jimport('joomla.environment.uri');

if( !class_exists('simple_html_dom_node') )
	require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php';

require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'wxtags.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php');

	$lang 			= JFactory::getLanguage();
    $document 		= JFactory::getApplication();
    $model 			= &$this->getModel('itemlist');
    $params 		= JComponentHelper::getParams('com_k2');
    $category 		= JTable::getInstance('K2Category', 'Table');
    $id 			= JRequest::getInt('id');

    JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

    $category->load($id);

    $version        = new JVersion;
    $joomlaVersion  = substr($version->getShortVersion(), 0, 3);

    if( $joomlaVersion >= 3 ) {

        $cparams = new JRegistry();
        $cparams->loadString( $category->params );

    } else {
            //Merge params
        $cparams = new JParameter( $category->params );

    }
    
    if ( $cparams->get('inheritFrom') ) {

        $masterCategory = JTable::getInstance('K2Category', 'Table');
        $masterCategory->load($cparams->get('inheritFrom'));

        if( $joomlaVersion >= 3 ) {

            $cparams = new JRegistry();
            $cparams->loadString( $masterCategory->params );

        } else $cparams = new JParameter( $masterCategory->params );

    }
    
    $params->merge($cparams);
    
    $ordering = $params->get('catOrdering');
    
    $db 		= JFactory::getDBO();					
    $query 		= "SELECT * FROM #__k2_extra_fields";
    
    $db->setQuery($query);
    
    $result = @$db->loadObjectList();
    
    $extraFields = array();
    
    foreach( (array) $result as $v ) 
    {
    
    	$extraFields[$v->id] = $v->name;
    
    }
    	
    if( JRequest::getVar("start") )
    	JRequest::setVar( "limitstart", JRequest::getVar("start") );
    
    $items = $model->getData($ordering);
    
    $geoArray = array();	$gps = false;
    
    if( (bool) JRequest::getVar("geotag") ) {
    
        JRequest::setVar('limit', 150);

        if( (JRequest::getVar("latitude") && JRequest::getVar("longitude")) )
    	   $gps 	= true;

    	$items 	= wxGeotag::getGeoData($items, "com_k2", $gps, $geoArray);
    	
    }
    
    if($category->image)
    	$category->image = JURI::root()."media/k2/categories/".$category->image;
    
    $feed 					= new R3SChannelMap;
    $feed->count 			= count($items);
    $feed->thisPage 		= 1;
    $feed->lastPage 		= 1;
    $feed->language 		= isset($lang->_default) ? $lang->_default : null;
    $feed->sort 			= $ordering;
    $feed->url 				= JURI::root()."index.php?".$_SERVER['QUERY_STRING'];
    $feed->description 		= "test";
    $feed->name 			= $category->name;
    $feed->image["mobile"] 	= $category->image;
    $feed->image["full"] 	= $category->image;
    $feed->items 			= array();
	        
	$feed->url = str_replace("?template=weever_cartographer","",$feed->url);
	$feed->url = str_replace("&template=weever_cartographer","",$feed->url);  
	
	$i = 0;
	        
	foreach( (array) $items as $k=>$v )
    {
    	$i++;
    	
    	if( JRequest::getVar("latitude") && $i > 25 && !JRequest::getVar("nolimit") )
    		continue;
    		
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