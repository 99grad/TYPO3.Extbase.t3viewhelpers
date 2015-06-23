<?php
namespace Nn\T3viewhelpers\ViewHelpers\Uri;


/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;

class ImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Service\ImageService
	 * @inject
	 */
	protected $imageService;

	/**
	 * @var \Nn\T3viewhelpers\ViewHelpers\ImageViewHelper
	 * @inject
	 */
	protected $imageViewHelper;

	/**
	 * Resizes the image (if required) and returns its path. If the image was not resized, the path will be equal to $src
	 *
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 * @param string $src
	 * @param FileInterface|AbstractFileFolder $image
	 * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
	 * @param boolean $returnBackgroundStyles
	 
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string path to the image
	 */
	 
	public function render($src = NULL, $image = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE, $returnBackgroundStyles = NULL) {

		$imageViewHelper = $this->objectManager->get('\Nn\T3viewhelpers\ViewHelpers\ImageViewHelper');
		return $imageViewHelper->render($src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight, $treatIdAsReference, $image, true, $returnBackgroundStyles);
	}
}
