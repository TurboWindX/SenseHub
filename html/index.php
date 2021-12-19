<?php
//Grab wifi credentials
$wifissid = $_GET["ssid"];
$wifikey = $_GET["key"];
$username = $_GET["uname"];
$password = $_GET["upass"];

//Location of configuration files
$netconf = '/etc/netplan/50-cloud-init.yaml';
$mosquittoconf = '/etc/mosquitto/conf.d/go-auth.conf';
$hostapdconf = '/etc/hostapd/hostapd.conf';
$dnsmasqconf = '/etc/dnsmasq.conf';

//Template  for wifi config
$wificonf = '    wifis:
        wlan0:           
            optional: true
            access-points: 
                %s:
                    password: %s
            dhcp4: true';
//format wifi credentials into template
$wificonf = sprintf($wificonf, $wifissid, $wifikey);

$mosquitto_db = 'auth_plugin /etc/mosquitto/conf.d/go-auth.so
allow_anonymous false
auth_opt_backends mysql
auth_opt_hasher bcrypt
auth_opt_hasher_cost 10
auth_opt_mysql_host localhost
auth_opt_mysql_port 3306
auth_opt_mysql_dbname mosquitto
auth_opt_mysql_user %s
auth_opt_mysql_password %s
auth_opt_mysql_connect_tries 5
auth_opt_mysql_allow_native_passwords true
auth_opt_mysql_userquery select pass FROM musers WHERE username = ? limit 1';
$mosquitto_db = sprintf($mosquitto_db, $username, $password);


if($wifissid != ""){
    try{
	//ADD WIFI CONFIG TO NETPLAN
        $fp = fopen($netconf,'a') or die("not working1");
        fwrite($fp, $wificonf);
        fclose($fp);

	//ADD USERNAME AND PASSWORD TO MOSQUITTO DB CONFIG
	$fp = fopen($mosquittoconf, 'a') or die("not working2");
	fwrite($fp, $mosquitto_db);
	fclose($fp);

	$dbnew = '/usr/bin/mysql -uroot -e "CREATE USER %s@localhost IDENTIFIED BY \'%s\';"';
        $dbnew = sprintf($dbnew, $username, $password);
        echo shell_exec($dbnew);
        
        $grantpriv = '/usr/bin/mysql -uroot -e "GRANT ALL PRIVILEGES ON mosquitto.* TO %s@localhost;"';
        $grantpriv = sprintf($grantpriv, $username);
        echo shell_exec($grantpriv);

        $dbcreds = '/usr/bin/mysql -uroot -e "ALTER USER root@localhost IDENTIFIED BY \'%s\'";';
        $dbcreds = sprintf($dbcreds, $password);
        echo shell_exec($dbcreds);

        $newroot = 'sudo echo -e "%s\n%s" | passwd root';
        $newroot = sprintf($newroot, $password, $password);
        echo shell_exec($newroot);
        shell_exec("sudo /usr/sbin/netplan apply");
        shell_exec("sudo /usr/sbin/shutdown -r now");
    
    }catch(Exception $e){
        echo $e;
    }
}
?>
