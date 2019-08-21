<?php
/**
 * MiLight
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 18:04:54 [Apr 20, 2017])
 */
//
//
class milight extends module
{
    /**
     * milight
     *
     * Module class constructor
     *
     * @access private
     */
    function milight()
    {
        $this->name = "milight";
        $this->title = "MiLight";
        $this->module_category = "<#LANG_SECTION_DEVICES#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 0)
    {
        $p = array();
        if (IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->data_source)) {
            $p["data_source"] = $this->data_source;
        }
        if (IsSet($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $data_source;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($data_source)) {
            $this->data_source = $data_source;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'milight_devices' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_milight_devices') {
                $this->search_milight_devices($out);
            }
            if ($this->view_mode == 'edit_milight_devices') {
                $this->edit_milight_devices($out, $this->id);
            }
            if ($this->view_mode == 'delete_milight_devices') {
                $this->delete_milight_devices($this->id);
                $this->redirect("?data_source=milight_devices");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'milight_commands') {
            if ($this->view_mode == '' || $this->view_mode == 'search_milight_commands') {
                $this->search_milight_commands($out);
            }
            if ($this->view_mode == 'edit_milight_commands') {
                $this->edit_milight_commands($out, $this->id);
            }
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    /**
     * milight_devices search
     *
     * @access public
     */
    function search_milight_devices(&$out)
    {
        require(DIR_MODULES . $this->name . '/milight_devices_search.inc.php');
    }

    /**
     * milight_devices edit/add
     *
     * @access public
     */
    function edit_milight_devices(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/milight_devices_edit.inc.php');
    }

    /**
     * milight_devices delete record
     *
     * @access public
     */
    function delete_milight_devices($id)
    {
        $rec = SQLSelectOne("SELECT * FROM milight_devices WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM milight_commands WHERE DEVICE_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM milight_devices WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * milight_commands search
     *
     * @access public
     */
    function search_milight_commands(&$out)
    {
        require(DIR_MODULES . $this->name . '/milight_commands_search.inc.php');
    }

    /**
     * milight_commands edit/add
     *
     * @access public
     */
    function edit_milight_commands(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/milight_commands_edit.inc.php');
    }

    function propertySetHandle($object, $property, $value)
    {
        $properties = SQLSelect("SELECT milight_commands.* FROM milight_commands WHERE milight_commands.LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND milight_commands.LINKED_PROPERTY LIKE '" . DBSafe($property) . "'");
        $total = count($properties);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                $device = SQLSelectOne("SELECT * FROM milight_devices WHERE ID=" . (int)$properties[$i]['DEVICE_ID']);
                $host = $device['IP'];
                $zone = $device['ZONE'];
                $type = $device['DEVICE_TYPE']; //0 = white, 1 = rgb
                $protocol = $device['PROTOCOL'];
                $command = $properties[$i]['TITLE'];
                $properties[$i]['VALUE'] = $value;
                $properties[$i]['UPDATED'] = date('Y-m-d H:i:s');
                SQLUpdate('milight_commands', $properties[$i]);

                if ($command == 'color') {
                    $value = preg_replace('/^#/', '', $value);
                }
                if ($command == 'color' && $value == '000000') {
                    $command = 'status';
                    $value = 0;
                } elseif ($command == 'color' && $value == 'ffffff') {
                    //$command = 'status';
                    //$value = 1;
                    $command = 'white';
                }

                if ($command == 'status' && $value) {
                    $command = 'on';
                } elseif ($command == 'status' && !$value) {
                    $command = 'off';
                } elseif ($command == 'level' && $value <= 10) {
                    $command = 'off';
                } elseif ($command == 'level' && $value >= 90 && $protocol==0) {
                    $command = 'levelmax';
                } elseif ($command == 'level' && $value <= 20 && $protocol==0) {
                    $command = 'levelmin';
                } elseif ($command == 'command') {
                    $command = $value;
                }

                $timeout_title = "milight_".$protocol."_".$zone; //."_".$command
                if (timeOutExists($timeout_title)) continue; // prevent many commands at once
                setTimeOut($timeout_title,'',1);
                DebMes("Protocol $protocol zone $zone v6type: $v6type command: $command value: $value",'milight');

                if ($protocol==0) {
                    include_once(DIR_MODULES . $this->name . '/milight_lib.php');
                    $milightObject = new MilightClass($host);
                    DebMes("Protocol $protocol zone $zone command: $command value: $value",'milight');
                    //white
                    if ($type == 0) {
                        $milightObject->setWhiteActiveGroup($zone);
                        if ($command == 'leveldown') {
                            $milightObject->command('whiteBrightnessDown');
                        }
                        if ($command == 'levelup') {
                            $milightObject->command('whiteBrightnessUp');
                        }
                        if ($command == 'levelmax') {
                            $milightObject->command('whiteGroup' . $zone . 'BrightnessMax');
                        }
                        if ($command == 'levelmin') {
                            $milightObject->command('whiteGroup' . $zone . 'BrightnessMin');
                        }
                        if ($command == 'nightmode') {
                            $milightObject->command('whiteGroup' . $zone . 'NightMode');
                        }
                        if ($zone == 1) {
                            if ($command == 'on') {
                                $milightObject->whiteGroup1On();
                            }
                            if ($command == 'off') {
                                $milightObject->whiteGroup1Off();
                            }
                        }
                        if ($zone == 2) {
                            if ($command == 'on') {
                                $milightObject->whiteGroup2On();
                            }
                            if ($command == 'off') {
                                $milightObject->whiteGroup2Off();
                            }
                        }
                        if ($zone == 3) {
                            if ($command == 'on') {
                                $milightObject->whiteGroup3On();
                            }
                            if ($command == 'off') {
                                $milightObject->whiteGroup3Off();
                            }
                        }
                        if ($zone == 4) {
                            if ($command == 'on') {
                                $milightObject->whiteGroup4On();
                            }
                            if ($command == 'off') {
                                $milightObject->whiteGroup4Off();
                            }
                        }

                    }
                    //rgbw
                    if ($type == 1) {
                        if ($command == 'disco') {
                            $milightObject->setRgbwActiveGroup($zone);
                            $milightObject->rgbwSendOnToActiveGroup();
                            $milightObject->command('rgbwDiscoMode');
                        }
                        if ($command == 'discofaster') {
                            $milightObject->setRgbwActiveGroup($zone);
                            $milightObject->rgbwSendOnToActiveGroup();
                            $milightObject->command('rgbwDiscoFaster');
                        }
                        if ($command == 'discoslower') {
                            $milightObject->setRgbwActiveGroup($zone);
                            $milightObject->rgbwSendOnToActiveGroup();
                            $milightObject->command('rgbwDiscoSlower');
                        }

                        if ($command == 'level') {
                            $milightObject->setRgbwActiveGroup($zone);
                            $milightObject->rgbwBrightnessPercent($value);
                        }
                        if ($command == 'color') {
                            $milightObject->setRgbwActiveGroup($zone);
                            $milightObject->rgbwSetColorHexString($value);
                        }
                        if ($zone == 1) {
                            if ($command == 'on') {
                                $milightObject->rgbwGroup1On();
                            }
                            if ($command == 'off') {
                                $milightObject->rgbwGroup1Off();
                            }
                            if ($command == 'white') {
                                $milightObject->rgbwGroup1SetToWhite();
                            }
                        }
                        if ($zone == 2) {
                            if ($command == 'on') {
                                $milightObject->rgbwGroup2On();
                            }
                            if ($command == 'off') {
                                $milightObject->rgbwGroup2Off();
                            }
                            if ($command == 'white') {
                                $milightObject->rgbwGroup2SetToWhite();
                            }
                        }
                        if ($zone == 3) {
                            if ($command == 'on') {
                                $milightObject->rgbwGroup3On();
                            }
                            if ($command == 'off') {
                                $milightObject->rgbwGroup3Off();
                            }
                            if ($command == 'white') {
                                $milightObject->rgbwGroup3SetToWhite();
                            }
                        }
                        if ($zone == 4) {
                            if ($command == 'on') {
                                $milightObject->rgbwGroup4On();
                            }
                            if ($command == 'off') {
                                $milightObject->rgbwGroup4Off();
                            }
                            if ($command == 'white') {
                                $milightObject->rgbwGroup4SetToWhite();
                            }
                        }
                    }
                }
                if ($protocol==6) {
                    include_once(DIR_MODULES . $this->name . '/milight6_lib.php');
                    $milightObject = new Milight6Class();
                    $milightObject->IP=$host;
                    $v6type=0;
                    if ($type == 0) $v6type=0; //white
                    if ($type == 1) $v6type=0; //rgbw
                    if ($type == 2) $v6type=2; //rgbww
                    if ($type == 3) $v6type=1; //bridge


                    $result = false;
                    if ($command=='on') {
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SWITCH_ON)]);
                    }
                    if ($command=='off') {
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SWITCH_OFF)]);
                    }
                    if ($command=='level') {
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SET_BRIGHTNESS,$value)]);
                    }
                    if ($command=='color') {
                        $colorhsl=$this->hex2hsl($value);
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SET_COLOR,$colorhsl)]);
                    }
                    if ($command=='mode') {
                        if (strtolower($value) == 'white') {
                            $command = 'white';
                        }
                        if (strtolower($value) == 'disco') {
                            $command = 'disco';
                            $value = 1;
                        }
                    }
                    if ($command == 'white') {
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SWITCH_ON_WHITE,0)]);
                    }
                    if ($command=='disco') {
                        $result = $milightObject->sendCmds([$milightObject->getCmd($v6type,$zone,$milightObject::CMD_SET_DISCO_PROGRAM,$value)]);
                    }

                    /*
                    if ($result) {
                        DebMes("Success","milight");
                    } else {
                        DebMes("Fail","milight");
                    }
                    */
                }


            }
        }
    }

    function validate_hex($hex) {
        if(preg_match("/^#([0-9a-fA-F]{6})$/", $hex) || preg_match("/^#([0-9a-fA-F]{3})$/", $hex)) {
            $hex = substr($hex, 1);
        }
        if(preg_match("/^([0-9a-fA-F]{6})$/", $hex)) {
            return $hex;
        }
        if(preg_match("/^([0-9a-f]{3})$/", $hex)) {
            return substr($hex, 0, 1) . substr($hex, 0, 1) . substr($hex, 1, 1) . substr($hex, 1, 1) . substr($hex, 2, 1) . substr($hex, 2, 1);
        }
        return "000000";
    }

    function hex2hsl($hex) {
        $hex = $this->validate_hex($hex);
        $hex = str_split($hex, 2);
        $r = (hexdec($hex[0])) / 255;
        $g = (hexdec($hex[1])) / 255;
        $b = (hexdec($hex[2])) / 255;
        return $this->rgbToHsl($r,$g,$b);
    }
    function rgbToHsl( $r, $g, $b ) {
        $oldR = $r;
        $oldG = $g;
        $oldB = $b;
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max( $r, $g, $b );
        $min = min( $r, $g, $b );
        $h;
        $s;
        $l = ( $max + $min ) / 2;
        $d = $max - $min;
        if( $d == 0 ){
            $h = $s = 0; // achromatic
        } else {
            $s = $d / ( 1 - abs( 2 * $l - 1 ) );
            switch( $max ){
                case $r:
                    $h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;
                case $g:
                    $h = 60 * ( ( $b - $r ) / $d + 2 );
                    break;
                case $b:
                    $h = 60 * ( ( $r - $g ) / $d + 4 );
                    break;
            }
        }
        return round( $h, 2 );
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLExec('DROP TABLE IF EXISTS milight_devices');
        SQLExec('DROP TABLE IF EXISTS milight_commands');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data = '')
    {
        /*
        milight_devices -
        milight_commands -
        */
        $data = <<<EOD
 milight_devices: ID int(10) unsigned NOT NULL auto_increment
 milight_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 milight_devices: IP varchar(255) NOT NULL DEFAULT ''
 milight_devices: ZONE varchar(255) NOT NULL DEFAULT ''
 milight_devices: PROTOCOL int(3) NOT NULL DEFAULT '0'
 milight_devices: DEVICE_TYPE varchar(255) NOT NULL DEFAULT ''

 milight_commands: ID int(10) unsigned NOT NULL auto_increment
 milight_commands: TITLE varchar(100) NOT NULL DEFAULT ''
 milight_commands: VALUE varchar(255) NOT NULL DEFAULT ''
 milight_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 milight_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 milight_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 milight_commands: LINKED_METHOD varchar(100) NOT NULL DEFAULT '' 
 milight_commands: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDIwLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
