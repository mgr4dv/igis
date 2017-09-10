<?
$ldapServerAddress = "ldap://ldap.virginia.edu";
$ldapServerPort = 389;
$uid = $_POST['uid'];

$ldap = ldap_connect($ldapServerAddress,$ldapServerPort); //connect to the UVa's LDAP server for the people search
$bindresult=ldap_bind($ldap); //anonymously bind to the server

if($bindresult) {
	echo "Successfully bound to the LDAP server.";
	
	//search base: o=University of Virginia,c=US
	
} else {
	
	echo "Unable to bind to the LDAP server ($ldapServerAddress; ldap=$ldap).\n\nError:".ldap_error($ldap);
}



?>