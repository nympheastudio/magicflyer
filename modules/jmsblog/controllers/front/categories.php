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

include_once(_PS_MODULE_DIR_.'jmsblog/class/JmsBlogHelper.php');
class JmsblogCategoriesModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		
		parent::initContent();			
		$categories 	= $this->getCategories();		
		$jmsblog_setting = JmsBlogHelper::getSettingFieldsValues();
		for($i = 0; $i < count($categories); $i++)
		{
			$categories[$i]['introtext'] = JmsBlogHelper::genIntrotext($categories[$i]['description'], 120);			
		}
		$this->context->controller->addCSS($this->module->getPathUri().'views/css/style.css', 'all');				
		$this->context->smarty->assign(array('meta_title' => 'Categories'));
		$this->context->smarty->assign(array(
			'categories' => $categories,			
			'jmsblog_setting' => $jmsblog_setting,
			'image_baseurl' => $this->module->getPathUri().'views/img/'
		));
		$this->setTemplate('categories.tpl');
	}
	
	public function getCategories()
	{
		$this->context = Context::getContext();		
		$id_lang = $this->context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsblog_categories hss						
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hssl.category_id = hss.category_id)
			WHERE hss.active = 1 AND hss.parent = 0 AND hssl.id_lang = '.(int)$id_lang.
			' GROUP BY hss.category_id	
			ORDER BY hss.ordering'
		);
	}
}
