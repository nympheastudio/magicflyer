<?php

/**
 * ---------------------------------------------------------------------------------
 *
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
 * @version Release: $Revision: 1.1 $
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ---------------------------------------------------------------------------------
 */
class CsvExport
{

	public static function getOrderData($date_from, $date_to, $id_order)
	{
		$sql = 'SELECT
				o.id_order AS order_id,
				od.product_reference AS product_reference,
				od.product_name AS product_name,
				od.product_price AS product_price,
				od.product_weight AS product_weight,
				od.product_quantity AS product_quantity,
				od.product_quantity_refunded AS product_quantity_refunded,
				od.product_quantity_return AS product_quantity_return,
				od.tax_rate AS product_tax_rate,
				od.ecotax AS product_ecotax,
				od.discount_quantity_applied AS product_discount_quantity_applied,
				o.id_customer AS customer_id,
				ainv.firstname AS invoice_firstname,
				ainv.lastname AS invoice_lastname,
				ainv.company AS invoice_company,
				ainv.address1 AS invoice_address1,
				ainv.address2 AS invoice_address2,
				ainv.postcode AS invoice_postcode,
				ainv.city AS invoice_city,
				ainv.phone AS invoice_phone,
				ainv.phone_mobile AS invoice_phone_mobile,
				adel.firstname AS delivery_firstname,
				adel.lastname AS delivery_lastname,
				adel.company AS delivery_company,
				adel.address1 AS delivery_address1,
				adel.address2 AS delivery_address2,
				adel.postcode AS delivery_postcode,
				adel.city AS delivery_city,
				adel.phone AS delivery_phone,
				adel.phone_mobile AS delivery_phone_mobile,
				DATE(o.invoice_date) AS invoice_date,
				o.payment AS payment,
				DATE(o.delivery_date) AS delivery_date,
				o.shipping_number AS shipping_number,
				(SELECT osl.name
					FROM
						'._DB_PREFIX_.'order_history oh,
						'._DB_PREFIX_.'order_state_lang osl
					WHERE o.id_order=oh.id_order
					AND oh.id_order_state = osl.id_order_state
					ORDER BY id_order_history DESC LIMIT 1) AS status,
				o.total_discounts AS total_discounts,
				o.total_paid AS total_paid,
				o.total_paid_real AS total_paid_real,
				o.total_products AS total_products,
				o.total_products_wt AS total_products_wt,
				o.total_shipping AS total_shipping,
				o.total_wrapping AS total_wrapping,
				cur.name AS currency
			FROM '._DB_PREFIX_.'orders o
			LEFT JOIN '._DB_PREFIX_.'order_detail od ON o.id_order=od.id_order
			LEFT JOIN '._DB_PREFIX_.'address ainv ON o.id_address_invoice=ainv.id_address
			LEFT JOIN '._DB_PREFIX_.'address adel ON o.id_address_delivery=adel.id_address
			LEFT JOIN '._DB_PREFIX_.'currency cur ON o.id_currency=cur.id_currency';
		if ($id_order == null)
			$sql .= ' WHERE o.valid=1 AND o.date_add >= "'.$date_from.' 00:00:00" AND o.date_add <= "'.$date_to.' 23:59:59"
			AND o.current_state = '.Configuration::get('PS_OS_PAYMENT').' ';
		else
			$sql .= ' WHERE o.id_order='.$id_order;

		$sql .= ' ORDER BY o.id_order, od.id_order_detail ASC';

		$results = Db::getInstance()->ExecuteS($sql);

		return $results;
	}

	public static function exportCsv($datas, $filename)
	{
		header('Content-type: application/vnd.ms-excel');
		header('Content-disposition: attachment; filename="'.$filename.'.csv"');
		$i = 0;
		$csv = null;
		foreach ($datas as $d)
		{
			if ($i == 0)
				$csv .= '"'.implode('";"', array_keys($d)).'"'."\n";

			$csv .= '"'.implode('";"', $d).'"'."\n";
			$i++;
		}
		print ($csv);
		exit;
	}

	public static function exportByMail($datas, $filename, $date_from, $date_to, $time, $id_order, $mail_admin)
	{
		$stat = 0;
		$fp = fopen($filename, 'w');
		$i = 0;
		$csv = null;
		foreach ($datas as $d)
		{
			if ($i == 0)
				$csv .= '"'.implode('";"', array_keys($d)).'"'."\n";

			$csv .= '"'.implode('";"', $d).'"'."\n";
			$i++;
		}

		file_put_contents(_PS_MODULE_DIR_.'simplecsvexport/'.$filename, $csv);

		if ($id_order == null)
		{
			$template = 'export_orders';
			$mail_to = Configuration::get('PS_CSV_MAIL');
		}
		else
		{
			$template = 'export_order';
			$mail_to = $mail_admin;
		}

		if (Mail::Send(
					(int)Configuration::get('PS_LANG_DEFAULT'),
					$template,
					'CSV Export : '.$filename,
					array(
						'{dateFrom}' => $date_from,
						'{id_order}' => $id_order,
						'{dateTo}' => $date_to,
						'{time}' => str_replace('_', ' ', $time),
						'{filename}' => $filename
					),
					$mail_to,
					null,
					null,
					Configuration::get('PS_SHOP_NAME'),
					array(
						'content' => Tools::file_get_contents(_PS_MODULE_DIR_.'simplecsvexport/'.$filename),
						'name' => $filename, 'mime' => 'text/csv'
					),
					null,
					dirname(__FILE__).'/mails/'))
			$stat = 1;

		if (file_exists(_PS_MODULE_DIR_.'simplecsvexport/'.$filename))
			unlink(_PS_MODULE_DIR_.'simplecsvexport/'.$filename);

		if ($id_order == null)
		{
			Configuration::updateValue('PS_CSV_SEND_COPY', 0);
			Configuration::updateValue('PS_CSV_MAIL', null);
		}

		if ($stat == 1)
			return true;
		else
			return false;
	}

}

?>
