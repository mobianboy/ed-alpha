<?php

// Make sure default timezone is set
date_default_timezone_set('UTC');


/**
 * Format any provided date string into relative difference date (e.g. 5 mins ago)
 *
 * @author SDK (steve@eardish.com)
 * @date 2013-01-21
 * @param str $date - The date to be formatted
 * @return str - The formatted relative date string
 */
function relative_date($date) {

  // If not provided necessary args, throw an error
  try {
    if(!isset($date)) {
      throw new Exception('Need to provide date');
    }
  } catch(Exception $e) {
    return $e->getMessage();
  }

  // Compare current time to provided date string
  $diff = time() - strtotime($date);

  // Setup milestone calcs
  $second = 1;
  $minute = 60;
  $hour   = 60 * 60;
  $day    = 60 * 60 * 24;
  $week   = 60 * 60 * 24 * 7;
  $month  = 60 * 60 * 24 * 30;
  $year   = 60 * 60 * 24 * 365;

  // Default format to true for results
  $format = TRUE;

  /*** TIERS ***/
  // Now or future
  if($diff <= 0) {
    $format = FALSE; // No formatting needed for this case
    $res = 'Now';

  // Seconds
  } elseif($diff > $second && $diff < $minute) {
    $format = FALSE; // No formatting needed for this case
    $res = '1 min ago';

  // Minutes
  } elseif($diff >= $minute && $diff < $hour) {
    $res = round($diff / $minute).' min';

  // Hours
  } elseif($diff >= $hour && $diff < $day) {
    $res = round($diff / $hour).' hour';

  // Days
  } elseif($diff >= $day && $diff < $week) {
    $res = round($diff / $day).' day';

  // Weeks
  } elseif($diff >= $week && $diff < $month) {
    $res = round($diff / $week).' week';

  // Months
  } elseif($diff >= $month && $diff < $year) {
    $res = round($diff / $month).' month';

  // Years
  } elseif($diff >= $year) {
    $res = round($diff / $year).' year';

  // Default
  } else {
    $format = FALSE; // No formatting needed for this case
    $res = 'Now';
  }
  /*** end TIERS ***/

  // Process res formatting
  if($format) {
    $res = (substr($res, 0, 2) != '1 ') ? $res.'s' : $res;
    $res .= ' ago';
  }

  // Return result
  return $res;
} // end function relative_date

