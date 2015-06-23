
<?php

if (!defined ('TYPO3_MODE')) die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\ViewHelpers\\ImageViewHelper'] = array(
	'className' => 'Nn\\T3viewhelpers\\ViewHelpers\\ImageViewHelper'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Fluid\\ViewHelpers\\Uri\\ImageViewHelper'] = array(
	'className' => 'Nn\\T3viewhelpers\\ViewHelpers\\Uri\\ImageViewHelper'
);

?>