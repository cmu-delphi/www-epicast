<?php
//Content-type header
header("Content-type: text/css; charset: UTF-8");

//Fonts
$bodyFont = 'Calibri, Segoe, "Segoe UI", Optima, Arial, sans-serif';
$titleFont = $bodyFont;
$fluvFont = '"Yanone Kaffeesatz", "Rockwell Extra Bold", "Rockwell Bold", monospace';
$delphiFont = '"Alegreya SC", "Rockwell Extra Bold", "Rockwell Bold", monospace';
//Backgrounds
$backgroundColor1 = '#fff';
$backgroundColor2 = '#eee';
$backgroundColor3 = '#282828';
//$backgroundColor3_under = '#fff';
//$backgroundColor3_over = 'rgba(0, 0, 0, 0.843)';
$backgroundColor3_under = '#fff';
$backgroundColor3_over = $backgroundColor3;
$backgroundColor4 = '#888';
//Foregrounds
$foregroundColor1 = '#000';
$foregroundColor2 = '#444';
$foregroundColor3 = '#eee';
$foregroundColor4 = '#888';
//Inputs
$inputColor1 = '#ddd';
$inputColor2 = '#bbb';
//Anchors (links)
$linkColor1 = '#48c';
$linkColor2 = '#c48';
$linkDelphiColor1 = '#c44';
$linkDelphiColor2 = '#fff';
//Misc
$popColor1 = '#48c';
$fluvColor = '#36c';
$successColor1 = '#4c8';
$successColor2 = '#efe';
$failureColor1 = '#c84';
$failureColor2 = '#fee';

function css3($property, $value) {
   printf('/' . '* %s *' . "/\n", $property);
   printf("-webkit-%s: %s;\n", $property, $value);
   printf("     -o-%s: %s;\n", $property, $value);
   printf("   -moz-%s: %s;\n", $property, $value);
   printf("    -ms-%s: %s;\n", $property, $value);
   printf("        %s: %s;\n", $property, $value);
}
?>