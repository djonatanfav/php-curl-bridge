<?php
/**
 * @Author(s)    : Djonatan Fávero (https://github.com/djonatanfav/)
 *               : Guilherme Lange (https://github.com/guilhermelange)
 * @Description  : Run a request with the same parameters was received
 * @Date         : 2021-03-08
 */

/** Include classes */
require_once __DIR__ . '/src/CurlBridge.php';

/** Create Object */
$CurlReplicated = new CurlBridge(); // STEP 1 -- To read request received

/** Run Request */
$CurlReplicated->runRequest();      // STEP 2 -- Run Request to Final URL

/** Send Respose */
$CurlReplicated->sendResponse();    // SET 3 -- RETURNS