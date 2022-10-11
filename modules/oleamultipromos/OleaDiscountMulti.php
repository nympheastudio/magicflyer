<?php 

if (version_compare('1.5', _PS_VERSION_) <= 0) {
	
require_once dirname(__FILE__).'/OleaDiscountMulti15.php';
class OleaDiscountMulti extends OleaDiscountMulti15 {
		
}

} else {

require_once dirname(__FILE__).'/OleaDiscountMulti14.php';
class OleaDiscountMulti extends OleaDiscountMulti14 {
		
}

}