<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
if(!isset($_SESSION["user"])){
   $_SESSION["user"] = array(); 
}
header('Content-Type: application/json');

require './php/lib/srp.php';

if(!isset($_REQUEST["phase"])){
    echo json_encode(array("success" => false, "message" => "missing parameters"));
} elseif($_REQUEST["phase"] == 0){
    //registration: receive I, s, v and save it
    $I = $_REQUEST["I"];
    $s = $_REQUEST["s"];
    $v = $_REQUEST["v"];
    
    $_SESSION["user"][$I] = array("s" => $s, "v" => $v);
    
    echo json_encode(array("success" => true));
} elseif ($_REQUEST["phase"] == 1){
    //reveive I and A, search s, v by I in DB, generate b and B, send s, B to client
    $I = $_REQUEST["I"];
    $A = $_REQUEST["A"];
    
    if(!isset($_SESSION["user"][$I])){
        echo json_encode(array("success" => false));
        exit;
    }
    
    $_SESSION["s"] = $_SESSION["user"][$I]["s"];
    $_SESSION["v"] = $_SESSION["user"][$I]["v"];
    $_SESSION["A"] = $A;
    
    $srp = new srp();
    $_SESSION["b"] = $srp->getRandomSeed();
    $_SESSION["B"] = $srp->generateB($_SESSION["b"], $_SESSION["v"]);
    
    echo json_encode(array(
        "success" => true,
        "B" => $_SESSION["B"],
        "s" => $_SESSION["s"]
        ));
    
} elseif ($_REQUEST["phase"] == 2){
    //server receive M1, verify it, build k; send M2 back
    $M1 = $_REQUEST["M1"];
    
    $srp = new srp();
    $_SESSION["S"] = $srp->generateS_Server($_SESSION["A"], $_SESSION["B"], $_SESSION["b"], $_SESSION["v"]);
    $M1_check = $srp->generateM1($_SESSION["A"], $_SESSION["B"], $_SESSION["S"]);
 
    if($M1 !== $M1_check){
        echo json_encode(array("success" => false));
        exit; 
    }
    
    $_SESSION["K"] = $srp->generateK($_SESSION["S"]);

    $M2 = $srp->generateM2($_SESSION["A"], $M1, $_SESSION["S"]);
    
    echo json_encode(array(
        "success" => true,
        "M2" => $M2
            ));
}