<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once(realpath(dirname(__FILE__) . '/../mondialrelay.php'));

class MRManagement extends MondialRelay
{
    private $_params = array();

    private $_resultList = array(
        'error'   => array(),
        'success' => array()
    );

    public function __construct($params)
    {
        $this->_params = $params;

        parent::__construct();
    }

    /*
    ** This method fill the database with the selected carrier
    */
    public function addSelectedCarrierToDB()
    {
        $db = Db::getInstance();
        // insutance
        $sql = 'SELECT insurance FROM ' . _DB_PREFIX_ . 'mr_method WHERE id_mr_method = ' . (int)$this->_params['id_mr_method'];
        $insurance = $db->getValue($sql);

        $query = 'SELECT `id_mr_selected` FROM `' . _DB_PREFIX_ . 'mr_selected` WHERE `id_cart` = ' . (int)$this->_params['id_cart'] . ' ';


        $columns = 'select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME="' . _DB_PREFIX_ . 'mr_selected"';
        $columns_mr2 = array();
        $column_exist = $db->executeS($columns);
        foreach ($column_exist as $key => $value) {
            $columns_mr2[] .= $value['COLUMN_NAME'];
        }


        // Not exist and needed for database
        unset($this->_params['relayPointInfo']['permaLinkDetail']);

        // Update if Exist else add a new entry
        if ($db->getRow($query)) {
            $query = 'UPDATE `' . _DB_PREFIX_ . 'mr_selected` 
                SET `id_method` = ' . (int)$this->_params['id_mr_method'] . ', 
                `MR_insurance` = ' . (int)$insurance . ',';
            if (is_array($this->_params['relayPointInfo'])) {
                foreach ($this->_params['relayPointInfo'] as $nameKey => $value) {
                    if (in_array('MR_Selected_' . MRTools::bqSQL($nameKey) . '', $columns_mr2)) {
                        $query .= '`MR_Selected_' . MRTools::bqSQL($nameKey) . '` = "' . pSQL($value) . '", ';
                    }
                }
            } else {  // Clean the existing relay point data
                $query .= '
                    MR_Selected_Num = NULL,
                    MR_Selected_LgAdr1 = NULL, 
                    MR_Selected_LgAdr2 = NULL,
                    MR_Selected_LgAdr3 = NULL,
                    MR_Selected_LgAdr4 = NULL,
                    MR_Selected_CP = NULL,
                    MR_Selected_Pays = NULL,
                    MR_Selected_Ville = NULL, ';
            }
            $query = rtrim($query, ', ') . ' WHERE `id_cart` = ' . (int)$this->_params['id_cart'];
        } else {
            $query = 'INSERT INTO `' . _DB_PREFIX_ . 'mr_selected`
                (`id_customer`, `id_method`, `id_cart`, MR_insurance, ';
            if (is_array($this->_params['relayPointInfo'])) {
                foreach ($this->_params['relayPointInfo'] as $nameKey => $value) {
                    if (in_array('MR_Selected_' . MRTools::bqSQL($nameKey) . '', $columns_mr2)) {
                        $query .= '`MR_Selected_' . MRTools::bqSQL($nameKey) . '`, ';
                    }
                }
            }
            $query = rtrim($query, ', ') . ') VALUES (
                    ' . (int)$this->_params['id_customer'] . ',
                    ' . (int)$this->_params['id_mr_method'] . ',
                    ' . (int)$this->_params['id_cart'] . ', 
                    ' . (int)$insurance . ', ';

            if (is_array($this->_params['relayPointInfo'])) {
                foreach ($this->_params['relayPointInfo'] as $nameKey => $value) {
                    if (in_array('MR_Selected_' . MRTools::bqSQL($nameKey) . '', $columns_mr2)) {
                        $query .= '"' . pSQL($value) . '", ';
                    }
                }
            }
            $query = rtrim($query, ', ') . ')';
        }
        $db->execute($query);
    }

    public function uninstallDetail()
    {
        $html = '';

        switch ($this->_params['action']) {
            case 'showFancy':
                $html .= '
                    <div id="PS_MRAskBackupContent">
                        <h2>' . $this->l('Uninstalling Mondial Relay') . '</h2>
                        <div>
                            ' . $this->l('You\'re about to uninstall the module, do you want to remove the database') . ' ?
                            <p id="PS_MRUninstallListSelection">
                                    <input type="button" id="PS_MR_BackupAction" href="javascript:void(0)" value="' . $this->l('Keep it and uninstall') . '"/>
                                    <br />
                                    <input type="button" id="PS_MR_UninstallAction" href="javascript:void(0)" value="' . $this->l('Remove and uninstall') . '" />
                                    <br />
                                    <input type="button" id="PS_MR_StopUninstall" href="javascript:void(0)" value="' . $this->l('Cancel') . '" />
                                    <br />
                            </p>
                        </div>
                    </div>
                ';
                $this->_resultList['html'] = $html;
                break;
            case 'backupAndUninstall':
                break;
            default:
        }

        return $this->_resultList;
    }

    public function deleteHistory()
    {
        $success = array();
        $error = array();

        if (is_array($this->_params['historyIdList']) && count($this->_params['historyIdList'])) {
            $query = '
                DELETE FROM `' . _DB_PREFIX_ . 'mr_history`
                WHERE id IN(';
            foreach ($this->_params['historyIdList'] as $id) {
                $query .= (int)$id . ', ';
            }
            $query = trim($query, ', ') . ')';

            $success['deletedListId'] = $this->_params['historyIdList'];
            $totalDeleted = Db::getInstance()->execute($query);
            if (count($success['deletedListId']) != $totalDeleted) {
                $error[] = $this->l('Some items can\'t be removed, please try to remove it again');
                foreach ($success['deletedListId'] as $id) {
                    $query = '
                        SELECT id FROM `' . _DB_PREFIX_ . 'mr_history`
                        WHERE id=' . (int)$id;
                    if (Db::getInstance()->getRow($query)
                        && ($key = array_search($id, $success['deletedListId'])) !== false
                    ) {
                        unset($success['deletedListId'][$key]);
                    }
                }
            }
            $this->_resultList['success'] = $success;
            $this->_resultList['other']['error'] = $error;
        } else {
            throw new Exception($this->l('Please select at least one history element'));
        }

        return $this->_resultList;
    }
}
