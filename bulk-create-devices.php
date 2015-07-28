#!/usr/bin/php
# Author: Per Nesager Toft (pto@telenor.dk)
# Descr: Script will iterate through devices.txt (list of hostnames) and create devices based on the $hosttemplate id
# Date: 2015-07-28

<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
        die("<br><strong>This script is only meant to run at the command line.</strong>");
}

/* We are not talking to the browser */
$no_http_headers = true;



$community = "public";
#Show the host template using add_device
$hosttemplate = 17; // Alcatel ISAM
$hostlist = 'devices.txt';
$localdomain = "telenor.dk";

#
$snmpqueryid=48;
$snmpquerytype=19;

$handle = fopen($hostlist, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $line = chop($line);
        print "[".$line."] \n";
        createHost($line);
    }
} else { die("Could not open file $filename!"); }
die;


function createHost($dev)
{
global $community;
global $hosttemplate;
print "================== Creating DSLAM for $dev =======================\n";

$ret = cmd("/usr/bin/php add_device.php --quiet --description='".$dev."' --ip='$dev.$localdomain --template=$hosttemplate --community='".$community."' --avail=snmp");
        //Get host id from: [RET] Success - new device-id: (20)
                if (preg_match("/\((\d+)\)/", $ret, $matches))
                {
                        $deviceID =  $matches[1];
                        print "Device ID: $deviceID \n";
                        //We got a device - create graphs for device
                        $ret = cmd("/usr/bin/php add_graphs.php --graph-type=ds --graph-template-id='31' --host-id=".$deviceID." --snmp-query-id=$snmpqueryid --snmp-query-type-id=$snmpquerytype --snmp-field=ifDescr --snmp-value='Ethernet
 Interface'");
                 //RET: Graph Added - graph-id: (34) - data-source-ids: (37, 37)
                        if (preg_match("/\((\d+)\)/", $ret, $matches)) {
                         $graphID =  $matches[1];
                        //We got a graph - add it to default tree
#                       cmd("/usr/bin/php /cacti/appl/cacti/cli/add_tree.php  --type=node  --node-type=graph --tree-id=1 --graph-id=".$graphID);
                        }
                }

}
function cmd($cmd)
{
print "[CMD] $cmd\n";
$ret =  exec($cmd)."\n";
print "[RET] $ret\n";
return $ret;
}
?>
