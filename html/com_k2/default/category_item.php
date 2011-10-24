<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2011 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter (rob@weeverapps.com)
*	Version: 	1.1
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

$v->image = null;

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$v->id).'_XS.jpg'))
{
	$v->image = JURI::root().'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5('Image'.$v->id)."_XS.jpg";
}	
else
{
	$html = SimpleHTMLDomHelper::str_get_html($v->introtext);
	
	foreach(@$html->find('img') as $vv)
	{
		if($vv->src)
			$v->image = JURI::root().$vv->src;
	}
	
	if(!$v->image)
		$v->image = JURI::root()."media/com_weever/icon_live.png";
}

$v->introtext = "";

$feedItem = new R3SItemMap;

$feedItem->type = "htmlContent";
$feedItem->description = $v->introtext;
$feedItem->name = $v->title;
$feedItem->datetime["published"] = $v->created;
$feedItem->datetime["modified"] = $v->modified;
$feedItem->image["mobile"] = $v->image;
$feedItem->image["full"] = $v->image;
$feedItem->url = JURI::root()."index.php?option=com_k2&view=item&id=".$v->id;
$feedItem->author = $v->author->name;
$feedItem->publisher = $mainframe->getCfg('sitename');
$feedItem->url = str_replace("?template=weever_cartographer","",$feedItem->url);
$feedItem->url = str_replace("&template=weever_cartographer","",$feedItem->url);

if(count($v->tags))
{

	foreach ($v->tags as $key=>$tag)
	{
		$feedItem->tags[$key]["name"] = $tag->name;
		$feedItem->tags[$key]["link"] = JURI::root().$tag->link;
	}

}

$extraFields = json_decode($v->extra_fields);


if(JRequest::getVar("geotag") == "true") 
{
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


$feed->items[] = $feedItem;

