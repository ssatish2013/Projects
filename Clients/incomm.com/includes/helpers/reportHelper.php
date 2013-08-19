<?php

//*** NOTE *** 
//All functions that are called in the xslt need to be
//defined as public static since we have no class instance
//to callthem from

class reportHelper{
  public static function getWeekId($timestamp) {
    $year = date('Y', strtotime($timestamp));
    $week = date('W', strtotime($timestamp));
    $day = date('N', strtotime($timestamp));

    //standard date time functions week's go from Monday - Sunday.  We want Sunday through Saturday.
    if($day == 7) { $week += 1; }
    if($week > 52) { $week = 1; $year +=1;}
    if($week < 10) { $week = "0".intval($week); }

    //towards the end of the year we can end up with some overlap
    //i.e. 2011W52 instead of 2010W52 for Jan 1st.
    if(strtotime($timestamp) < strtotime($year.'W'.$week)) {
      $year -= 1;
    }
    return $year.'W'.$week;

  }
  public static function getMonthId($timestamp) {
    return date('Y\Mm', strtotime($timestamp));
  }


}
