<?php
/**
 * Class handles group templates.
 *
 * PHP version 5
 *
 * @category GroupTemplateAutoLogout
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @author   George1421  <null@null.null>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Class handles group templates.
 *
 * @category GroupTemplateAutoLogout
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @author   George1421  <null@null.null>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class GroupTemplateAutoLogout extends FOGController
{
    /**
     * The table name for this class to reference.
     *
     * @var string
     */
    protected $databaseTable = 'groupTemplate';
    /**
     * The table names and common name relations
     *
     * @var array
     */
    protected $databaseFields = array(
        'id' => 'gtID',
        'name' => 'gtName',
        'description' => 'gtDesc',
        'imageID' => 'gtImage',
        'building' => 'gtBuilding',
        'createdBy' => 'gtCreateBy',
        'useAD' => 'gtUseAD',
        'ADDomain' => 'gtADDomain',
        'ADOU' => 'gtADOU',
        'ADUser' => 'gtADUser',
        'ADPass' => 'gtADPass',
        'ADPassLegacy' => 'gtADPassLegacy',
        'productKey' => 'gtProductKey',
        'printerLevel' => 'gtPrinterLevel',
        'kernelArgs' => 'gtKernelArgs',
        'kernel' => 'gtKernel',
        'kernelDevice' => 'gtDevice',
        'init' => 'gtInit',
        'biosexit' => 'gtExitBios',
        'efiexit' => 'gtExitEfi',
        'enforce' => 'gtEnforce',
    );
    /**
     * The required fields
     *
     * @var array
     */
    protected $databaseFieldsRequired = array(
        'name',
    );
    /**
     * Additional fields
     *
     * @var array
     */
    protected $additionalFields = array(
        'group',
        'screen',
        'autologout',
        'printers',
        'printersnotinme',
        'snapins',
        'snapinsnotinme',
        'modules',
        'powermanagementtasks',
    );
}
