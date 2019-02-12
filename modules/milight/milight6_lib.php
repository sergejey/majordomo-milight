<?php

class Milight6Class {
   const TYPE_RGBW   = 0;
   const TYPE_BRIDGE = 1;
   const TYPE_RGBWW  = 2;
   const CMD_SWITCH_ON  = 0;
   const CMD_SWITCH_OFF = 1;
   const CMD_SET_COLOR  = 2;
   const CMD_SET_SATURATION = 3;
   const CMD_SET_BRIGHTNESS = 4;
   const CMD_SET_TEMPERATURE = 5;
   const CMD_SWITCH_ON_WHITE = 6;
   const CMD_SWITCH_ON_NIGHT = 7;
   const CMD_SET_DISCO_PROGRAM = 8;
   const CMD_INC_DISCO_SPEED = 9;
   const CMD_DEC_DISCO_SPEED = 10;
   const CMD_SET_LINK_MODE = 11;
   const CMD_SET_UNLINK_MODE = 12;

   public $IP;
   public $MACAdr = "";
   public $SessionID1 = -1;
   public $SessionID2 = -1;
   private $SequenceNbr = 1;
   private $sendRetries = 1;
   private $receiveRetries = 1;

   private static $CMD_PreAmble = array(0x80, 0x00, 0x00, 0x00, 0x11);
   private static $CMD_GetSessionID = array( 0x20, 0x00, 0x00, 0x00, 0x16, 0x02, 0x62, 0x3A, 0xD5, 0xED, 0xA3, 0x01, 0xAE, 0x08, 0x2D, 0x46, 0x61, 0x41, 0xA7, 0xF6, 0xDC, 0xAF, 0xD3, 0xE6, 0x00, 0x00, 0x1E );

