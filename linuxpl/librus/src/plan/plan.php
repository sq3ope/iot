login: <?php print $config['librus_login']; ?>
<br/>
password: <?php print $config['librus_password']; ?>

<?php

	$LIBRUS_OAUTH_AUTHORIZATION_URL = "https://api.librus.pl/OAuth/Authorization?client_id=46&response_type=code&scope=mydata";

	function get_librus_session_id() {
		global $LIBRUS_OAUTH_AUTHORIZATION_URL;

		$timeout = 10;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $LIBRUS_OAUTH_AUTHORIZATION_URL );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		$http_response = curl_exec($ch);
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		#print $http_response;

		if ($http_code != "302") {
			die("[ERROR] Wrong oauth authorization response code: $http_code");
		}

		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $http_response, $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
		    parse_str($item, $cookie);
		    $cookies = array_merge($cookies, $cookie);
		}
		#var_dump($cookies);

		curl_close( $ch );
		return $cookies['DZIENNIKSID'];
	}


	# 1. Get Librus session id
	
	$librus_session_id = get_librus_session_id();
	print "librus_session_id: $librus_session_id";

?>
