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
include_once(_PS_MODULE_DIR_.'jmsblog/JmsComment.php');
class JmsblogPostModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$this->context->controller->addCSS($this->module->getPathUri().'views/css/style.css', 'all');	
		$this->context->controller->addJS($this->module->getPathUri().'views/js/jquery.validate.min.js', 'all');				
		$post_id 	= (int)Tools::getValue('post_id');
		$jmsblog_setting = JmsBlogHelper::getSettingFieldsValues();
		$module_instance = new JmsBlog(); 
		JmsBlogHelper::UpdateViews($post_id);
		$post 		= $this->getPost($post_id);						
		$cerrors = array ();
		$msg = (int)Tools::getValue('msg', 0);
		if (Tools::getValue('action') == 'submitComment')
		{
			$comment = new JmsComment();							
			$comment->title = $post['title'];
			$comment->post_id = $post_id;
			$comment->customer_name = Tools::getValue('customer_name');
			$comment->email = Tools::getValue('email');
			$comment->comment = Tools::getValue('comment');
			$comment->customer_site = Tools::getValue('customer_site');
			$comment->time_add = date('Y-m-d H:i:s');	
			if((int)$jmsblog_setting['JMSBLOG_AUTO_APPROVE_COMMENT'])	
				$comment->status = 1;		
			else 
				$comment->status = -2;			
			$lasttime = $this->getLatCommentTime($comment->email);			
			$res = false;
			if(strtotime($lasttime) + (int)$jmsblog_setting['JMSBLOG_COMMENT_DELAY'] < time()) 
			{
				$res = $comment->add();			
				if(!$res) 
					$cerrors[] = $module_instance->l('The comment could not be added.');
				else 
					Tools::redirect('index.php?fc=module&module=jmsblog&controller=post&msg=1&post_id='.$post_id."#comments"); 
			}	
			else
				$cerrors[] = $module_instance->l('Please wait before posting another comment').' '.$jmsblog_setting['JMSBLOG_COMMENT_DELAY'].' '.$module_instance->l('seconds before posting a new comment');
		}
		$comments = $this->getComments($post_id);		
		$count = count($comments);				
		$category = JmsBlogHelper::getCategory($post['category_id']);
		$this->context->controller->addCSS($this->module->getPathUri().'css/style.css', 'all');
		$this->context->smarty->assign(array('meta_title' => $post['title']));			
		$this->context->smarty->assign(array(
			'post' => $post,
			'current_category' => $category,
			'msg' => $msg,
			'comments' => $comments,
			'customer' => (array)$this->context->customer,
			'jmsblog_setting' => $jmsblog_setting,
			'cerrors' => $cerrors,				
			'link' => $this->context->link,
			'image_baseurl' => $this->module->getPathUri().'views/img/',
			'module_dir' => _PS_MODULE_DIR_.'jmsblog/views/templates/front/'
		));

		$this->setTemplate('post.tpl');
	}
	function getLatCommentTime($email) 
	{
		$result = Db::getInstance()->getValue('
				SELECT pc.`time_add`
				FROM `'._DB_PREFIX_.'jmsblog_posts_comments` pc
				WHERE pc.`email` = \''.$email.'\'
				ORDER BY pc.`time_add` DESC'
			);
		return $result;
	}
	public function getPost($post_id)
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT hss.`post_id`, hssl.`image`,hss.`link_video`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`created`, hss.`modified`, hss.`views`,
			hssl.`alias`, hssl.`fulltext`, hssl.`introtext`, hssl.`meta_desc`, hssl.`meta_key`, hssl.`key_ref`, hss.`category_id`, hscl.`title` as category_name, hscl.`alias` as category_alias
			FROM '._DB_PREFIX_.'jmsblog_posts hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories hsc ON (hsc.category_id = hss.category_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hscl ON (hscl.category_id = hss.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			' AND hss.`post_id` = '.$post_id.' 
			ORDER BY hss.ordering'
		);
	}

	public function getComments($post_id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT * FROM `'._DB_PREFIX_.'jmsblog_posts_comments`
		WHERE post_id ='.$post_id.' AND status = 1
		ORDER BY time_add ASC
		');
	}	
}
