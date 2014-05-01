<?php


/**
 * @desc Location class to handle zips, lat+lon and distance calculations with radius
 * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
 * @date 2013-06-17
 */
class location {

  // Default locations for filter dropdown
  public static $locations = array(
	  'Albany'	          => 12207,
		'Albuquerque'	      => 87102,
		'Alexandria'	      => 22314,
		'Anchorage'	        => 99501,
		'Athens'	          => 30601,
		'Atlanta'	          => 30303,
		'Atlantic City'	    => 08401,
		'Austin'	          => 78701,
		'Baltimore'	        => 21202,
		'Baton Rouge'	      => 70802,
		'Birmingham'	      => 35203,
		'Bismarck'	        => 58501,
		'Boise'	            => 83702,
		'Boston'	          => 02109,
		'Buffalo'	          => 14202,
		'Charleston'	      => 25303,
		'Charleston'	      => 29401,
		'Charlotte'	        => 28202,
		'Cheyenne'	        => 82001,
		'Chicago'	          => 60602,
		'Cincinnati'        => 45202,
		'Cleveland'         => 44114,
		'Colorado Springs'  => 81073,
		'Columbia'	        => 29201,
		'Columbus'	        => 43215,
		'Dallas'	          => 75201,
		'Denver'	          => 80204,
		'Des Moines'	      => 50309,
		'Detroit'	          => 48226,
		'Dover'							=> 19904,
		'Flagstaff'					=> 86001,
		'Hartford'					=> 06103,
		'Helena'						=> 59601,
		'Hilo'							=> 96720,
		'Honolulu'					=> 96813,
		'Houston'						=> 77002,
		'Indianapolis'			=> 46204,
		'Jackson'						=> 39202,
		'Jacksonville'			=> 32202,
		'Juneau'						=> 99801,
		'Kansas City'				=> 64172,
		'Lansing'						=> 48933,
		'Las Vegas'					=> 89101,
		'Lincoln'						=> 68508,
		'Little Rock'				=> 72201,
		'Los Angeles'				=> 90012,
		'Louisville'				=> 40214,
		'Madison'						=> 53703,
		'Manchester'				=> 03101,
		'Memphis'						=> 38103,
		'Miami'							=> 33128,
		'Milwaukee'					=> 53202,
		'Minneapolis'				=> 55415,
		'Mobile'						=> 36602,
		'Montgomery'				=> 36104,
		'Montpelier'				=> 05602,
		'Nashville'				  => 37201,
		'Newark'				    => 07102,
		'New Orleans'				=> 70112,
		'NY City'				    => 10007,
		'Oakland'				    => 94612,
		'Oklahoma City'			=> 73102,
		'Omaha'				      => 68102,
		'Orlando'				    => 32801,
		'Philadelphia'			=> 19107,
		'Phoenix'				    => 85003,
		'Pierre'				    => 57501,
		'Pittsburgh'				=> 15219,
		'Portland'				  => 04101,
		'Portland'				  => 97201,
		'Providence'				=> 02903,
		'Raleigh'				    => 27603,
		'Richmond'				  => 23219,
		'Sacramento'				=> 95814,
		'Salt Lake City'		=> 84111,
		'San Antonio'				=> 78205,
		'San Diego'				  => 92101,
		'San Francisco'			=> 94102,
		'San Jose'				  => 95113,
		'Santa Fe'				  => 87501,
		'Seattle'				    => 98104,
		'Sioux Falls'				=> 57104,
		'St. Louis'				  => 63103,
		'St. Paul'				  => 55102,
		'Tampa'				      => 33602,
		'Topeka'				    => 66603,
		'Trenton'				    => 08611,
		'Tulsa'				      => 74103,
		'Washington DC'			=> 20002,
		'Wichita'				    => 67202,
  );


  /**
   * $desc Calculate bounding box with radius based distances for lat and lon
   * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
   * @date 2013-06-17
   * @param float $lat - The latitude coordinate 
   * @param float $lon - The longitude coordinate 
   * @param int $dist - The radius distance in miles
   * @return arr - The bounding box for search radius
   */
  public static function latlonBoundBox($lat, $lon, $dist) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($lat) || !isset($lon) || !isset($dist)) {
        throw new Exception('Need to provide lat, lon and dist');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Calculate lat and lon distances
    $lad = ($dist * 180) / (3959 * pi());
    $lod = abs(($dist * 180) / (3959 * cos($lat) * pi()));

    // Setup directional borders of bounded box
    $res = array(
      'maxlat'  => $lat + $lad,
      'minlat'  => $lat - $lad,
      'maxlon'  => $lon + $lod,
      'minlon'  => $lon - $lod,
    );

    // Return results
    return $res;
  } // end function latlonBoundBox


  /**
   * @desc Convert zip code into latitude/longitude coords
   * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
   * @date 2013-06-17
   * @param int $zip - The zipcode to query
   * @return arr - The lat/lon coords
   */
  public static function zipLoc($zip) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($zip)) {
        throw new Exception('Need to provide zip');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query for zip_loc row
    $sql = "SELECT *
            FROM zip_loc
            WHERE zip = %d";
    $row = $wpdb->get_row($wpdb->prepare($sql, $zip));

    // If data exists in DB
    if(count($row)) {
    
      // If matching zip_loc
      $res = array(
        'lat' => $row->lat,
        'lon' => $row->lon,
      );

    // If no data in DB, query google maps API
    } else {
    
      // CURL google maps API
      $url = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=$zip";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $data = curl_exec($ch);
      curl_close($ch);
      $info = json_decode($data);

      // If API data came back, process it
      if(count($info)) {

        // Pull out lat/lon data
        $lat = $info->results[0]->geometry->location->lat;
        $lon = $info->results[0]->geometry->location->lng;
        $res = array(
          'lat' => $lat,
          'lon' => $lon,
        );

        // Insert API data into DB for next query attempt
        $sql = "REPLACE INTO zip_loc SET
                zip = %d,
                lat = %s,
                lon = %s";
        $wpdb->query($wpdb->prepare($sql, $zip, $lat, $lon));

      // If no data came back, error handling
      } else {
        $res = array();
      }
    }

    // Return results
    return $res;
  } // end function zipLoc


  /**
   * @desc Get list of zips within radius of source zip
   * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
   * @date 2013-07-15
   * @param int $zip - The zipcode to query
   * @return arr - The lat/lon coords
   */
  public static function getZipList($zip, $rad) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($zip) || !isset($rad)) {
        throw new Exception('Need to provide zip and rad');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Convert zip to lat/lon
    $zipLoc = location::zipLoc($zip);

    // Calculate bounding box
    $bb = location::latlonBoundBox($zipLoc['lat'], $zipLoc['lon'], $rad);

    // Find other zips within bounding box
    $sql = "SELECT zip
            FROM zip_loc
            WHERE (lat > %d AND lat < %d)
            AND (lon > %d AND lon < %d)
            ORDER BY zip ASC";
    $res = $wpdb->get_col($wpdb->prepare($sql, $bb['minlat'], $bb['maxlat'], $bb['minlon'], $bb['maxlon']));

    // Return results
    return $res;
  } // end function getZipList


} // end class location

