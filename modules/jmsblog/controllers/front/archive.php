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
class JmsblogArchiveModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{		
		
		parent::initContent();
		$_month = Tools::getValue('archive');		
		$posts = $this->getPosts($_month);		
		
		$jmsblog_setting = JmsBlogHelper::getSettingFieldsValues();		
		for($i = 0; $i < count($posts); $i++)
		{
			$posts[$i]['introtext'] = JmsBlogHelper::genIntrotext($posts[$i]['introtext'],$jmsblog_setting['JMSBLOG_INTROTEXT_LIMIT']);
			$posts[$i]['comment_count'] = JmsBlogHelper::getCommentCount($posts[$i]['post_id']);
		}		
		$this->context->controller->addCSS($this->module->getPathUri().'views/css/style.css', 'all');		
		$this->context->smarty->assign(array('meta_title' => $_month));				
		$this->context->smarty->assign(array(
			'month' => $_month,
			'posts' => $posts,
			'jmsblog_setting' => $jmsblog_setting,
			'image_baseurl' => $this->module->getPathUri().'views/img/'
		));
		$this->setTemplate('archive.tpl');
	}
	public function getPosts($_month = '')
	{
		$this->context = Context::getContext();		
		$id_lang = $this->context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id`,hss.`link_video`, hssl.`image`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`created`, hss.`modified`, hss.`views`,
			hssl.`alias`, hssl.`fulltext`, hssl.`introtext`,hssl.`meta_desc`, hssl.`meta_key`, hssl.`key_ref`, catsl.`title` AS category_name, catsl.`alias` AS category_alias
			FROM '._DB_PREFIX_.'jmsblog_posts hss			
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang catsl ON (catsl.category_id = hss.category_id)
			WHERE hss.active = 1 AND hssl.id_lang = '.(int)$id_lang.
			' AND DATE_FORMAT(hss.created,"%Y-%m") LIKE "%'.$_month.'%"
			GROUP BY hss.post_id
			ORDER BY hss.created DESC'
		);		
	}
}
