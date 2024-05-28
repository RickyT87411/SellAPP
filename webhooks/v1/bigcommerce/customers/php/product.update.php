<?php
require 'globals.php';

function processProductUpdate($request) {
	global $_VEND_DOMAIN_OAUTH2_KEYS_;
	global $_DEAR_DOMAIN_AUTH_KEYS_;

	$debug = false;

	// Try to parse application/x-www-form-urlencoded body
	try {
		$payload = $request->getBody();
		
		parse_str($payload, $params);
		
		// Enable debugging if passed in
		$debug = $params && is_array($params) && array_key_exists("debug" ,$params) && $params["debug"] === true ? true : false;
		
		// Check payload matches known format and expected _TRIGGER_TYPE_ before continuing
		if( !$params || !is_array($params) || 
			!array_key_exists(_VEND_WEBHOOK_TRIGGER_TYPE_PARAM ,$params) || $params[_VEND_WEBHOOK_TRIGGER_TYPE_PARAM] != _VEND_WEBHOOK_TRIGGER_TYPE_ ||
			!array_key_exists(_VEND_WEBHOOK_DOMAIN_PARAM_,$params) || !$params[_VEND_WEBHOOK_DOMAIN_PARAM_] ) {
			return;
		}
   
		$domain = $params[_VEND_WEBHOOK_DOMAIN_PARAM_];
		$product = urldecode($params[_VEND_WEBHOOK_PAYLOAD_PARAM_]);
		
		// Check that an actual domain and produt payload was extracted
		if( !$product || !is_array(json_decode($product,true)) ) {
			return;
		}
		
		productUpdate($domain, $product);
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return;
	}
}

function productUpdate($domain, $payload, $debug = false) {       
	try {         
		$product = json_decode($payload, true); 
	
		$id = array_key_exists(_VEND_PRODUCT_ID_FIELD_, $product) ? $product[_VEND_PRODUCT_ID_FIELD_] : null;
		//$sku = array_key_exists(_VEND_PRODUCT_SKU_FIELD_, $product) ? $product[_VEND_PRODUCT_SKU_FIELD_] : null;
		$sku = null;
		$name = array_key_exists(_VEND_PRODUCT_NAME_FIELD_, $product) ? $product[_VEND_PRODUCT_NAME_FIELD_] : null;

		// Only process products that have a $0.00 cost and/or supply price
		if( $id &&
			(
			 (array_key_exists(_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_, $product) && !$product[_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_]) ||
			 (array_key_exists(_VEND_PRODUCT_ACTUAL_COST_FIELD_, $product) && !$product[_VEND_PRODUCT_ACTUAL_COST_FIELD_])
			) &&
			_hasStockOnHand($domain, $product) &&
			!_isVendCompositeProduct($domain, $id, $vend_product) &&
			_isDearProduct($domain, $sku, $name, $dear_product)
		  ) {

			// Remove the 'Name' due to a bug in Vend, no need for this field anyways
			if(array_key_exists(_VEND_PRODUCT_BASE_NAME_FIELD_, $vend_product)) unset($vend_product[_VEND_PRODUCT_BASE_NAME_FIELD_]);
			if(array_key_exists(_VEND_PRODUCT_NAME_FIELD_, $vend_product))      unset($vend_product[_VEND_PRODUCT_NAME_FIELD_]);
			
			print_r($vend_product);
			$cost = _getDearLastProductCost($domain, $sku, $name);
			//$cost = 14.0800;
			//print_r(_updateVendProductCost($domain, $vend_product, $cost));

		}

	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return;
	}
}

