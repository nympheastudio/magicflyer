<?php
	/**
	 * 2007-2017 PrestaShop
	 *
	 * NOTICE OF LICENSE
	 *
	 * This source file is subject to the Academic Free License (AFL 3.0)
	 * that is bundled with this package in the file LICENSE.txt.
	 * It is also available through the world-wide-web at this URL:
	 * http://opensource.org/licenses/afl-3.0.php
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
	 * @copyright 2007-2015 PrestaShop SA
	 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
	 *  International Registered Trademark & Property of PrestaShop SA
	 */

	
class AdminOrdersController extends AdminOrdersControllerCore
{
	
	/*hack */
	public function ajaxProcessAddAttachment()
	{
		$attachment_descriptions = array();
		$attachment_names=array();

	if ($this->tabAccess['edit'] === '0')
	{
		return die(Tools::jsonEncode(array('error' => $this->l('You do not have the right permission'))));
	}
	if (isset($_FILES['attachment_file']))
	{

		if ((int)$_FILES['attachment_file']['error'] === 1)
		{
			$_FILES['attachment_file']['error'] = array();

			$max_upload = (int)ini_get('upload_max_filesize');
			$max_post = (int)ini_get('post_max_size');
			$upload_mb = min($max_upload, $max_post);
			$_FILES['attachment_file']['error'][] = sprintf($this->l('File %1$s exceeds the size allowed by the server. The limit is set to %2$d MB.'), '<b>'.$_FILES['attachment_file']['name'].'</b> ', '<b>'.$upload_mb.'</b>');
		}

		$_FILES['attachment_file']['error'] = array();



		$is_attachment_name_valid = true;
	
		if (empty($_FILES['attachment_file']['error']))
		{
		
			if (is_uploaded_file($_FILES['attachment_file']['tmp_name']))
			{
				if ($_FILES['attachment_file']['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024))
				{
					$_FILES['attachment_file']['error'][] = sprintf($this->l('The file is too large. Maximum size allowed is: %1$d kB. The file you are trying to upload is %2$d kB.'), (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024), number_format(($_FILES['attachment_file']['size'] / 1024), 2, '.', ''));
				}
				else if ($_FILES['attachment_file']['type']!='image/gif' && $_FILES['attachment_file']['type']!='image/png' && $_FILES['attachment_file']['type']!='image/jpeg' && $_FILES['attachment_file']['type']!='image/tiff' && $_FILES['attachment_file']['type']!='application/pdf' && $_FILES['attachment_file']['type']!='application/xml' && $_FILES['attachment_file']['type']!='image/svg+xml' && $_FILES['attachment_file']['type']!='application/octet-stream' && $_FILES['attachment_file']['type']!='application/zip'  && $_FILES['attachment_file']['type']!='application/x-zip-compressed')
				{
					$_FILES['attachment_file']['error'][] =1;
				}
				else
				{
					$name = (int)Tools::getValue('id_order').$_FILES['attachment_file']['name'];
					$_FILES['attachment_file']['tmpname']=$name;
					if (file_exists(_PS_UPLOAD_DIR_.$name))
					{
						$_FILES['attachment_file']['error'][] = $this->l('File copy failed, exists same name');
					}
					else
					{
						if (!copy($_FILES['attachment_file']['tmp_name'], _PS_UPLOAD_DIR_.$name))
						{
							$_FILES['attachment_file']['error'][] = $this->l('File copy failed');
						}
					}
				}
			}
			else
			{
				$_FILES['attachment_file']['error'][] = Tools::displayError('The file is missing.');
			}

			if (empty($_FILES['attachment_file']['error']))
			{
				$sql = 'SELECT * FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_order`=\''.(int)Tools::getValue('id_order').'\' and `name` = \''.pSQL($name).'\'';
				$tmp = Db::getInstance()->ExecuteS($sql);
				$_FILES['attachment_file']['id'] = (int)$tmp[0]['id_attachment'];
				if ((int)$tmp[0]['id_attachment'] == 0)
				{
					$res = Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'order_attachment(`id_order`, `name`, `type`) VALUES ('.(int)Tools::getValue('id_order').',\''.pSQL($name).'\',\''.pSQL($_FILES['attachment_file']['type']).'\')');
					$_FILES['attachment_file']['id'] = Db::getInstance()->Insert_ID();
				}
			}
		}

		die(Tools::jsonEncode($_FILES));

}
	}
	
	public function ajaxProcess()
	{
		
		if (Tools::getIsset('id_attachment'))
		{
			$sql = 'SELECT name,id_attachment FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_attachment`=\''.(int)Tools::getValue('id_attachment').'\'';
			$tmp = Db::getInstance()->ExecuteS($sql);

			if ((int)$tmp[0]['id_attachment']> 0)
			{
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_attachment`=\''.(int)Tools::getValue('id_attachment').'\'');
				if (file_exists(_PS_UPLOAD_DIR_.$tmp[0]['name']) && $tmp[0]['name']!='logoreplace.png')
				{
					@unlink(_PS_UPLOAD_DIR_.$tmp[0]['name']);
				}
				die('true');
			}
		}
	}


}
?>