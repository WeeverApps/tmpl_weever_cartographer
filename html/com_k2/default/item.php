<?php
/*
*
*	Weever Cartographer R3S Output Template for Joomla
*	(c) 2010-2014 Weever Apps Inc. <http://www.weeverapps.com/>
*
*	Author: 	Robert Gerald Porter (rob@weeverapps.com)
*	Version: 	2.0
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

jimport( 'joomla.environment.uri' );

if( !class_exists('simple_html_dom_node') )
	require_once JPATH_THEMES . DS . 'weever_cartographer' . DS . 'simpledom' . DS . 'simpledom.php';
	
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'r3s.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'wxtags.php');
require_once(JPATH_THEMES . DS . 'weever_cartographer' . DS . 'classes' . DS . 'geotag.php');
	
	$conf =& JFactory::getConfig();
	$lang =& JFactory::getLanguage();
	
	$jsonHtml = new R3SHtmlContentDetailsMap;
	
	$jsonHtml->language = @$lang->_default;
	//$jsonHtml->publisher = $conf->getValue('config.sitename');
	
	$document =& JFactory::getDocument();
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	
	
	?>
	
	<!-- Start K2 Item Layout -->
	<span id="startOfPageId<?php echo JRequest::getInt('id'); ?>"></span>
	
	<div id="k2Container" class="itemView<?php echo ($this->item->featured) ? ' itemIsFeatured' : ''; ?><?php if($this->item->params->get('pageclass_sfx')) echo ' '.$this->item->params->get('pageclass_sfx'); ?>">
	
		<!-- Plugins: BeforeDisplay -->
		<?php //echo $this->item->event->BeforeDisplay; ?>
	
		<!-- K2 Plugins: K2BeforeDisplay -->
		<?php //echo $this->item->event->K2BeforeDisplay; ?>
	
	
		<?php if(JRequest::getVar("content_header") !== "false") : ?>
	
		<div class="itemHeader">
	
			<?php if($this->item->params->get('itemDateCreated')): ?>
			<!-- Date created -->
			<span class="itemDateCreated">
				<?php echo JHTML::_('date', $this->item->created , JText::_('DATE_FORMAT_LC2')); ?>
			</span>
			<?php endif; ?>
	
		  <?php if($this->item->params->get('itemTitle')): ?>
		  <!-- Item title -->
		  <h2 class="itemTitle">
		  	<?php echo $this->item->title; ?>
	
		  	<?php if($this->item->params->get('itemFeaturedNotice') && $this->item->featured): ?>
		  	<!-- Featured flag -->
		  	<span>
			  	<sup>
			  		<?php echo JText::_('Featured'); ?>
			  	</sup>
		  	</span>
		  	<?php endif; ?>
	
		  </h2>
		  <?php endif; ?>
	
			<?php if($this->item->params->get('itemAuthor')): ?>
			<!-- Item Author -->
			<span class="itemAuthor">
				<?php echo K2HelperUtilities::writtenBy($this->item->author->profile->gender); ?>&nbsp;
				<?php if(empty($this->item->created_by_alias)): ?>
				<?php echo $this->item->author->name; ?>
				<?php else: ?>
				<?php echo $this->item->author->name; ?>
				<?php endif; ?>
			</span>
			<?php endif; ?>
	
	  </div>
	  
	  <?php endif; ?>
	
	  <!-- Plugins: AfterDisplayTitle -->
	  <?php //echo $this->item->event->AfterDisplayTitle; ?>
	
	  <!-- K2 Plugins: K2AfterDisplayTitle -->
	  <?php //echo $this->item->event->K2AfterDisplayTitle; ?>
	
	  <div class="itemBody">
	
		  <!-- Plugins: BeforeDisplayContent -->
		  <?php //echo $this->item->event->BeforeDisplayContent; ?>
	
		  <!-- K2 Plugins: K2BeforeDisplayContent -->
		  <?php //echo $this->item->event->K2BeforeDisplayContent; ?>
	
		  <?php if($this->item->params->get('itemImage') && !empty($this->item->image)): ?>
		  <!-- Item Image -->
		  <div class="itemImageBlock">
			  <span class="itemImage">
			  		<img src="<?php echo $this->item->image; ?>" alt="<?php if(!empty($this->item->image_caption)) echo $this->item->image_caption; else echo $this->item->title; ?>" style="width:<?php echo $this->item->imageWidth; ?>px; height:auto;" />
			  </span>
	
			  <?php if($this->item->params->get('itemImageMainCaption') && !empty($this->item->image_caption)): ?>
			  <!-- Image caption -->
			  <span class="itemImageCaption"><?php echo $this->item->image_caption; ?></span>
			  <?php endif; ?>
	
			  <?php if($this->item->params->get('itemImageMainCredits') && !empty($this->item->image_credits)): ?>
			  <!-- Image credits -->
			  <span class="itemImageCredits"><?php echo $this->item->image_credits; ?></span>
			  <?php endif; ?>
	
			  <div class="clr"></div>
		  </div>
		  <?php endif; ?>
	
		  <?php if(!empty($this->item->fulltext)): ?>
	
		  <?php if($this->item->params->get('itemIntroText')): ?>
		  <!-- Item introtext -->
		  <div class="itemIntroText">
		  	<?php echo $this->item->introtext; ?>
		  </div>
		  <?php endif; ?>
	
		  <?php if($this->item->params->get('itemFullText')): ?>
		  <!-- Item fulltext -->
		  <div class="itemFullText">
		  	<?php echo $this->item->fulltext; ?>
		  </div>
		  <?php endif; ?>
	
		  <?php else: ?>
	
		  <!-- Item text -->
		  <div class="itemFullText">
		  	<?php echo $this->item->introtext; ?>
		  </div>
	
		  <?php endif; ?>
	
			<div class="clr"></div>
	
			<!-- remove to show extra fields -->
		  <?php if($this->item->params->get('itemExtraFields') && count($this->item->extra_fields)): ?>
		  <!-- Item extra fields
		  <div class="itemExtraFields">
		  	<h3><?php echo JText::_('Additional Info'); ?></h3>
		  	<ul>
				<?php foreach ($this->item->extra_fields as $key=>$extraField):?>
				<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
					<span class="itemExtraFieldsLabel"><?php echo $extraField->name; ?>:</span>
					<span class="itemExtraFieldsValue"><?php echo $extraField->value; ?></span>
				</li>
				<?php endforeach; ?>
				</ul>
		    <div class="clr"></div>
		  </div>  -->
		  <?php endif; ?>
	
		  <?php if(JRequest::getVar("content_header") !== "false") : ?>
		  
				<?php if($this->item->params->get('itemDateModified') && intval($this->item->modified)!=0):?>
				<!-- Item date modified -->
				<?php if($this->item->created != $this->item->modified): ?>
				<span class="itemDateModified">
					<?php echo JText::_('Last modified on'); ?> <?php echo JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2')); ?>
				</span>
				<?php endif; ?>
				<?php endif; ?>
				
		  <?php endif; ?>
	
		  <!-- Plugins: AfterDisplayContent -->
		  <?php //echo $this->item->event->AfterDisplayContent; ?>
	
		  <!-- K2 Plugins: K2AfterDisplayContent -->
		  <?php //echo $this->item->event->K2AfterDisplayContent; ?>
	
		  <div class="clr"></div>
	  </div>
	
	  <?php if(
	  $this->item->params->get('itemAttachments')
	  ): ?>
	  <div class="itemLinks">
	
	
	
		  <?php if($this->item->params->get('itemAttachments') && count($this->item->attachments)): ?>
		  <!-- Item attachments -->
		  <div class="itemAttachmentsBlock">
			  <span><?php echo JText::_("Download attachments:"); ?></span>
			  <ul class="itemAttachments">
			    <?php foreach ($this->item->attachments as $attachment): ?>
			    <li>
				    <a title="<?php echo htmlentities($attachment->titleAttribute, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$attachment->id); ?>">
				    	<?php echo $attachment->title ; ?>
				    </a>
				    <?php if($this->item->params->get('itemAttachmentsCounter')): ?>
				    <span>(<?php echo $attachment->hits; ?> <?php echo (count($attachment->hits)==1) ? JText::_("download") : JText::_("downloads"); ?>)</span>
				    <?php endif; ?>
			    </li>
			    <?php endforeach; ?>
			  </ul>
		  </div>
		  <?php endif; ?>
	
			<div class="clr"></div>
	  </div>
	  <?php endif; ?>
	
	  <?php if($this->item->params->get('itemVideo') && !empty($this->item->video)): ?>
	  <!-- Item video -->
	  <a name="itemVideoAnchor" id="itemVideoAnchor"></a>
	
	  <div class="itemVideoBlock">
	  	<h3><?php echo JText::_('Related Video'); ?></h3>
	
			<?php if($this->item->videoType=='embedded'): ?>
			<div class="itemVideoEmbedded">
				<?php echo $this->item->video; ?>
			</div>
			<?php else: ?>
			<span class="itemVideo"><?php echo $this->item->video; ?></span>
			<?php endif; ?>
	
		  <?php if($this->item->params->get('itemVideoCaption') && !empty($this->item->video_caption)): ?>
		  <span class="itemVideoCaption"><?php echo $this->item->video_caption; ?></span>
		  <?php endif; ?>
	
		  <?php if($this->item->params->get('itemVideoCredits') && !empty($this->item->video_credits)): ?>
		  <span class="itemVideoCredits"><?php echo $this->item->video_credits; ?></span>
		  <?php endif; ?>
	
		  <div class="clr"></div>
	  </div>
	  <?php endif; ?>
	
	  <?php if($this->item->params->get('itemImageGallery') && !empty($this->item->gallery)): ?>
	  <!-- Item image gallery -->
	  <a name="itemImageGalleryAnchor" id="itemImageGalleryAnchor"></a>
	  <div class="itemImageGallery">
		  <h3><?php echo JText::_('Image Gallery'); ?></h3>
		  <?php echo $this->item->gallery; ?>
	  </div>
	  <?php endif; ?>
	
	
	  <!-- Plugins: AfterDisplay -->
	  <?php //echo $this->item->event->AfterDisplay; ?>
	
	  <!-- K2 Plugins: K2AfterDisplay -->
	  <?php //echo $this->item->event->K2AfterDisplay; ?>
	
		<div class="clr"></div>
	</div>
	<!-- End K2 Item Layout -->
	
	
	<?php 
	
	$jsonHtml->html 		= ob_get_clean();
	$jsonHtml->uuid 		= $this->item->id;
	$jsonHtml->url 			= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	
	$db 		= JFactory::getDBO();					
	$query 		= "SELECT * FROM #__k2_extra_fields";
	
	$db->setQuery($query);
	
	$result = @$db->loadObjectList();
	
	$extraFields = array();
	
	foreach( (array) $result as $v ) 
	{
	
		$extraFields[$v->id] = $v->name;
	
	}
	
	$itemExtraFields 		= $this->item->extra_fields;
	
	$jsonHtml->properties	= new StdClass();
	
	foreach ( (array) $itemExtraFields as $key=>$extraField )
	{
	
		if ( $extraFields[$extraField->id] )
			$jsonHtml->properties->{$extraFields[$extraField->id]} 	= $extraField->value;
			
	}
	
	$jsonHtml->image["mobile"] = null;
	
	if(JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$jsonHtml->id).'_S.jpg'))
	{
		$jsonHtml->image["mobile"] = JURI::root().'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5('Image'.$jsonHtml->id)."_S.jpg";
	}	
	else
	{
		
		if( class_exists('SimpleHTMLDomHelper') ) 
			$html = SimpleHTMLDomHelper::str_get_html($jsonHtml->html);
		else {
		
			if( function_exists('str_get_html') )
				$html = str_get_html($jsonHtml->html);
			else 
				$html = null;
			
		}
		
		foreach(@$html->find('img') as $vv)
		{
			if($vv->src)
			{
				if(strstr($vv->src, "http://") || strstr($vv->src, "https://"))
					$jsonHtml->image["mobile"] = $vv->src;
				else
					$jsonHtml->image["mobile"] = JURI::root().$vv->src;
			}
		}

	}
	
	// Mask external links so we leave only internal ones to play with.
	$jsonHtml->html = str_replace("href=\"http://", "hrefmask=\"weever://", $jsonHtml->html);
	
	// Mask external links so we leave only internal ones to play with.
	$jsonHtml->html = str_replace("href=\"https://", "hrefmask=\"weevers://", $jsonHtml->html);
	
	// Mask telephone links
	$jsonHtml->html = str_replace("href=\"tel:", "hrefmask=\"weevertel:", $jsonHtml->html);
	
	// Mask email links
	$jsonHtml->html = str_replace("href=\"mailto:", "hrefmask=\"weevermail:", $jsonHtml->html);
	
	// Mask sms links
	$jsonHtml->html = str_replace("href=\"sms:", "hrefmask=\"weeversms:", $jsonHtml->html);
	
	// For HTML5 compliance, we take out spare target="_blank" links just so we don't duplicate
	$jsonHtml->html = str_replace("target=\"_blank\"", "", $jsonHtml->html);
	$jsonHtml->html = str_replace("href=\"", "target=\"_blank\" href=\"".JURI::root(), $jsonHtml->html);
	$jsonHtml->html = str_replace("src=\"/", "src=\"".JURI::root(), $jsonHtml->html);
	$jsonHtml->html = str_replace("src=\"images", "src=\"".JURI::root()."images", $jsonHtml->html);
	
	// Restore external links, ensure target="_blank" applies
	$jsonHtml->html = str_replace("hrefmask=\"weever://", "target=\"_blank\" href=\"http://", $jsonHtml->html);
	$jsonHtml->html = str_replace("hrefmask=\"weevers://", "target=\"_blank\" href=\"https://", $jsonHtml->html);
	$jsonHtml->html = str_replace("hrefmask=\"weevertel:", "href=\"tel:", $jsonHtml->html);
	$jsonHtml->html = str_replace("hrefmask=\"weevermail:", "href=\"mailto:", $jsonHtml->html);
	$jsonHtml->html = str_replace("hrefmask=\"weeversms:", "href=\"sms:", $jsonHtml->html);

	$jsonHtml->html = str_replace("<iframe title=\"YouTube video player\" width=\"480\" height=\"390\"",
										"<iframe title=\"YouTube video player\" width=\"160\" height=\"130\"", $jsonHtml->html);
										
	$jsonHtml->datetime["published"] = $this->item->created;
	$jsonHtml->datetime["modified"] = $this->item->modified;
	$jsonHtml->name = $this->item->title;
	
	if(empty($this->item->created_by_alias))
		$jsonHtml->author = $this->item->author->name;
	else 
		$JsonHtml->author = $this->item->created_by_alias;
		
	if(count($this->item->tags))
	{
	
		foreach ($this->item->tags as $key=>$tag)
		{
			$jsonHtml->tags[$key]["name"] = $tag->name;
			$jsonHtml->tags[$key]["link"] = JURI::root().$tag->link;
		}
	
	}
		
	
	$_com 		= "com_k2";
	$db 		= JFactory::getDBO();
	$geoArray 	= array();
	
	$query = "SELECT component_id, AsText(location) AS location, address, label, kml, marker ".
			"FROM
				#__weever_maps ".
			"WHERE
				component = ".$db->quote($_com)." 
				AND
				component_id = ".$this->item->id." ";

	$db->setQuery($query);
	$results = $db->loadObjectList();	
	
	foreach( (array) $results as $k=>$v ) {
	
		wxGeotag::convertToLatLong( $v );

		unset($v->component_id);
		unset($v->location);	
		
		$geoArray[] = $v;
			
	}
	
	$jsonHtml->geo = $geoArray;

	//wxGeotag::getK2PluginGeoData($jsonHtml, $this->item);
	
	$callback = JRequest::getVar('callback');
	
	$jsonOutput = new jsonOutput;
	$jsonOutput->results[] = $jsonHtml;
	$output = json_encode($jsonOutput);
	
	if($callback)
		$json = $callback."(".$output.")";
	else 
		$json = $output;
	
	print_r($json);
	
	jexit();
	
