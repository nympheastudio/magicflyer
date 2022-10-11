<?php

/**
 * 1997-2013 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <ecommerce@quadra-informatique.fr>
 * @copyright 1997-2013 Quadra Informatique
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvExport extends Module
{

	public function __construct()
	{
		$this->name = 'simplecsvexport';
		$this->tab = 'export';
		$this->version = 1.1;
		$this->author = 'Quadra informatique';

		parent::__construct();

		$this->displayName = $this->l('Simple CSV export of orders.');
		$this->description = $this->displayName;

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall simple CSV export of orders ?');
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		$id_order_tab = Tab::getIdFromClassName('AdminParentOrders');
		$id_lang_en = LanguageCore::getIdByIso('en');
		$id_lang_fr = LanguageCore::getIdByIso('fr');

		if (parent::install() == false ||
			!$this->registerHook('postUpdateOrderStatus') ||
			!$this->registerHook('adminOrder') ||
			!$this->installModuleTab('AdminSimpleCsvExport', array($id_lang_fr => 'Export commandes', $id_lang_en => $this->l('Export orders')), $id_order_tab)
			)
			return false;

		Configuration::updateValue('PS_CSV_SEND_COPY', 0);
		Configuration::updateValue('PS_CSV_MAIL', null);

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
				!Configuration::deleteByName('PS_CSV_SEND_COPY') ||
				!Configuration::deleteByName('PS_CSV_MAIL') ||
				!$this->uninstallModuleTab('AdminSimpleCsvExport'))
			return false;

		return true;
	}

	private function installModuleTab($tab_class, $tab_name, $id_tab_parent)
	{
		$tab = new Tab();
		$tab->name = $tab_name;
		$tab->class_name = $tab_class;
		$tab->module = $this->name;
		$tab->id_parent = (int)$id_tab_parent;
		if (!$tab->save())
			return false;
		return true;
	}

	private function uninstallModuleTab($tab_class)
	{
		$id_tab = Tab::getIdFromClassName($tab_class);
		if ($id_tab != 0)
		{
			$tab = new Tab($id_tab);
			$tab->delete();
			return true;
		}
		return false;
	}

	public function hookAdminOrder($params)
	{
		$token_simplecsvexport = Tools::getAdminToken('AdminSimpleCsvExport'.
				(int)Tab::getIdFromClassName('AdminSimpleCsvExport').
				(int)$this->context->employee->id);
		$this->context->smarty->assign(array(
			'token_simple' => $token_simplecsvexport,
			'id_order' => $params['id_order']
		));
		return $this->display(__FILE__, 'adminorders.tpl');
	}

}