function _hasStockOnhand($domain, $product, $debug = false) {
	try {
		// Test input parameters
		if( !$product || !$domain )
			return false;
		
		// Check @PRODUCT is formatted correctly and there is an Inventory array to test
		if( !is_array($product) || !array_key_exists(_VEND_PRODUCT_INVENTORY_FIELD_, $product) )
			return false;
		
		// Extract the Inventory array
		$inventory = $product[_VEND_PRODUCT_INVENTORY_FIELD_];
		$soh = 0;
		
		// Parse all the Outlets for Stock On Hand
		foreach( $inventory as $outlet ) {
			if( $outlet && is_array($outlet) && array_key_exists(_VEND_PRODUCT_SOH_FIELD_, $outlet) ) {
				// Add the Outlet's Stock On Hand to the running total (includes negatives)
				$soh += (is_double($outlet[_VEND_PRODUCT_SOH_FIELD_]) || is_numeric($outlet[_VEND_PRODUCT_SOH_FIELD_])) ? $outlet[_VEND_PRODUCT_SOH_FIELD_] : 0;
			}
		}

		// Return a result based on actual SOH (excludes negatives)
		return $soh > 0;
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _getDearLastProductCost($domain, $sku, $name, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_DEAR_DOMAIN_AUTH_KEYS_;
	
	try {
		// Test input parameters
		if( (!$sku && !$name) || !$domain)
			return null;
		
		// Decode JSON result of PurchaseList records
		$purchase_list = json_decode(_getDearPurchaseList($domain, _DEAR_PURCHASE_LIST_QUERY_), true);

		// Check result is not null
		if( $purchase_list && array_key_exists(_DEAR_PURCHASE_LIST_ORDERS_FIELD_, $purchase_list) ) {
			// Extract to the array of Purchase headers
			$purchase_list = $purchase_list[_DEAR_PURCHASE_LIST_ORDERS_FIELD_];

			// Ordered array of all the purcahses by @LastUpdatedDate
			$ordered_purchase_list = array();
			
			// Parse each Purchase in the list
			foreach( $purchase_list as $purchase ) {
				if( array_key_exists(_DEAR_PURCHASE_LIST_ORDER_LAST_UPDATED_FIELD_, $purchase) )
					$ordered_purchase_list[$purchase[_DEAR_PURCHASE_LIST_ORDER_LAST_UPDATED_FIELD_]] = $purchase;
			}
			
			// Reverse sort all date keys to be most recent first
			krsort($ordered_purchase_list);
			
			// Parse each Order by most recent
			foreach( $ordered_purchase_list as $purchase ) {
				$id = array_key_exists(_DEAR_PURCHASE_LIST_ORDER_ID_FIELD_, $purchase) ? $purchase[_DEAR_PURCHASE_LIST_ORDER_ID_FIELD_] : null;
				
				// Decode JSON result of Purchase record
				$purchase = json_decode(_getDearPurchase($domain, $id), true);
				
				// Check the Order format
				if( $purchase &&
					!array_key_exists(_DEAR_PURCHASE_ORDER_FIELD_, $purchase) ||
					!array_key_exists(_DEAR_PURCHASE_ORDER_LINE_FIELD_, $purchase[_DEAR_PURCHASE_ORDER_FIELD_]) )
					return null;
				
				// Extract the Order Lines
				$order_lines = $purchase[_DEAR_PURCHASE_ORDER_FIELD_][_DEAR_PURCHASE_ORDER_LINE_FIELD_];
				
				// Parse each Order Line to search for a matching @SKU or @NAME
				foreach( $order_lines as $line ) {
					//print_r($line);
					// If a @SKU or @NAME match is found then extract and return cost price
					if( (
						 $sku &&
						 array_key_exists(_DEAR_PURCHASE_ORDER_LINE_SKU_FIELD_, $line) &&
						 strcmp($line[_DEAR_PURCHASE_ORDER_LINE_SKU_FIELD_], $sku) == 0 
						) ||
						(
						 $name &&
						 array_key_exists(_DEAR_PURCHASE_ORDER_LINE_NAME_FIELD_, $line) &&
						 strcmp($line[_DEAR_PURCHASE_ORDER_LINE_NAME_FIELD_], $name) == 0 
						)
					) {
						
						$qty = array_key_exists(_DEAR_PURCHASE_ORDER_LINE_QUANTITY_FIELD_, $line) ? $line[_DEAR_PURCHASE_ORDER_LINE_QUANTITY_FIELD_] : null;
						$total = array_key_exists(_DEAR_PURCHASE_ORDER_LINE_TOTAL_FIELD_, $line) ? $line[_DEAR_PURCHASE_ORDER_LINE_TOTAL_FIELD_] : null;
						
						// Check that the @QUANTITY and @TOTAL are set and are numeric
						if( !$qty || !$total || !is_double($qty) || !is_double($total) )
							return null;
						
						// Return the cost price as a function of @TOTAL / @QUANTITY
						// This amount is tax exclusive and inclusive of calculated discounts already
						return $total / $qty;
					}
				}
			}
		}
		
		return null;
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return null;
	}
}

function _isVendCompositeProduct($domain, $id, &$product = null, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_VEND_DOMAIN_OAUTH2_KEYS_;
	
	try {
		// Test input parameters
		if( !$id || !$domain )
			return false;
		
		// Test auth key are configured for domain
		if( !$_VEND_DOMAIN_OAUTH2_KEYS_[$domain] )
			return false;
		
		$product_detail_api_endpoint = sprintf(_VEND_PRODUCT_API_V0_1_, $domain) . $id;

		$headers = array(
			"Content-type: application/json",
			"Authorization: " . $_VEND_DOMAIN_OAUTH2_KEYS_[$domain]
		);
		
		$response = _GET($product_detail_api_endpoint, $headers);
		
		// If no further information can be gathered about the product then exit
		if( !$response || !is_array(json_decode($response,true)) ) {
			return false;
		}

		// Extract product detaild
		$product = json_decode($response,true);
		// Unpack to product array
		$product = array_key_exists(_VEND_PRODUCT_FIELD_, $product) && $product[_VEND_PRODUCT_FIELD_] ? $product[_VEND_PRODUCT_FIELD_][0] : array();

		// Return the composite status of the product
		return array_key_exists(_VEND_PRODUCT_COMPOSITE_FIELD_, $product);
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _isDearProduct($domain, $sku, $name, &$product = null, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_DEAR_DOMAIN_AUTH_KEYS_;

	try {
		// Test input parameters
		if( (!$sku && !$name) || !$domain)
			return false;
		
		// Test auth keys are configured for domain
		if( !$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_] ||
			!$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_])
			return false;
		
		if( $sku )
			$product_list_api_endpoint = _DEAR_PRODUCT_API_V1_ . "?" . _DEAR_PRODUCT_SKU_QUERY_ . urlencode($sku);
		else if( $name )
			$product_list_api_endpoint = _DEAR_PRODUCT_API_V1_ . "?" . _DEAR_PRODUCT_NAME_QUERY_ . urlencode($name);

		// GET request to Dear PurchaseList API
		$curl = curl_init();
		
		$headers = array(
			"Content-type: application/json",
			_DEAR_AUTH_ACCOUNT_PARAM_   . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_],
			_DEAR_AUTH_KEY_PARAM_       . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_]
		);
		
		$response = _GET($product_list_api_endpoint, $headers);
		
		$product = json_decode($response,true);
		$product = $product && is_array($product) && array_key_exists(_DEAR_PRODUCT_NODE_, $product) ? $product[_DEAR_PRODUCT_NODE_] : null;

		return is_array($product);
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _getDearPurchaseList($domain, $query, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_DEAR_DOMAIN_AUTH_KEYS_;
	
	try {
		// Test input parameters
		if( !$query || !$domain)
			return null;
		
		// Test auth keys are configured for domain
		if( !$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_] ||
			!$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_])
			return null;
			
		$purchase_list_api_endpoint = _DEAR_PURCHASELIST_API_V1_ . $query;
		
		$headers = array(
			"Content-type: application/json",
			_DEAR_AUTH_ACCOUNT_PARAM_   . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_],
			_DEAR_AUTH_KEY_PARAM_       . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_]
		);
		
		$response = _GET($purchase_list_api_endpoint, $headers);

		return $response && is_array(json_decode($response,true)) ? $response : null;
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return null;
	}
}

