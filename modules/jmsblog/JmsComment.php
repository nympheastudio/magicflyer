<?php
/**
* 2007-2014 PrestaShop
*
* Jms Advance Blog
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

class JmsComment extends ObjectModel
{
	public $title;
	public $post_id;
	public $comment;
	public $customer_name;
	public $email;	
	public $customer_site;
	public $time_add;
	public $status;

	public static $definition = array(
		'table' => 'jmsblog_posts_comments',
		'primary' => 'comment_id',
		'multilang' => false,
		'fields' => array(
			'title' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
			'email' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 500),
			'customer_name' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 200),
			'customer_site' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 200),
			'comment' =>	array('type' => self::TYPE_HTML, 'validate' => 'isString', 'size' => 4000),	
			'post_id' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'time_add' =>	array('type' => self::TYPE_DATE),
			'status' =>	array('type' => self::TYPE_INT, 'required' => true),	

		)
	);
	public	function __construct($comment_id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($comment_id, $id_lang, $id_shop);
	}

	public function add($autodate = true, $null_values = false)
	{
		$res = true;
		$res = parent::add($autodate, $null_values);
		return $res;
	}
	
}