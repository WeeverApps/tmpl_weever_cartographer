<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2014 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter <rob@weeverapps.com>
*	Version: 	2.0.1
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

if( !defined('DS') )
	define( 'DS', DIRECTORY_SEPARATOR );

$item_model 	= $this->getModel('item');
$v 				= $item_model->prepareItem($v, "itemlist", "category");
$v->image 		= null;

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$v->id).'_XS.jpg'))
	$v->image = JURI::root().'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5('Image'.$v->id)."_XS.jpg";
	
else {

	if( class_exists('SimpleHTMLDomHelper') )
		$html = SimpleHTMLDomHelper::str_get_html($v->introtext);
		
	else {
	
		if( function_exists('str_get_html') )
			$html = str_get_html($v->introtext);
		else 
			$html = null;
		
	}
	
	foreach(@$html->find('img') as $vv)	{
		if(!$vv->src)
			continue;
		
		if(strstr($vv->src, "http://"))
			$v->image = $vv->src;
		else
			$v->image = JURI::root().$vv->src;
		
	}
}

$v->introtext 			= "";
$feedItem 				= new R3SItemMap;
$itemExtraFields 		= json_decode($v->extra_fields);
$feedItem->properties	= new StdClass();

if ($itemExtraFields) {

	foreach( (array) $itemExtraFields as $key=>$extraField ) {
	
		if( !isset( $extraField->value) || !isset( $extraField->id) || empty($extraFields) )
			continue;

		if ( isset($extraFields[$extraField->id]) )
			$feedItem->properties->{$extraFields[$extraField->id]} = $extraField->value;
	}

}


$feedItem->type 				= "htmlContent";
$feedItem->description 			= "";
$feedItem->name 				= wxTags::parse( $v->title );
$feedItem->datetime["published"] = $v->created;
$feedItem->datetime["modified"] = $v->modified;
$feedItem->image["mobile"] 		= $v->image;
$feedItem->image["full"] 		= $v->image;
$feedItem->uuid					= base64_encode( $document->getCfg('sitename') ) . "-k2-" . $v->id;
$feedItem->url 					= JURI::root()."index.php?option=com_k2&view=item&id=".$v->id;
$feedItem->author 				= @$v->author->name; // check to see if this exists someday
$feedItem->publisher 			= $document->getCfg('sitename');
$feedItem->url 					= str_replace( "?template=weever_cartographer", "", $feedItem->url );
$feedItem->url 					= str_replace( "&template=weever_cartographer", "", $feedItem->url );

if( count(@$v->tags) ) {

	foreach ($v->tags as $key=>$tag) {
	
		$feedItem->tags[$key]["name"] = $tag->name;
		$feedItem->tags[$key]["link"] = JURI::root().$tag->link;
		
	}

}

if( (bool) JRequest::getVar("geotag") ) {
	
	$feedItem->geo = isset( $geoArray[$v->id] ) ? $geoArray[$v->id] : null;

}

$feed->items[] = $feedItem;

