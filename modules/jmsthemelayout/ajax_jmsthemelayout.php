<?php
/**
* 2007-2014 PrestaShop
*
* Jms Advance Footer
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('jmsthemelayout.php');

$context = Context::getContext();
$rows = array();
if (Tools::getValue('action') == 'updateHomesOrdering' && Tools::getValue('homepage'))
{
	$homepage = Tools::getValue('homepage');

	foreach ($homepage as $position => $id_homepage)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsadv_homepages` SET `ordering` = '.(int)$position.'
			WHERE `id_homepage` = '.(int)$id_homepage
		);
	}
	$jms_homepage = new JmsHomepage();
	$jms_homepage->clearCache();
}
if (Tools::getValue('action') == 'updateProfOrdering' && Tools::getValue('prof'))
{
	$prof = Tools::getValue('prof');

	foreach ($prof as $position => $id_prof)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsadv_prof` SET `ordering` = '.(int)$position.'
			WHERE `id_prof` = '.(int)$id_prof
		);
	}
	$jms_prof = new JmsProf();
	$jms_prof->clearCache();
}
if (Tools::getValue('action') == 'updateRowsOrdering' && Tools::getValue('row'))
{

	$rows = Tools::getValue('row');
	
	foreach ($rows as $position => $id_row)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsadv_rows` SET `ordering` = '.(int)$position.'
			WHERE `id_row` = '.(int)$id_row
		);

	}
	$jms_row = new JmsRow();
	$jms_row->clearCache();
}
if (Tools::getValue('action') == 'updatePositionsOrdering' && Tools::getValue('position'))
{

	$positions = Tools::getValue('position');	
	foreach ($positions as $position => $id_pos)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsadv_position` SET `ordering` = '.(int)$position.'
			WHERE `id_position` = '.(int)$id_pos
		);

	}
	$jms_position = new JmsPosition();
	$jms_position->clearCache();
}
if (Tools::getValue('action') == 'updateBlocksOrdering' && Tools::getValue('block'))
{

	$blocks = Tools::getValue('block');	
	foreach ($blocks as $position => $id_block)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsadv_blocks` SET `ordering` = '.(int)$position.'
			WHERE `id_block` = '.(int)$id_block
		);

	}
	$jms_block = new JmsBlock();
	$jms_block->clearCache();
}
if (Tools::getValue('action') == 'savepos' && Tools::getValue('pos'))
{
	$pos = Tools::getValue('pos');			
	$pos_arr = explode('|', $pos);	
	foreach ($pos_arr as $position)
	{
		$pos_obj = explode('-', $position);
		$query = 'UPDATE `'._DB_PREFIX_.'jmsadv_position` SET `col_lg` = '.$pos_obj[1].', `col_md` = '.$pos_obj[2].', `col_sm` = '.$pos_obj[3].', `col_xs` = '.$pos_obj[4].
		'	WHERE `id_position` = '.$pos_obj[0];

		$res = Db::getInstance()->execute($query);

	}	
}