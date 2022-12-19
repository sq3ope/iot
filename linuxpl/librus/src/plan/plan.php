<?php

	$LIBRUS_OAUTH_AUTHORIZATION_URL = "https://api.librus.pl/OAuth/Authorization?client_id=46&response_type=code&scope=mydata";
	$LIBRUS_LOGIN_URL = "https://api.librus.pl/OAuth/Authorization?client_id=46";
	$LIBRUS_OAUTH_AUTHORIZATION_GRANT_URL = "https://api.librus.pl/OAuth/Authorization/Grant?client_id=46";
	$SYNERGIA_LOGIN_URL = "https://synergia.librus.pl/loguj/portalRodzina?code={{authorizationCode}}";
	$SYNERGIA_SCHEDULE_URL = "https://synergia.librus.pl/przegladaj_plan_lekcji";

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

		#print "response:$http_response";

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

		if (!array_key_exists("DZIENNIKSID",$cookies)) {
			die("[ERROR] DZIENNIKSID cookie missing");
		}

		curl_close( $ch );
		return $cookies['DZIENNIKSID'];
	}

	function login($librus_session_id, $login, $password) {
		global $LIBRUS_LOGIN_URL;

		$timeout = 10;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $LIBRUS_LOGIN_URL );
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Cookie: DZIENNIKSID=$librus_session_id",
			'Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "action=login&login=$login&pass=$password");
		$http_response = curl_exec($ch);
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		#print $http_response;

		if ($http_code != "200") {
			die("[ERROR] Wrong login response code: $http_code");
		}

		$json = json_decode($http_response, true);
		if (!is_array($json)) {
			die("[ERROR] response is not json");
		}

		if (!array_key_exists("status", $json)) {
			die("[ERROR] status field missing");
		}

		if ($json['status'] != "ok") {
			die("[ERROR] Wrong status: " . $json['status']);
		}

		curl_close( $ch );
	}

	function get_authorization_code($librus_session_id) {
		global $LIBRUS_OAUTH_AUTHORIZATION_GRANT_URL;

		$timeout = 10;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $LIBRUS_OAUTH_AUTHORIZATION_GRANT_URL );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: DZIENNIKSID=$librus_session_id"));
		$http_response = curl_exec($ch);
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$curl_info = curl_getinfo($ch);
		$headers = substr($http_response, 0, $curl_info["header_size"]);

		#print $http_response;

		if ($http_code != "302") {
			die("[ERROR] Wrong oauth authorization grant response code: $http_code");
		}

		preg_match("!\r\n(?:[Ll]ocation|URI): *(.*?) *\r\n!", $headers, $matches);
		if (sizeof($matches) == 0) {
			die("[ERROR] No 'location' header received");
		}

		$url = $matches[1];
		#print "url: $url";

		$url_components = parse_url($url);
		parse_str($url_components['query'], $params);

		if (!array_key_exists("code", $params)) {
			die("[ERROR] 'code' parameter missing");
		}

		curl_close( $ch );
		return $params['code'];
	}

	function get_synergia_session_id($authorization_code) {
		global $SYNERGIA_LOGIN_URL;

		$url = str_replace("{{authorizationCode}}", urlencode($authorization_code), $SYNERGIA_LOGIN_URL);
		#print($url);

		$timeout = 10;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		$http_response = curl_exec($ch);
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		#print $http_response;

		if ($http_code != "200") {
			die("[ERROR] Wrong synergia login response code: $http_code");
		}

		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $http_response, $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
		    parse_str($item, $cookie);
		    $cookies = array_merge($cookies, $cookie);
		}
		#var_dump($cookies);

		if (!array_key_exists("DZIENNIKSID",$cookies)) {
			die("[ERROR] DZIENNIKSID cookie missing");
		}

		curl_close( $ch );
		return $cookies['DZIENNIKSID'];
	}

	function get_schedule($synergia_session_id) {
		global $SYNERGIA_SCHEDULE_URL;

		$timeout = 10;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $SYNERGIA_SCHEDULE_URL );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
        $DZIENNIKSID = $synergia_session_id;
        $exploded_sid = explode("~", $synergia_session_id);
        $SDZIENNIKSID = $exploded_sid[1];
        #echo "DZIENNIKSID=$DZIENNIKSID; SDZIENNIKSID=$SDZIENNIKSID<br/>\n";
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: DZIENNIKSID=$DZIENNIKSID; SDZIENNIKSID=$SDZIENNIKSID"));

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
		}

		$http_response = curl_exec($ch);
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		#print $http_response;

		if ($http_code != "200") {
			die("[ERROR] Wrong get schedule response code: $http_code");
		}

		curl_close( $ch );
		return $http_response;
	}


	$librus_session_id = get_librus_session_id();
	#print "librus_session_id: $librus_session_id <br/>";

	login($librus_session_id, $config['librus_login'], $config['librus_password']);
	$authorization_code = get_authorization_code($librus_session_id);
	#print "authorization_code: $authorization_code <br/>";

	$synergia_session_id = get_synergia_session_id($authorization_code);
	#print "DZIENNIKSID=$synergia_session_id<br/>";

	$schedule = get_schedule($synergia_session_id);
	$schedule = str_replace('src="/', 'src="https://synergia.librus.pl/', $schedule);
	$schedule = str_replace('href="/', 'href="https://synergia.librus.pl/', $schedule);
	$schedule = str_replace('"/przegladaj_plan_lekcji"', "\"http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\"", $schedule);

	print $schedule;
?>
