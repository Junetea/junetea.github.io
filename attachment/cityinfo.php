<?php
	class CityDB extends SQLite3 {
		function __construct() {
			$this->open('./cityinfo.db');
		}
	}	


	$db = new CityDB();
	if(!$db) {
		echo $db->lastErrorMsg();
	}
	else {
		$method = $_GET['method'];
		if ($method) {
			if ($_GET['method'] == 'provs') {
				$py_sql =<<<EOF
					SELECT provcode, provname, pinyin, py FROM weather_prov;	
EOF;

				$ret = $db->query($py_sql);
				if (!$ret) {
					echo $db->lastErrorMsg();
				}
				else {
					$provs = array();
					while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
						$provs[] = $res;
					}
					echo json_encode($provs);
				}
			}
			else if ($method == 'area') {
				$provcode = $_GET['provcode'];
				if ($provcode) {
					$area_sql =<<<EOF
					SELECT provcode, areacode, areaname, pinyin, py FROM weather_area WHERE provcode = '$provcode';
EOF;
					$ret = $db->query($area_sql);
					$areas = array();
					while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
						$areas[] = $res;
					}

					echo json_encode($areas);
				}
			}
			else if ($method == 'city') {
				$areacode = $_GET['areacode'];
				if ($areacode) {
					$city_sql =<<<EOF
						SELECT areacode,  citycode, cityname, pinyin, py FROM weather_city WHERE areacode = '$areacode';
EOF;
					$ret = $db->query($city_sql);
					if ($ret) {
						$cities = array();
						while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
							$cities[] = $res;	
						}

						echo json_encode($cities);
					}
					else {
						echo $db->lastErrorMsg();
					}
				}
				else {
					echo '{"err":"areacode=?"}';
				}
			}
			else {
				echo '{"err":"Method not implemented"}';
			}
		}
		else {
			echo '{"err":"method=?"}';
		}
		$db->close();
	}
?>
