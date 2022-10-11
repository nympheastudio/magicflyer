<?php
/**
 * 2007-2015 PrestaShop
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
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

class EditMegaMenu extends BAMegaMenu
{
    public function __construct()
    {
        parent::__construct();
    }
    protected function getMegamenu($id = "", $id_shop = null)
    {
        if (empty($id_shop)) {
            $id_shop = (int) Tools::getValue('mega_id_shop', null);
        }
        return parent::getMegamenu($id, $id_shop);
    }
    public function selectedMenu($one = '')
    {
        $iditem = Tools::getValue('iditem', '');
        $name = Tools::getValue('name', '');
        $type = Tools::getValue('type', '');
        $id = Tools::getValue('id', '');
        $row = Tools::getValue('row', '');
        $selected = $this->listSelected($iditem, $type, $id, $row, $one);
        return $this->choicesSelect($name, $type, $id, $row, $selected);
    }

    public function selectedMenuProductList()
    {
        $iditem = Tools::getValue('iditem', '');

        $type = '';
        $id = Tools::getValue('id', '');
        $row = Tools::getValue('row', '');
        $selected = array();
        $list = $this->getMegamenu($iditem);
        $list = $list[0];
        $sub = Tools::jsonDecode($this->ps_cipherTool->decrypt($list['sub']));
        $selected[] = $sub[$row][$id][4];
        $html = null;
        $html .=$this->choicesSelectCategory($type, $id, $row, $selected);
        $html .='<br><input type="text" id="product_' . (int) $id . '" name="sub['. (int) $row . ']['
                . (int) $id . '][]" value="' . pSQL(htmlspecialchars(strip_tags($sub[$row][$id][5])))
                . '" placeholder="Number Product">';
        return $html;
    }

    public function inputMenu($one = '')
    {
        $iditem = Tools::getValue('iditem', '');
        $menu = $this->getMegamenu($iditem);
        $menu = $menu[0];
        if (empty($one)) {
            $html = '<input type="text" id="custom_url" name="custom_url" value="'
                    . pSQL(htmlspecialchars(strip_tags($menu['custom_url']))) . '" placeholder="Custom Url">';
        } else {
            $html = '<input type="text" id="custom_url_one" name="custom_url_one" value="'
                    . pSQL(htmlspecialchars(strip_tags($menu['custom_url_one']))) . '" placeholder="Custom Url">';
        }
        return $html;
    }

    public function loadHook()
    {
        $id = Tools::getValue('id', '');
        $row = Tools::getValue('row', '');
        //$name=Tools::getValue('name','');
        $iditem = Tools::getValue('iditem', '');
        $selected = array();
        $list = $this->getMegamenu($iditem);
        $list = $list[0];
        $sub = Tools::jsonDecode($this->ps_cipherTool->decrypt($list['sub']));
        $selected[] = $sub[$row][$id][4];
        return $this->getHook($row, $id, $selected);
    }

    public function inputSubMenu()
    {
        $id = Tools::getValue('id', '');
        $row = Tools::getValue('row', '');
        $iditem = Tools::getValue('iditem', '');
        $menu = $this->getMegamenu($iditem);
        $menu = $menu[0];
        $sub = Tools::jsonDecode($this->ps_cipherTool->decrypt($menu['sub']));
        return '<input type="text" id="product_' . (int) $id . '" name="sub[' . (int) $row . ']['
                . (int) $id . '][]" value="'
                . pSQL(htmlspecialchars(strip_tags($sub[$row][$id][4]))) . '" placeholder="Product Id">';
    }

    public function textareaSubMenu($code = '')
    {
        $id = Tools::getValue('id', '');
        $row = Tools::getValue('row', '');
        $iditem = Tools::getValue('iditem', '');
        $menu = $this->getMegamenu($iditem);
        $menu = $menu[0];
        $sub = Tools::jsonDecode($this->ps_cipherTool->decrypt($menu['sub']));
        if (empty($code) || $code == '') {
            return '<div class="btn btn-default" onclick="ViewEditHtml('
            . (int) $row . ',' . (int) $id . ')" ><i class="process-icon-edit "></i>'
                    .'<span>View/Edit Html</span></div><div class="view_edit_html" style="display:none">'
                    .'<textarea class="rte autoload_rte" aria-hidden="true" id="sub_' . (int) $row . '_'
                    . (int) $id . '" name="sub['
                    . (int) $row . '][' . (int) $id . '][]" rows="15" cols="15">'
                    . str_replace('\\r\\n', '&#13;', pSQL(htmlspecialchars($sub[$row][$id][4])))
                    . '</textarea></div><script type="text/javascript">var iso = "en";var ad = "";'
                    .'$(document).ready(function(){tinySetup({editor_selector :"autoload_rte"});});</script>';
        } else {
            return '<div class="btn btn-default" onclick="ViewEditHtml('
                    . (int) $row . ',' . (int) $id . ')" ><i class="process-icon-edit "></i>'
                    .'<span>View/Edit Php</span></div><div class="view_edit_html" style="display:none">'
                    .'<textarea class="" aria-hidden="true" id="sub_' . (int) $row . '_' . (int) $id . '" name="sub['
                    . (int) $row . '][' . (int) $id . '][]" rows="15" cols="15">'
                    . pSQL(htmlspecialchars(strip_tags($sub[$row][$id][4]))) . '</textarea></div>';
        }
    }

    public function textareaMenu($code = '')
    {
        $name = Tools::getValue('name', '');
        $iditem = Tools::getValue('iditem', '');
        $menu = $this->getMegamenu($iditem);
        $menu = $menu[0];
        if (empty($code)) {
            $tbs = pSQL(htmlspecialchars($this->ps_cipherTool->decrypt($menu['custom_url_one'])));
            $html = '<div class="btn btn-default" onclick="ViewEditHtmlMenu()" ><i class="process-icon-edit "></i>'
                    .'<span>View/Edit Html</span></div><div class="view_edit_html" style="display:none">'
                    .'<textarea class="rte autoload_rte" aria-hidden="true" id="'
                    . pSQL(htmlspecialchars(strip_tags($name))) . '_view_edit_html" name="'
                    . pSQL(htmlspecialchars(strip_tags($name))) . '" rows="15" cols="15">'
                    . str_replace('\\r\\n', '&#13;', $tbs)
                    . '</textarea></div><script type="text/javascript">var iso = "en";var ad = '
                    .'"";$(document).ready(function(){tinySetup({editor_selector :"autoload_rte"});});</script>';
            return $html;
        } else {
            $html = '<div class="btn btn-default" onclick="ViewEditHtmlMenu()" ><i class="process-icon-edit ">'
                    .'</i><span>View/Edit Php</span></div><div class="view_edit_html" style="display:none">'
                    .'<textarea class="rte autoload_rte" aria-hidden="true" id="'
                    . pSQL(htmlspecialchars(strip_tags($name))) . '_view_edit_html" name="'
                    . pSQL(htmlspecialchars(strip_tags($name))) . '" rows="15" cols="15">'
                    . pSQL(htmlspecialchars(strip_tags($this->ps_cipherTool->decrypt($menu['custom_url_one']))))
                    . '</textarea></div>';
            return $html;
        }
    }

    public function listSelected($iditem, $type, $id, $row, $one = '')
    {
        $selected = array();
        $list = $this->getMegamenu($iditem);
        $list = $list[0];
        if (empty($type)) {
            if (empty($one)) {
                $selected[] = $list['custom_url'];
            } else {
                $selected[] = $list['custom_url_one'];
            }
        } else {
            $sub = Tools::jsonDecode($this->ps_cipherTool->decrypt($list['sub']));
            unset($sub[$row][$id][0]);
            unset($sub[$row][$id][1]);
            unset($sub[$row][$id][2]);
            unset($sub[$row][$id][3]);
            $selected = $sub[$row][$id];
        }
        return $selected;
    }
}