   private static $CMDS = array(
      self::TYPE_RGBW => array(
         self::CMD_SWITCH_ON => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x01, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_OFF => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x02, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SET_COLOR => array( 0x31, 0x00, 0x00, 0x07, 0x01, 0xBA, 0xBA, 0xBA, 0xBA, 0x00), // 9th=zone
         self::CMD_SET_BRIGHTNESS => array( 0x31, 0x00, 0x00, 0x07, 0x02, 0xBE, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_ON_WHITE => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x05, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_ON_NIGHT => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x06, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SET_DISCO_PROGRAM=>array( 0x31, 0x00, 0x00, 0x07, 0x04, 0x01, 0x00, 0x00, 0x00, 0x00), // 9th=zone 6th hex values 0x01 to 0x09
         self::CMD_INC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x03, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_DEC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x07, 0x03, 0x04, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SET_LINK_MODE => array( 0x3D, 0x00, 0x00, 0x07, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SET_UNLINK_MODE => array( 0x3E, 0x00, 0x00, 0x07, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00)  // 9th=zone
         ),
      self::TYPE_BRIDGE => array(
         self::CMD_SWITCH_ON => array( 0x31, 0x00, 0x00, 0x00, 0x03, 0x03, 0x00, 0x00, 0x00, 0x01 ),
         self::CMD_SWITCH_OFF => array( 0x31, 0x00, 0x00, 0x00, 0x03, 0x04, 0x00, 0x00, 0x00, 0x01 ),
         self::CMD_SET_COLOR => array( 0x31, 0x00, 0x00, 0x00, 0x01, 0xBA, 0xBA, 0xBA, 0xBA, 0x01 ),
         self::CMD_SET_BRIGHTNESS => array( 0x31, 0x00, 0x00, 0x00, 0x02, 0xBE, 0x00, 0x00, 0x00, 0x01 ),
         self::CMD_SWITCH_ON_WHITE => array( 0x31, 0x00, 0x00, 0x00, 0x03, 0x05, 0x00, 0x00, 0x00, 0x01 ),
         self::CMD_SET_DISCO_PROGRAM => array( 0x31, 0x00, 0x00, 0x00, 0x04, 0x01, 0x00, 0x00, 0x00, 0x01 ), // 6th hex values 0x01 to 0x09
         self::CMD_INC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x00, 0x03, 0x02, 0x00, 0x00, 0x00, 0x01 ),
         self::CMD_DEC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x00, 0x03, 0x01, 0x00, 0x00, 0x00, 0x01 )
	 ),
      self::TYPE_RGBWW => array(
         self::CMD_SWITCH_ON => array( 0x31, 0x00, 0x00, 0x08, 0x04, 0x01, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_OFF => array( 0x31, 0x00, 0x00, 0x08, 0x04, 0x02, 0x00, 0x00, 0x00, 0x00), // 9th=zone
                            // 31 00 00 08 01 BA BA BA BA = Set Color to Blue (0xBA) (0xFF = Red, D9 = Lavender, BA = Blue, 85 = Aqua, 7A = Green, 54 = Lime, 3B = Yellow, 1E = Orange)
         self::CMD_SET_COLOR => array( 0x31, 0x00, 0x00, 0x08, 0x01, 0xBA, 0xBA, 0xBA, 0xBA, 0x00), // 9th=zone
                            // 31 00 00 08 02 SS 00 00 00 = Saturation (SS hex values 0x00 to 0x64 : examples: 00 = 0%, 19 = 25%, 32 = 50%, 4B, = 75%, 64 = 100%)
         self::CMD_SET_SATURATION => array( 0x31, 0x00, 0x00, 0x08, 0x02, 0xBE, 0x00, 0x00, 0x00, 0x00), // 9th=zone
                            // 31 00 00 08 03 BN 00 00 00 = BrightNess (BN hex values 0x00 to 0x64 : examples: 00 = 0%, 19 = 25%, 32 = 50%, 4B, = 75%, 64 = 100%)
         self::CMD_SET_BRIGHTNESS => array( 0x31, 0x00, 0x00, 0x08, 0x03, 0xBE, 0x00, 0x00, 0x00, 0x00), // 9th=zone
                            // 31 00 00 08 05 KV 00 00 00 = Kelvin (KV hex values 0x00 to 0x64 : examples: 00 = 2700K (Warm White), 19 = 3650K, 32 = 4600K, 4B, = 5550K, 64 = 6500K (Cool White))
         self::CMD_SET_TEMPERATURE => array( 0x31, 0x00, 0x00, 0x08, 0x05, 0xBE, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_ON_WHITE => array( 0x31, 0x00, 0x00, 0x08, 0x05, 0x64, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_SWITCH_ON_NIGHT => array( 0x31, 0x00, 0x00, 0x08, 0x04, 0x05, 0x00, 0x00, 0x00, 0x00), // 9th=zone
                            // 31 00 00 08 06 MO 00 00 00 = Mode Number MO hex values 0x01 to 0x09
         self::CMD_SET_DISCO_PROGRAM =>array( 0x31, 0x00, 0x00, 0x08, 0x06, 0x01, 0x00, 0x00, 0x00, 0x00), // 9th=zone 6th hex values 0x01 to 0x09
         self::CMD_INC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x08, 0x04, 0x03, 0x00, 0x00, 0x00, 0x00), // 9th=zone
         self::CMD_DEC_DISCO_SPEED => array( 0x31, 0x00, 0x00, 0x08, 0x04, 0x04, 0x00, 0x00, 0x00, 0x00), // 9th=zone
                            // 3D 00 00 08 00 00 00 00 00 = Link (Sync Bulb within 3 seconds of lightbulb socket power on)
         self::CMD_SET_LINK_MODE => array( 0x3D, 0x00, 0x00, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00), // 9th=zone
	                        // 3E 00 00 08 00 00 00 00 00 = UnLink (Clear Bulb within 3 seconds of lightbulb socket power on)
         self::CMD_SET_UNLINK_MODE => array( 0x3E, 0x00, 0x00, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00)  // 9th=zone
	     )
	  );

function sendCmds($cmds) {
      //global $SequenceNbr,$SessionID1,$SessionID2,$IP;
      $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
      socket_connect($socket,$this->IP,"5987");
      $this->getSessionID($socket);
      // alle cmds nacheinander senden
      foreach ($cmds as $key => $cmd) if ($cmd<>[]) {
//$this->Log( $key.": ".chunk_split( bin2hex( vsprintf(str_repeat('%c', count($cmd)), $cmd)), 2, ' '));
//break;

         $bytes = array(0x80, 0x00, 0x00, 0x00, 0x11);
         $bytes[] = $this->SessionID1;
         $bytes[] = $this->SessionID2;
         $bytes[] = 0x00;
         $bytes[] = $this->SequenceNbr;
         $bytes[] = 0x00;
         $bytes = array_merge($bytes, $cmd);
         $bytes[] = 0x00;
         $checksum = 0;
         for ($i=10; $i<=10+10; $i++) $checksum = $checksum + $bytes[$i];
         $bytes[] = $checksum & 0xFF;

         $sentBytes = $this->sendByteArray($socket, $bytes);
		   if ($sentBytes==0) {
	         $msg = chunk_split(bin2hex( vsprintf(str_repeat('%c', count($bytes)), $bytes), 2, ' '));
            socket_close($socket);
            return false;
         }
         $this->SequenceNbr = ($this->SequenceNbr + 1) & 0xFF;
      }
     socket_close($socket);
     return true;
   }


function sendByteArray($socket, array $bytes) {
      $buf = vsprintf(str_repeat('%c', count($bytes)), $bytes);
      return $this->sendString($socket, $buf);
   }

function getSessionID($socket) {
      //global $MACAdr, $SessionID1, $SessionID2;
      $sentBytes = $this->sendByteArray($socket, array( 0x20, 0x00, 0x00, 0x00, 0x16, 0x02, 0x62, 0x3A, 0xD5, 0xED, 0xA3, 0x01, 0xAE, 0x08, 0x2D, 0x46, 0x61, 0x41, 0xA7, 0xF6, 0xDC, 0xAF, 0xD3, 0xE6, 0x00, 0x00, 0x1E ) );
		if ($sentBytes!=0) {
         $receive = $this->receiveString($socket);
         if (strlen($receive) > 20) {
            $this->MACAdr = substr(chunk_split(bin2hex( substr($receive,8,6) ),2,":"),0,-1);
            $this->SessionID1 = ord($receive[19]);
            $this->SessionID2 = ord($receive[20]);
            //$this->Log("MAC= $this->MACAdr ID1=$this->SessionID1");
            return true;
         }
      }
      return false;
   }

function sendString($socket, $buf) {
      //global $sendRetries;
      $sendRetry=1;
	   $sentBytes=0;
//$this->Log(chunk_split(bin2hex( $buf ),2," "));

      while ( ($sendRetry <= $this->sendRetries) and ($sentBytes==0) ) {
         //$this->Log("Sendeversuch: .$sendRetry / $this->sendRetries");
         $sentBytes = @socket_send($socket, $buf, strlen($buf), 0);
         $sendRetry++;
      }
   return $sentBytes;
   }

function receiveString($socket) {
      //global $receiveRetries;
      $res = "";

      $receiveRetry = 1;
      while ($receiveRetry <= $this->receiveRetries) {
         //$this->Log("Empfangsversuch: $receiveRetry / $this->receiveRetries");
         $receiveBytes = @socket_recv($socket, $buf, 128, 0); // MSG_DONTWAIT = 0x40
         if ($receiveBytes > 0 ) {
            $res = $buf;
            break;
         } else {
            $receiveRetry++;
         }
   }
//$this->Log(chunk_split(bin2hex( $res ),2," "));
   return $res;
 }

function getCmd($type=0, $zone=0, $cmd=1, $value=0 ) {
	  //global $CMDS;
      $result=[];
      $result=self::$CMDS[$type][$cmd];

      $patch=[];
      switch ($cmd) {
         case self::CMD_SET_COLOR:
            $value = intval( min( 360, max( 0, $value) ) / 360 * 255);
            $patch = array(5=>$value, 6=>$value, 7=>$value, 8=>$value );
            break;
         case self::CMD_SET_BRIGHTNESS:
            $value = min( 100, max( 0, $value) );
            $patch = array(5=>$value );
            break;
         case self::CMD_SET_SATURATION:
            $value = 100 - min( 100, max( 0, $value) );
            $patch = array(5=>$value );
            break;
         case self::CMD_SET_TEMPERATURE:
            $value = intval( ( min( 100, max( 0, $value) ) ) / 100 * 0x64);
            $patch = array(5=>$value );
            break;
         case self::CMD_SET_DISCO_PROGRAM:
            $value = intval( min( 9, max( 0, $value) ) );
            $patch = array(5=>$value );
            break;
      }
      foreach ($patch as $key => $value) {
         $result[$key]=$value;
      }

      // patch zone (nicht bei BRIDGE)
      if ( ($type!=self::TYPE_BRIDGE) and (count($result)>9) ) {
         $result[9] = $zone;
      }

      return $result;
   }

}

