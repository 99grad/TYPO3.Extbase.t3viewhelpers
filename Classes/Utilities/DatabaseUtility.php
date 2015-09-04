<?php
namespace Nn\T3viewhelpers\Utilities;

class DatabaseUtility {

	/**
	 * Returns a valid DatabaseConnection object that is connected and ready
	 * to be used static
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	public static function getDatabaseConnection() {
		if (!$GLOBALS['TYPO3_DB']) {
			\TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeTypo3DbGlobal();
		}
		return $GLOBALS['TYPO3_DB'];
	}
}