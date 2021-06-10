<?php
/*
RCON remote console class, modified for minecraft compability by Tehbeard.

!!!YOU MUST CONFIGURE RCON ON YOUR MINECRAFT SERVER FOR THIS TO WORK
AT TIME OF WRITING ONLY 1.9pr4+ HAVE BUILTIN RCON SUPPORT!!!

Example Code:
============
include_once("rcon.class.php"); //Include this file
$r = new rcon("127.0.0.1",25575,"foobar"); //create rcon object for server on the rcon port with a specific password
if($r->Auth()){ //Connect and attempt to authenticate
{
  $r->rconCommand("say Saving in 10 seconds!"); //send a command
  sleep(10);
  $r->rconCommand("save-all"); //send a command
  $r->rconCommand("say Save complete!");//send a command
  echo $r->rconCommand("list");//send a command, echo returned value
}
============



Based upon the following work:
[<<<
	Basic CS:S Rcon class by Freman.  (V1.00)
	----------------------------------------------
	Ok, it's a completely working class now with with multi-packet responses

	Contact: printf("%s%s%s%s%s%s%s%s%s%d%s%s%s","rc","on",chr(46),"cl","ass",chr(64),"pri","ya",chr(46),2,"y",chr(46),"net")

	Behaviour I've noticed:
		rcon is not returning the packet id.
>>>]
*/

define("SERVERDATA_EXECCOMMAND",2);
define("SERVERDATA_AUTH",3);

class RCon {
	var $Password;
	var $Host;
	var $Port = 27015;
	var $_Sock = null;
	var $_Id = 0;

	function RCon ($Host,$Port,$Password) {
		$this->Password = $Password;
		$this->Host = $Host;
		$this->Port = $Port;
		$this->_Sock = @fsockopen($this->Host,$this->Port, $errno, $errstr, 30) or
		die("Unable to open socket: $errstr ($errno)\n");
		$this->_Set_Timeout($this->_Sock,2,500);
	}

	function Auth () {
		$PackID = $this->_Write(SERVERDATA_AUTH,$this->Password);

		// Real response (id: -1 = failure)
		$ret = $this->_PacketRead();
		//var_dump($ret);
		if ($ret[0]['ID'] == -1) {
			return false;
		}
		return true;
	}

	function _Set_Timeout(&$res,$s,$m=0) {
		if (version_compare(phpversion(),'4.3.0','<')) {
			return socket_set_timeout($res,$s,$m);
		}
		return stream_set_timeout($res,$s,$m);
	}

	function _Write($cmd, $s1='', $s2='') {
		// Get and increment the packet id
		$id = ++$this->_Id;

		// Put our packet together
		$data = pack("VV",$id,$cmd).$s1.chr(0).$s2.chr(0);

		// Prefix the packet size
		$data = pack("V",strlen($data)).$data;

		// Send packet
		fwrite($this->_Sock,$data,strlen($data));

		// In case we want it later we'll return the packet id
		return $id;
	}

	function _PacketRead() {
		//Declare the return array
		$retarray = array();
		//Fetch the packet size
		while ($size = @fread($this->_Sock,4)) {
			$size = unpack('V1Size',$size);
			//Work around valve breaking the protocol
			if ($size["Size"] > 4096) {
				//pad with 8 nulls
				$packet = "\x00\x00\x00\x00\x00\x00\x00\x00".fread($this->_Sock,4096);
			} else {
				//Read the packet back
				$packet = fread($this->_Sock,$size["Size"]);
			}
			array_push($retarray,unpack("V1ID/V1Response/a*S1/a*S2",$packet));
		}
		return $retarray;
	}

	function Read() {
		$Packets = $this->_PacketRead();

		foreach($Packets as $pack) {
			if (isset($ret[$pack['ID']])) {
				$ret[$pack['ID']]['S1'] .= $pack['S1'];
				$ret[$pack['ID']]['S2'] .= $pack['S1'];
			} else {
				$ret[$pack['ID']] = array(
					'Response' => $pack['Response'],
					'S1' => $pack['S1'],
					'S2' =>	$pack['S2'],
				);
			}
		}
		return $ret;
	}

	function sendCommand($Command) {
		//$Command = '"'.trim(str_replace(' ','" "', $Command)).'"';
		//$Command="stop";
		$this->_Write(SERVERDATA_EXECCOMMAND,$Command,'');
	}

	function rconCommand($Command) {
		$this->sendcommand($Command);

		$ret = $this->Read();

		//ATM: Source servers don't return the request id, but if they fix this the code below should read as
		// return $ret[$this->_Id]['S1'];
		return $ret[$this->_Id]['S1'];
	}
}
?>