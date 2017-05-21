<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require './php/lib/srp.php';

$srp = new srp();
$server_vars = array();
$client_vars = array();

//1. generate s, v (clinent generated, stored by server)
$client_vars["username"] = "falk";
$client_vars["password"] = "test123";

$s = $srp->getRandomSeed();
$x = $srp->generateX($s, $client_vars["username"], $client_vars["password"]);
$client_vars["x"] = $x;
$server_vars["s"]  = $s;
$server_vars["v"]  = $srp->generateV($x);

//2.1 client generate a, A and send A, I (username) to server
$client_vars["a"] = $srp->getRandomSeed();
$client_vars["A"] = $srp->generateA($client_vars["a"]);

//2.2 server reveive A, search s, v by I in DB, generate b and B, send s, B to client
$server_vars["A"] = $client_vars["A"];
$server_vars["b"] = $srp->getRandomSeed();
$server_vars["B"] = $srp->generateB($server_vars["b"], $server_vars["v"]);

//3.1 client receive s, B; build M1 and send it to server
$client_vars["B"] = $server_vars["B"];
$client_vars["S1"] = $srp->generateS_Client($client_vars["A"], $client_vars["B"], $client_vars["a"], $x);
$client_vars["M1"]  = $srp->generateM1($client_vars["A"], $client_vars["B"], $client_vars["S1"]);


//3.2 server receive M1, verify it, build k; send M2 back
$server_vars["M1_recive"] = $client_vars["M1"];
$server_vars["S2"] = $srp->generateS_Server($server_vars["A"], $server_vars["B"], $server_vars["b"], $server_vars["v"]);
$M1_check = $srp->generateM1($server_vars["A"], $server_vars["B"], $server_vars["S2"]);

if($server_vars["M1_recive"]== $M1_check){
    echo "Client verifikation complete. ".$srp->generateK($server_vars["S2"]);
    echo "<br/>";
}

$server_vars["M2"] = $srp->generateM2($server_vars["A"], $M1_check, $server_vars["S2"]);


//4. client verify M2, build k
$M2_check = $srp->generateM2($client_vars["A"], $client_vars["M1"], $client_vars["S1"]);

if($M2_check == $server_vars["M2"]){
    echo "Server verification complete. ".$srp->generateK($client_vars["S1"]);
}


?> 