function _getDearPurchase($domain, $id, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_DEAR_DOMAIN_AUTH_KEYS_;
	
	try {
		// Test input parameters
		if( !$id || !$domain)
			return null;
		
		// Test auth keys are configured for domain
		if( !$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_] ||
			!$_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_])
			return null;

		$purchase_api_endpoint = _DEAR_PURCHASE_API_V1_ . $id;
		
		$headers = array(
			"Content-type: application/json",
			_DEAR_AUTH_ACCOUNT_PARAM_   . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_],
			_DEAR_AUTH_KEY_PARAM_       . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_]
		);
		
		$response = _GET($purchase_api_endpoint, $headers);

		return $response && is_array(json_decode($response,true)) ? $response : null;
		
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return null;
	}
}

function _updateVendProductCost($domain, $product, $cost, $track_inventory_override = false, $debug = false) {
	try {
		// Test input parameters
		if( !$product || !$cost || !$domain )
			return false;
		
		// Check @PRODUCT is formatted correctly and that Inventory Tracking is actually set
		if( !is_array($product) || 
			!array_key_exists(_VEND_PRODUCT_TRACK_INVENTORY_FIELD_, $product) ||
			!array_key_exists(_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_, $product) )
			return false;
			
		$response = true;
			
		// If the Track Inventory setting is enabled then turn it off to force a cost update
		if( $product[_VEND_PRODUCT_TRACK_INVENTORY_FIELD_] === true )
			$response = _updateVendProductCostPre($domain, $product, $cost, $track_inventory_override);
		
		// Update the product again to return the original settings and Inventory levels (if affected)
		// Also, update the supplier price regardless
		if( $response )
			$response = _updateVendProductCostPost($domain, $product, $cost, $track_inventory_override);
		
		return $response;
		
	} catch (Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _updateVendProductCostPre ($domain, $product, $cost, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_VEND_DOMAIN_OAUTH2_KEYS_;
	
	try {
		// Test input parameters
		if( !$product || !$cost || !$domain )
			return false;
		
		// Test auth key are configured for domain
		if( !$_VEND_DOMAIN_OAUTH2_KEYS_[$domain] )
			return false;
		
		// Check @PRODUCT is formatted correctly and that Inventory Tracking is actually set
		if( !is_array($product) || 
			!array_key_exists(_VEND_PRODUCT_TRACK_INVENTORY_FIELD_, $product) ||
			!array_key_exists(_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_, $product) ||
			!$product[_VEND_PRODUCT_TRACK_INVENTORY_FIELD_] )
			return false;

		// Change Invetory Tracking to 'false'
		$product[_VEND_PRODUCT_TRACK_INVENTORY_FIELD_] = false;
		// Change the cost value to be the new value
		$product[_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_] = $cost;
		
		$product_api_endpoint = sprintf(_VEND_PRODUCT_API_V0_1_, $domain);
		$payload = json_encode($product);

		$headers = array(
			"Content-type: application/json",
			"Content-length: " . strlen($payload),
			"Authorization: " . $_VEND_DOMAIN_OAUTH2_KEYS_[$domain]
		);
		
		// Update the Vend Product to turn off Inventory Tracking and force an Average Cost update
		return _POST($product_api_endpoint, $payload, null, $headers);

	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _updateVendProductCostPost ($domain, $product, $cost, $track_inventory_override = false, $debug = false) {
	// Include OAUTH keys from globals.php
	global $_VEND_DOMAIN_OAUTH2_KEYS_;
	
	try {
		// Test input parameters
		if( !$product || !$cost || !$domain ) 
			return false;
		
		// Test auth key are configured for domain
		if( !$_VEND_DOMAIN_OAUTH2_KEYS_[$domain] )
			return false;
		
		// Check @PRODUCT is formatted correctly
		if( !is_array($product) || 
			!array_key_exists(_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_, $product) )
			return false;
		
		// Change the cost value to be the new value
		$product[_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_] = $cost;
		
		// Override the Track Inventory setting if requried
		if( $track_inventory_override && array_key_exists(_VEND_PRODUCT_TRACK_INVENTORY_FIELD_, $product) )
			$product[_VEND_PRODUCT_TRACK_INVENTORY_FIELD_] = $track_inventory_override;
		
		$product_api_endpoint = sprintf(_VEND_PRODUCT_API_V0_1_, $domain);
		$payload = json_encode($product);

		$headers = array(
			"Content-type: application/json",
			"Content-length: " . strlen($payload),
			"Authorization: " . $_VEND_DOMAIN_OAUTH2_KEYS_[$domain]
		);
		
		// Update the Vend Product to turn off Inventory Tracking and force an Average Cost update
		return _POST($product_api_endpoint, $payload, null, $headers);

	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _GET($url, $headers = null, $ssl_verification = false, $debug = false) {
	try {
		if( !$url )
			return false;
		
		// GET request to Vend Product API
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if( $headers)   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);

		// Execute RESTful API call
		$response = curl_exec($curl);
		
		// Test that connection was successful
		if ($response === false) {
			$info = curl_getinfo($curl);
			print_r($info);
		}
		
		curl_close($curl);
		
		return $response;
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}

function _POST($url, $payload = null, $fields = array(), $headers = null, $ssl_verification = false, $debug = false) {
	try {
		if( !$url || (!$payload && (!$fields || count($fields) <= 0)) )
			return false;

		// Checks if the @FIELDS parameter is set which will then switch the POST to 'form-data' mode
		$fields_count = $fields && is_array($fields) && count($fields) > 0 ? count($fields) : 0;
		// Convert the @FIELDS associative array to 'form-data' parameters
		// N.B. This will replace any input @PAYLOAD
		if( $fields_count > 0 ) {
			//url-ify the data for the POST
			foreach($fields as $key=>$value) { 
				$payload .= $key.'='.$value.'&'; 
			}
			rtrim($payload, '&');
		}
		
		// Correct the URL to remove any trailing '/' characters as this may lead to a GET request
		$url = strripos($url, "/") == strlen($url) - 1 ? substr($url, 0, strlen($url)-1) : $url;

		// GET request to Vend Product API
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);
		if( $fields_count <= 0 )    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		if( $fields_count >  0 )    curl_setopt($curl, CURLOPT_POST, $fields_count);
		if( $headers )              curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if( $payload )              curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		
		// Execute RESTful API call
		$response = curl_exec($curl);

		// Test that connection was successful
		if ($response === false) {
			$info = curl_getinfo($curl);
			print_r($info);
		}
		
		curl_close($curl);
		
		return $response;
	} catch(Exception $e) {
		if($debug)
			print_r($e);
		return false;
	}
}