<?php
require_once('common/header.php');
require_once('common/navigation.php');
if($error) {
   return;
}




// Store the age group id as in the age_groups table
$ageGroupId = $_GET['id'];
var_dump(getHospitalizationForAgeGroup($ageGroupId));
