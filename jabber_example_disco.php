<?php
/* Copyright 2007, noalwin <lambda512 at gmail dot com> <xmpp:lambda512 at jabberes dot org>
 * Based on
 * Jabber Class Example
 * Copyright 2002-2005, Steve Blinch
 * http://code.blitzaffe.com
 * ============================================================================
 *
 * LICENSE
 *
 * class_Jabber.php - Jabber Client Library (service discovery extension)
 * Copyright 2007, noalwin
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version.
 * 
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 * JABBER is a registered trademark of Jabber Inc.
 */
 

// This class query server by server to get their services

// set your Jabber server hostname, username, and password here
define("JABBER_SERVER","example.org");
define("JABBER_USERNAME","user");
define("JABBER_PASSWORD","pass");

define("RUN_TIME",30);	// set a maximum run time of 30 seconds
define("CBK_FREQ",1);	// fire a callback event every second

// include the Jabber class
require_once("class_Jabber.php");

class service_query_bot {
	
	function service_query_bot($server) {
	
		// create an instance of the Jabber class
		$this->jab = new Jabber(true);
		$this->first_roster_update = true;
		
		// set handlers for the events we wish to be notified about
		$this->jab->set_handler("connected",$this,"handleConnected");
		$this->jab->set_handler("authenticated",$this,"handleAuthenticated");
		$this->jab->set_handler("authfailure",$this,"handleAuthFailure");
		$this->jab->set_handler("servicesdiscovered",$this,"handleServicesDiscovered");
		$this->jab->set_handler("servicesupdated",$this,"handleServiceInfo");
		$this->jab->set_handler("browseresult",$this,"handleBrowseResult");
		$this->jab->set_handler("heartbeat",$this,"handleHeartbeat");
		$this->jab->set_handler("error",$this,"handleError");
		
		$this->server=$server;
		
		/*echo "Created!\n";*/
	}
	
	function execute(){
		// connect to the Jabber server
		if (!$this->jab->connect(JABBER_SERVER)) {
			die("Could not connect to the Jabber server!\n");
		}
		
		// now, tell the Jabber class to begin its execution loop
		$this->jab->execute(CBK_FREQ,RUN_TIME);
		
		// Note that we will not reach this point (and the execute() method will not
		// return) until $this->jab->terminated is set to TRUE.  The execute() method simply
		// loops, processing data from (and to) the Jabber server, and firing events
		// (which are handled by our TestMessenger class) until we tell it to terminate.
		//
		// This event-based model will be familiar to programmers who have worked on
		// desktop applications, particularly in Win32 environments.
		
		// disconnect from the Jabber server
		$this->jab->disconnect();
		
	}
	
	// called when a connection to the Jabber server is established
	function handleConnected() {
		/*echo "Connected!\n";*/
		
		// now that we're connected, tell the Jabber class to login
		$this->jab->login(JABBER_USERNAME,JABBER_PASSWORD,JABBER_RESOURCE);
	}
	
	// called after a login to indicate the the login was successful
	function handleAuthenticated() {
		/*echo "Authenticated!\n";*/
// 		$this->discover_servers_services()
		
		//All services
// 		$this->jab->services = array();
		
		$this->jab->get_service_details($this->server);
		$this->jab->discover($this->server);

	}

	
	function handleServicesDiscovered($services_discovered,$nodes_discovered,$parent,$parentNode=NULL) {

		echo "Childs of $parent".($parentNode?"->".$parentNode:'')."\n";
		echo "	Nodes:\n";
		foreach($nodes_discovered as $node => $jid){
			echo "		$jid ($node) \n";
		}
		echo "	Services:\n";
		foreach($services_discovered as $service){
			echo "		$service\n";
		}
	}
	
	function handleServiceInfo($service,$node=NULL) {
		echo "Info from $service ".($node?"-> ".$node:'')."\n";
		
		if(isset($node)){
			$features=$this->jab->services[$service]['nodes'][$node]['features'];
			$identities=$this->jab->services[$service]['nodes'][$node]['identities'];
		}else{
			$features=$this->jab->services[$service]['features'];
			$identities=$this->jab->services[$service]['identities'];
		}
			
		foreach($features as $feature){
			echo "	Feature: $feature\n";
		}
		foreach($identities as $identity){
			echo "	Category: ".$identity['category']." type: ".$identity['type'].($identity['name']?" name: ".$identity['name']:'')."\n";
		}
		
	}
	
	
	// called after a login to indicate that the login was NOT successful
	function handleAuthFailure($code,$error) {
		//echo "Authentication failure: $error ($code)\n";
		
		// set terminated to TRUE in the Jabber class to tell it to exit
		$this->jab->terminated = true;
	}
	
	// called periodically by the Jabber class to allow us to do our own
	// processing
 	function handleHeartbeat() {
//  		//echo "Heartbeat!\n";
	}
	
	// called when an error is received from the Jabber server
	function handleError($code,$error,$xmlns,$packet) {
		$from = $packet['iq']['@']['from'];
// 		var_dump($packet);
		if(isset($packet['iq']['#']['query'][0]['@']['node'])){
			$node = $packet['iq']['#']['query'][0]['@']['node'];
		}else{
			$node = NULL;
		}
		echo "Error: $error ($code)".($xmlns?" in $xmlns":"noxmlns")." from $from $node id ".$packet['iq']['@']['id']."\n";
	}
}

$test = new service_query_bot("jabber.org");
$test->execute();
?>
