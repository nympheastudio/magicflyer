<?php

define('LECABFLASH_API_PROD', 'https://api.lecab.fr');
define('LECABFLASH_API_TEST', 'https://testapi.lecab.fr');
define('LECABFLASH_API_TEST_KEY', 'd910d2180df552add4a0315054fc9edf');
define('LECABFLASH_API_VERSION', 'v2.01');


/* DIMENSIONS/WEIGHT LIMITS */

define('LECABFLASH_MAX_WIDTH', 50);     // 50cm
define('LECABFLASH_MAX_HEIGHT', 39);    // 39cm
define('LECABFLASH_MAX_DEPTH', 15);     // 15cm
define('LECABFLASH_MAX_WEIGHT', 10);     // 10kg
define('LECABFLASH_MAX_VOL', 30);       // 30L

/* OTHERS */
define('LECABFLASH_DATE_DROP_OFFSET', 0); // minutes to add for drop time estimation

/* ERRORS CODES */

define('LECABFLASH_ERROR_ADDRESS_UNAVAILABLE', 'The address is out of zone');