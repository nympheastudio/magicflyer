<?php
/**
* 2007-2015 PrestaShop
*
* Jms Theme Layout
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'jmsthemelayout/controllers/admin/AdminJmsthemelayoutBase.php');
class AdminJmsthemelayoutFooterController extends AdminJmsthemelayoutBaseController {
	public function __construct()
	{
		$this->name = 'jmsthemelayout';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		parent::__construct();
	}
}
?>