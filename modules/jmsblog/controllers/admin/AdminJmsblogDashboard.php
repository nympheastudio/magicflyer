<?php
/**
* 2007-2015 PrestaShop
*
* Jms Blog
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;
class AdminJmsblogDashboardController extends ModuleAdminController {
	public function __construct()
	{
		$this->name = 'jmsblog';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);		
		parent::__construct();
	}

	public function renderList()
	{			
		
		$this->_html = '';
		/* Validate & process */		
		$this->_html .= $this->renderDashboard();
		return $this->_html;
	}
	
	public function renderDashboard()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');				
			
		$tpl = $this->createTemplate('dashboard.tpl');
		$tpl->assign( array(
			'link' => $this->context->link
		));
		return $tpl->fetch();
	}	
}
?>