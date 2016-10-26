<?php
/**
 * Class handles group templates.
 *
 * PHP version 5
 *
 * @category GroupTemplateSnapinManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @author   George1421  <null@null.null>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Class handles group templates.
 *
 * @category GroupTemplateSnapinManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @author   George1421  <null@null.null>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class GroupTemplateSnapinManager extends FOGManagerController
{
    /**
     * Install the group template table
     *
     * @return bool
     */
    public function install()
    {
        $this->uninstall();
        $sql = "CREATE TABLE `groupTemplate` ("
            . "`gtID` INTEGER NOT NULL AUTO_INCREMENT,"
            . "`gtName` VARCHAR(250) NOT NULL,"
            . "`gtDesc` LONGTEXT NOT NULL,"
            . "`gtImage` INTEGER NOT NULL,"
            . "`gtBuilding` INTEGER NOT NULL,"
            . "`gtCreateBy` VARCHAR(50) NOT NULL,"
            . "`gtUseAD` ENUM('0','1') NOT NULL DEFAULT '0',"
            . "`gtADDomain` VARCHAR(250) NOT NULL,"
            . "`gtADOU` LONGTEXT NOT NULL,"
            . "`gtADUser` VARCHAR(250) NOT NULL,"
            . "`gtADPass` VARCHAR(250) NOT NULL,"
            . "`gtADPassLegacy` LONGTEXT NOT NULL,"
            . "`gtProductKey` LONGTEXT NOT NULL,"
            . "`gtPrinterLevel` VARCHAR(2) NOT NULL,"
            . "`gtKernelArgs` VARCHAR(250) NOT NULL,"
            . "`gtKernel` VARCHAR(250) NOT NULL,"
            . "`gtDevice` VARCHAR(250) NOT NULL,"
            . "`gtInit` LONGTEXT NOT NULL,"
            . "`gtExitBios` LONGTEXT NOT NULL,"
            . "`gtExitEfi` LONGTEXT NOT NULL,"
            . "`gtEnforce` ENUM('0','1') NOT NULL DEFAULT '1',"
            . "PRIMARY KEY(`gtID`),"
            . "UNIQUE INDEX `name` (`gtName`)"
            . ") ENGINE=MyISAM AUTO_INCREMENT=1 "
            . "DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";
        return self::$DB->query($sql);
    }
    /**
     * Uninstalls the group template table
     *
     * @return bool
     */
    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `groupTemplate`";
        return self::$DB->query($sql);
    }
}
