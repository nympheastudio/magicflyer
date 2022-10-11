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
include_once(_PS_MODULE_DIR_.'jmsblog/class/JmsBlogHelper.php');
include_once(_PS_MODULE_DIR_.'jmsblog/JmsComment.php');
class AdminJmsblogCommentController extends ModuleAdminController {
	public function __construct()
	{
		$this->name = 'jmsblog';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		$this->categoryselect = array();
		$this->postselect = array();
		parent::__construct();
	}

	public function renderList()
	{	
		$this->_html = '';
		/* Validate & process */		
		if (Tools::isSubmit('delete_id_comment') || Tools::isSubmit('changeCommentStatus') || Tools::isSubmit('Approve') || Tools::isSubmit('ApproveAll'))
		{	
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderFilter();
				$this->_html .= $this->renderListComment($this->context->cookie->filter_post_id, $this->context->cookie->filter_cstate, $this->context->cookie->filter_cstart, $this->context->cookie->filter_climit);
				$this->html .= $this->renderPagination();
			}
			else
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderListComment();
			}
		}		
		else
		{		
			if(Tools::isSubmit('filter_post_id'))
				$this->context->cookie->filter_post_id 		= (int)Tools::getValue('filter_post_id', 0);
			if(Tools::isSubmit('filter_cstate'))
				$this->context->cookie->filter_cstate 		= (int)Tools::getValue('filter_cstate', -1);
			else 
				$this->context->cookie->filter_cstate 		= -1;
			if(Tools::isSubmit('cstart'))
				$this->context->cookie->filter_cstart 		= (int)Tools::getValue('cstart', 0);
			else 
				$this->context->cookie->filter_cstart 		= 0;
			if(Tools::isSubmit('climit'))
				$this->context->cookie->filter_climit 		= (int)Tools::getValue('climit', 20);			
			else 
				$this->context->cookie->filter_climit 		= 20;			
			$this->_html .= $this->renderFilter();	
			$this->_html .= $this->renderListComment($this->context->cookie->filter_post_id, $this->context->cookie->filter_cstate, $this->context->cookie->filter_cstart, $this->context->cookie->filter_climit);
			$this->_html .= $this->renderPagination();
		}		
		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();		

		/* Validation for configuration */
		if (Tools::isSubmit('changeCommentStatus') || Tools::isSubmit('Approve'))
		{
			if (!Validate::isInt(Tools::getValue('status_id_comment')))
				$errors[] = $this->l('Invalid Comment');
		}			
		elseif (Tools::isSubmit('delete_id_comment'))
		{
			if	((!Validate::isInt(Tools::getValue('delete_id_comment')) || !$this->commentExists((int)Tools::getValue('delete_id_comment'))))
				$errors[] = $this->l('Invalid id_comment');
		}				
		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= Tools::displayError(implode('<br />', $errors));
			return false;
		}
		/* Returns if validation is ok */
		return true;
	}
	private function _postProcess()
	{		
		$errors = array();		
		if (Tools::isSubmit('delete_id_comment'))
		{
			$item = new JmsComment((int)Tools::getValue('delete_id_comment'));
			$res = $item->delete();	
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogComment', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('changeCommentStatus') && Tools::isSubmit('status_id_comment'))
		{			
			$item = new JmsComment((int)Tools::getValue('status_id_comment'));
			if ($item->status == 0)
				$item->status = 1;
			else
				$item->status = 0;
			$res = $item->update();
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogComment', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('Approve') && Tools::isSubmit('status_id_comment'))
		{
			$item = new JmsComment((int)Tools::getValue('status_id_comment'));
			$item->status = 1;
			$res = $item->update();
			if (!$res)
				$this->_html .= Tools::displayError('The comment cant approved.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogComment', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		} 
		elseif (Tools::isSubmit('ApproveAll'))
		{
			$res = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'jmsblog_posts_comments` SET status = 1 WHERE status = -2');		
			if (!$res)
				$this->_html .= Tools::displayError('The comment cant approved.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogComment', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
	}
		
	public function commentExists($id_comment)
	{
		$req = 'SELECT hs.`post_id`
				FROM `'._DB_PREFIX_.'jmsblog_posts_comments` hs
				WHERE hs.`comment_id` = '.(int)$id_comment;
		$post = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($post);
	}
	
	public function getComments($post_id = 0, $state = -1, $start = 0, $limit = 20)
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$filter = '';		
		if	($state != -1)
			$filter = ' AND hss.`status` = '.(int)$state;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`comment_id`, hss.`title`, hss.`comment`, hss.`customer_name`, hss.`email`, hss.`customer_site`, hss.`time_add`, hss.`status`
			FROM '._DB_PREFIX_.'jmsblog_posts_comments hss			
			WHERE 1 '.
			$filter.
			($post_id ? ' AND hss.`post_id` = '.$post_id : ' ').'
			ORDER BY hss.time_add DESC 
			LIMIT '.$start.','.$limit
		);
	}
		
	public function renderFilter()
	{		
		$filter_post_id = $this->context->cookie->filter_post_id;		
		$filter_cstate = $this->context->cookie->filter_cstate;		
		$this->getPostSelect(0, 0);
		$categories = $this->categoryselect;		
		foreach($categories as $category)		
			$this->postselect[$category['category_id']] = $this->getCategoryPosts($category['category_id']);
		$posts = $this->postselect;	
		$tpl = $this->createTemplate('filter.tpl');
		$tpl->assign( array(			
			'categories'=>$categories,
			'posts'=>$posts,
			'filter_cstate'=>$filter_cstate,
			'link' => $this->context->link,	
			'filter_post_id' => $filter_post_id	
		));		
		return $tpl->fetch();
	}
	
	public function renderPagination()
	{	
		$cstart = (int)Tools::getValue('cstart', 0);
		$climit = (int)Tools::getValue('climit', 20);
		$ctotal = $this->getCommentCount($this->context->cookie->filter_post_id, $this->context->cookie->filter_cstate);
		if ($ctotal % $climit)
			$cpages = (int)($ctotal / $climit) + 1;
		else		
			$cpages = $ctotal / $climit;
		$tpl = $this->createTemplate('pagination.tpl');
		$tpl->assign( array(
			'cstart'=>$cstart,
			'climit'=>$climit,
			'cpages'=>$cpages,
			'ctotal'=>$ctotal,
			'link' => $this->context->link
		));	
		
		return $tpl->fetch();		
	}
	public function getCommentCount($post_id = 0, $state = -1)
	{				
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$filter = '';
		if	($state != -1)
			$filter = ' AND hss.`status` = '.(int)$state;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(hss.`comment_id`)
			FROM '._DB_PREFIX_.'jmsblog_posts_comments hss			
			WHERE 1 '.
			$filter.
			($post_id ? ' AND hss.`post_id` = '.$post_id : ' ').'
			ORDER BY hss.post_id'
		);
	}
	public function renderListComment($post_id = 0, $state = -1, $start = 0, $limit = 20)
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');				
		if (!$post_id)
			$post_id = (int)Tools::getValue('filter_post_id', 0);
		$waiting_total = $this->getCommentCount(0, -2);
		$items = $this->getComments($post_id, $state, $start, $limit);		
		$tpl = $this->createTemplate('listcomments.tpl');
		$tpl->assign( array(
			'post_id'=>$post_id,
			'waiting_total' => $waiting_total,
			'state'=>$state,
			'start'=>$start,
			'limit'=>$limit,
			'link' => $this->context->link,
			'items' => $items,
		));
		return $tpl->fetch();
	}
	public function renderNavigation()
	{
		$html = '<div class="navigation">';
		$html .= '<a class="btn btn-default" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminJmsblogComment').'" title="Back to Dashboard"><i class="icon-home"></i>Back to Dashboard</a>';
		$html .= '</div>';
		return $html;
	}
	public function getCategoryPosts($category_id = 0)
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id`, hssl.`title`
			FROM '._DB_PREFIX_.'jmsblog_posts hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)			
			WHERE hssl.id_lang = '.(int)$id_lang.						
			($category_id ? ' AND hss.`category_id` = '.$category_id : ' ').'
			ORDER BY hss.ordering'
		);
	}
	public function getPostSelect($parent = 0, $lvl = 0)
	{
		$lvl ++;
		$str = '';
		for	($i = 1; $i <= $lvl; $i++)
			$str .= '- ';
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`category_id`, hssl.`title`
			FROM '._DB_PREFIX_.'jmsblog_categories hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hss.category_id = hssl.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			' AND hss.`parent` = '.$parent.' 
			ORDER BY hss.`category_id` ASC'
		);
		if (count($items))
		{
			while ($element = current($items))
			{
				$items[key($items)]['lvl'] = $lvl;
				$items[key($items)]['title'] = $str.$items[key($items)]['title'];
				$this->categoryselect[] = $items[key($items)];
				$this->getPostSelect($element['category_id'], $lvl);
				next($items);
			}
		}
	}
}
?>