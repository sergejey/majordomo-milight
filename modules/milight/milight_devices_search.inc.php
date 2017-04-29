<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  if (!$qry) $qry="1";

  $sortby_milight_devices="IP, ZONE, DEVICE_TYPE";
  $out['SORTBY']=$sortby_milight_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM milight_devices WHERE $qry ORDER BY ".$sortby_milight_devices);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
