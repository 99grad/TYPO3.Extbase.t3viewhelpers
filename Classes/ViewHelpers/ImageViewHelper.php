<?php
namespace Nn\T3viewhelpers\ViewHelpers;

/*																		*
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *																		*
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.										  *
 *																		*
 * This script is distributed in the hope that it will be useful, but	 *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-	*
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General	  *
 * Public License for more details.									   *
 *																		*/

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;

/**
 * Resizes a given image (if required) and renders the respective img tag
 *
 * = Examples =
 *
 * <code title="Default">
 * <f:image src="EXT:myext/Resources/Public/typo3_logo.png" alt="alt text" />
 * </code>
 * <output>
 * <img alt="alt text" src="typo3conf/ext/myext/Resources/Public/typo3_logo.png" width="396" height="375" />
 * or (in BE mode):
 * <img alt="alt text" src="../typo3conf/ext/viewhelpertest/Resources/Public/typo3_logo.png" width="396" height="375" />
 * </output>
 *
 * <code title="Image Object">
 * <f:image image="{imageObject}" />
 * </code>
 * <output>
 * <img alt="alt set in image record" src="fileadmin/_processed_/323223424.png" width="396" height="375" />
 * </output>
 *
 * <code title="Inline notation">
 * {f:image(src: 'EXT:viewhelpertest/Resources/Public/typo3_logo.png', alt: 'alt text', minWidth: 30, maxWidth: 40)}
 * </code>
 * <output>
 * <img alt="alt text" src="../typo3temp/pics/f13d79a526.png" width="40" height="38" />
 * (depending on your TYPO3s encryption key)
 * </output>
 *
 * <code title="non existing image">
 * <f:image src="NonExistingImage.png" alt="foo" />
 * </code>
 * <output>
 * Could not get image resource for "NonExistingImage.png".
 * </output>
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * @var \TYPO3\CMS\Extbase\Service\ImageService
	 * @inject
	 */
	protected $imageService;

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', FALSE);
		$this->registerTagAttribute('ismap', 'string', 'Specifies an image as a server-side image-map. Rarely used. Look at usemap instead', FALSE);
		$this->registerTagAttribute('longdesc', 'string', 'Specifies the URL to a document that contains a long description of an image', FALSE);
		$this->registerTagAttribute('usemap', 'string', 'Specifies an image as a client-side image-map', FALSE);
	}

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 * @param string $src a path to a file, a combined FAL identifier or an uid (integer). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
	 * @param FileInterface|AbstractFileFolder $image a FAL object
	 * @param boolean $onlyReturnUri
	 * @param boolean $returnBackgroundStyles
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string Rendered tag
	 */
	public function render($src = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE, $image = NULL, $onlyReturnUri = NULL, $returnBackgroundStyles = NULL ) {
		
//print_r($this);//		\TYPO3\CMS\Core\Utility\DebugUtility::debug( $this );
		
		
		if (is_null($src) && is_null($image) || !is_null($src) && !is_null($image)) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must either specify a string src or a File object.', 1382284106);
		}
		
		$additionalAttributes = $this->arguments['additionalAttributes'];
		$image = $this->imageService->getImage($src, $image, $treatIdAsReference);
		$cropParams = false;
		
		if ($treatIdAsReference && TYPO3_MODE != 'BE') {
		
			$fileReferenceRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Core\Resource\FileRepository');
			$fileRef = $fileReferenceRepository->findFileReferenceByUid( $src );
			
			
			$fileAttr = $image->getProperties();
			$cropExtensionLoaded = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('t3pimper');
			$imgvariants = json_decode($fileRef->getProperty('imgvariants'), true);
			
			if ($cropExtensionLoaded && $imgvariants) {

				$cropVariants = $imgvariants['crop'];
				
//				\TYPO3\CMS\Core\Utility\DebugUtility::debug( $cropVariants );

				$cprop = false;
				
				if (($maxWidth || $maxHeight) && $cropVariants['default']) {

					$bestprop = 'default';
					$factor = 0;

					$p = $cropVariants[$bestprop];
					
					$tw = ($p['x2']-$p['x1'])*$fileAttr['width'];
					$th = ($p['y2']-$p['y1'])*$fileAttr['height'];
			
					$srcProps = $tw/$th;
					
					if ($maxWidth && $maxHeight) {
						$f = 1/$tw*$maxWidth;
						if ($th*$f > $maxHeight) $f = 1/$th*$maxHeight;
						$maxWidth = $tw*$f;
						$maxHeight = $th*$f;
					}
					
					if (!$maxHeight) {
						$maxHeight = $maxWidth * ($srcProps);
					}
					
					if (!$maxWidth) {
						$maxWidth = $th * $srcProps * (1/$th*$maxHeight);
					}
					
					$cprop = 1/$srcProps;
					
//					echo "<pre>srcProps: {$srcProps} / cprop: {$cprop} / maxWidth: {$maxWidth} / maxHeight: {$maxHeight} f:".(1/$fileAttr['height']*$maxHeight)."</pre>";
					
				} else if ($width && $height) {

					$cprop = intval($height)/intval($width);
					
					// Bild-Einstellung finden, die am Besten zu den Proportionen passt
					$closest = false;
					$bestcrop = false;
					foreach ($cropVariants as $k=>$v) {
						$vprop = (($v['y2']-$v['y1'])*$fileAttr['height'])/(($v['x2']-$v['x1'])*$fileAttr['width']);
//						echo "<pre>{$k} [{$width}x{$height}] = vprop: {$vprop} / cprop: {$cprop} / == ".abs($vprop-$cprop)."</pre>";
						if ($closest === false || abs($vprop-$cprop) < $closest) {
							$closest = abs($vprop-$cprop);
							$bestprop = $k;
						}
					}

					// Faktor für Lineare Interpolation zwischen Endformat und bestmöglichem Ausschnitt
					$factor = max(min($closest,1),0);
				}
				
				if ($cprop !== false) {
//					echo "<pre>best: {$bestprop} - faktor: {$factor}</pre>";
									
					// Ziel-Größe des Ausschnitts errechnen
					$p = $cropVariants[$bestprop];
					
					$tw = ($p['x2']-$p['x1'])*$fileAttr['width'];
					$th = ($p['y2']-$p['y1'])*$fileAttr['height'];
					$cw = $tw + ($fileAttr['width']-$tw)*$factor;
					$ch = $cw * $cprop;
					
					$tw_x1 = $fileAttr['width'] * $p['x1'];
					$tw_x2 = $fileAttr['width'] * $p['x2'];
					$tw_y1 = $fileAttr['height'] * $p['y1'];
					$tw_y2 = $fileAttr['height'] * $p['y2'];
					
					$x1 = ($tw_x1+($tw_x2-$tw_x1)/2) - $cw/2;
					$y1 = ($tw_y1+($tw_y2-$tw_y1)/2) - $ch/2;
					
					$m =  abs($ch - ($tw_y2 - $tw_y1));
					
//					echo "<pre>{$cw} x {$ch}<br /> x1:{$x1} y1:{$y1}<br /> {$m} </pre>";
					
					if ($x1 + $cw > $fileAttr['width']) $x1 = $fileAttr['width'] - $cw;
					if ($x1 < 0) $x1 = 0;
					if ($y1 + $ch > $fileAttr['height']) $y1 = $fileAttr['height'] - $ch;
					if ($y1 < 0) $y1 = 0;
					
					$cropParams = array(
						'x1' => $x1, 
						'y1' => $y1, 
						'x2' => $x1 + $cw, 
						'y2' => $y1 + $ch
					);
				}
				
			}
		}
		
		$processingInstructions = array(
			'width' => $width,
			'height' => $height,
			'minWidth' => $minWidth,
			'minHeight' => $minHeight,
			'maxWidth' => $maxWidth,
			'maxHeight' => $maxHeight
		);

		if ($cropParams) {
//			echo "<pre>".print_r($cropParams,true)."</pre>";
			$cropProcessingInstructions = $this->initCrop($cropParams, $image->getProperties(), $processingInstructions);
			$processedImage = $this->imageService->applyProcessingInstructions($image, $cropProcessingInstructions);
			$imageUri = $this->imageService->getImageUri($processedImage);
		} else {
			$processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
			$imageUri = $this->imageService->getImageUri($processedImage);
		}
		
		if ($onlyReturnUri && !$returnBackgroundStyles) return $imageUri;
		
		$this->tag->addAttribute('src', $imageUri);
		$this->tag->addAttribute('width', $processedImage->getProperty('width'));
		$this->tag->addAttribute('height', $processedImage->getProperty('height'));

		$alt = $image->getProperty('alternative');
		$title = $image->getProperty('title');

		// The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
		if (empty($this->arguments['alt'])) {
			$this->tag->addAttribute('alt', $alt);
		}
		if (empty($this->arguments['title']) && $title) {
			$this->tag->addAttribute('title', $title);
		}
		
		$this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
	
		$configuration = $GLOBALS['TSFE']->tmpl->setup['tt_content.']["image."]['20.']['1.'];
				
		$layoutKey = $configuration['layoutKey'];
		unset($configuration['file.']);
		
		$attr = $this->cObj->getImageSourceCollection($layoutKey, $configuration, $imageUri);
		$this->tag->addAttribute($layoutKey, $attr);

		// Rendering mit Focus-Point
		if (TYPO3_MODE != 'BE') {
			if ($additionalAttributes['useFocusPoint'] || $returnBackgroundStyles) {

				$focalpoint = $imgvariants['focalpoint']['default'];
				if (!$focalpoint) $focalpoint = array('x1'=>0.5, 'y1'=>0.5);
				
				$this->tag->removeAttribute('useFocusPoint');
				$imgWidth = $this->tag->getAttribute('width');
				$imgHeight = $this->tag->getAttribute('height');
				$class = $this->tag->getAttribute('class');
				$id = $this->tag->getAttribute('id');
				
				$GLOBALS['TSFE']->getPageRenderer()->addCssFile('typo3conf/ext/t3pimper/Resources/Public/Frontend/Css/focuspoint.css');
				$GLOBALS['TSFE']->getPageRenderer()->addJsLibrary('focuspoint', 'typo3conf/ext/t3pimper/Resources/Public/Frontend/Js/jquery.focuspoint.min.js');

				$this->tag->removeAttribute('id');
				$this->tag->removeAttribute('class');
				$this->tag->removeAttribute('width');
				$this->tag->removeAttribute('height');				
				$imgTag =  $this->tag->render();

				if ($returnBackgroundStyles) {
					$focalpointX = 100 * $focalpoint['x1'];
					$focalpointY = 100 * $focalpoint['y1'];
					return "background-image:url({$imageUri});background-position:{$focalpointX}% {$focalpointY}%;background-size:cover;";
				} else {
					$focalpointX = max(-1, min(1, $focalpoint['x1'])*2-1);
					$focalpointY = max(-1, min(1, $focalpoint['y1'])*2-1);	
					return "<div  style=\"width:100%;height:{$imgHeight}px\" class=\"focuspoint\" data-focus-x=\"{$focalpointX}\" data-focus-y=\"{$focalpointY}\" data-image-w=\"{$imgWidth}\" data-image-h=\"{$imgHeight}\">{$imgTag}</div>";
				}
				
			}
		}
		
		return $this->tag->render();
	}
	
	
	
	protected function initCrop($cropValues, $currentFileProperties, $processingConfig){
		//$cropValues = json_decode($cropValues, TRUE);	   

		if (count($cropValues) >= 4 && count($cropValues) <= 6) {
			$cropData['cropValues'] = $cropValues;
		}

		if ($cropData) {
			$cropData['originalImage']['width'] = $currentFileProperties['width'];
			$cropData['originalImage']['height'] = $currentFileProperties['height'];
		}
		
		//debug($cropData,'cropData');
		if (empty($cropData)) {
			return NULL;
		}

		$cropParameters = $this->calcCrop($cropData, $processingConfig);
		return $cropParameters;
	}


	protected function calcCrop($cropData, $processingConfiguration) {

		$cropWidth = '';
		$cropHeight = '';
		$srcWidth = '';
		$srcHeight = '';
		$cropParameters = '';

		$width = (int)$processingConfiguration['width'];
		$height = (int)$processingConfiguration['height'];

		$maxWidth = (int)$processingConfiguration['maxWidth'];
		$maxHeight = (int)$processingConfiguration['maxHeight'];

		$fileWidth = $cropData['originalImage']['width'];
		$fileHeight = $cropData['originalImage']['height'];

		if ($maxWidth && $width > $maxWidth) {
			$width = $maxWidth;
		}

		if ($maxHeight && $height > $maxHeight) {
			$height = $maxHeight;
		}

		if ($cropData['cropValues']) {
			$cropValues = $cropData['cropValues'];
		} else {
			$cropValues = array();
		}

		if ($cropData['aspectRatio']) {
			$arValues = $cropData['aspectRatio'];
		} else {
			$arValues = array();
		}

		if ($cropValues) {
			$cropWidth = (int)$cropValues['x2'] - $cropValues['x1'];
			$cropHeight = (int)$cropValues['y2'] - $cropValues['y1'];

			if (!$arValues) {
				$arValues[0] = $cropValues['x2'] - $cropValues['x1'];
				$arValues[1] = $cropValues['y2'] - $cropValues['y1'];
			}
		}

		if ($maxWidth && !$width && !$height) {
			$width = $maxWidth;
			$height = (int)$width * ($arValues[1] / $arValues[0]);
		} elseif ($maxWidth && $width) {
			$height = (int)$width * ($arValues[1] / $arValues[0]);
		} elseif ($height && $width) {
			$width = (int)$height * ($arValues[0] / $arValues[1]);
			if ($maxWidth && $maxWidth <= $width) {
				$width = $maxWidth;
				$height = (int)$width * ($arValues[1] / $arValues[0]);
			}
		}

		//cropping
		if ($cropData['cropValues']) {
			$srcWidth = (int)$fileWidth * $width / $cropWidth;
			$srcHeight = (int)$fileHeight * $height / $cropHeight;

			$offsetX = (int)$cropValues['x1'] * ($width / $cropWidth);
			$offsetY = (int)$cropValues['y1'] * ($height / $cropHeight);

			$cropParameters = ' -crop ' . (int)$width . 'x' . (int)$height . '+' . (int)$offsetX . '+' . (int)$offsetY . ' ';
		}

		//set values
		$processingConfiguration['maxWidth'] = '';
		$processingConfiguration['maxHeight'] = '';

		if (!$cropValues) {
			$processingConfiguration['width'] = (int)$width . 'c';
			$processingConfiguration['height'] = (int)$height . 'c';
		} else {
			$processingConfiguration['width'] = (int)$srcWidth;
			$processingConfiguration['height'] = (int)$srcHeight;
			$processingConfiguration['additionalParameters'] = $cropParameters . $processingConfiguration['additionalParameters'];
		}	   

		return $processingConfiguration;
	}
}
