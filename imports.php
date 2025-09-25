<?php

$currentFolder = realpath(dirname(__FILE__));
include_once("database.php");
include_once($currentFolder."/classes/Result.php");
include_once($currentFolder."/classes/Utilisateur.php");
include_once($currentFolder."/classes/Voyage.php");
include_once($currentFolder."/classes/Voiture.php");
include_once($currentFolder."/classes/Avis.php");
session_start();

?>