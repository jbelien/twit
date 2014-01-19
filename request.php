<?php
function request($_oauth, $url, $params = array(), $method = 'GET') {
	// OAUTH
	$consumer_key              = $_oauth['consumer_key'];
	$consumer_secret           = $_oauth['consumer_secret'];
	$oauth_access_token        = $_oauth['oauth_access_token'];
	$oauth_access_token_secret = $_oauth['oauth_access_token_secret'];

	$oauth = array(
		'oauth_consumer_key' => $consumer_key,
		'oauth_nonce' => time(),
		'oauth_signature_method' => 'HMAC-SHA1',
		'oauth_timestamp' => time(),
		'oauth_token' => $oauth_access_token,
		'oauth_version' => '1.0'
	);

	$_params = array_merge($oauth, $params);

	$signature = $method.'&'.rawurlencode($url).'&'.rawurlencode(http_build_query($_params)); //var_dump($signature);
	$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret); //var_dump($composite_key);
	$oauth_signature = base64_encode(hash_hmac('sha1', $signature, $composite_key, TRUE)); //var_dump($oauth_signature);
	$oauth['oauth_signature'] = $oauth_signature;

	$header  = 'Authorization: OAuth';
	$header .= ' oauth_consumer_key="'.rawurlencode($oauth['oauth_consumer_key']).'"';
	$header .= ', oauth_nonce="'.rawurlencode($oauth['oauth_nonce']).'"';
	$header .= ', oauth_signature="'.rawurlencode($oauth['oauth_signature']).'"';
	$header .= ', oauth_signature_method="'.rawurlencode($oauth['oauth_signature_method']).'"';
	$header .= ', oauth_timestamp="'.rawurlencode($oauth['oauth_timestamp']).'"';
	$header .= ', oauth_token="'.rawurlencode($oauth['oauth_token']).'"';
	$header .= ', oauth_version="'.rawurlencode($oauth['oauth_version']).'"';
	foreach($params as $k => $v) $header .= ', '.$k.'="'.rawurlencode($v).'"';
	//var_dump($header);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HTTPHEADER, array($header, 'Expect:'));
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem'); // From http://curl.haxx.se/docs/caextract.html

	if ($method == 'POST') {
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	} else {
		curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
	}

	$json = curl_exec($ch);

	if ($json === FALSE) { trigger_error(curl_error($ch), E_USER_WARNING); }

	curl_close($ch);

	return json_decode($json);
}

