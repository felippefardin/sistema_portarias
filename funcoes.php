<?php

function formatarDataExtenso($data){

$meses = [
"01"=>"janeiro",
"02"=>"fevereiro",
"03"=>"março",
"04"=>"abril",
"05"=>"maio",
"06"=>"junho",
"07"=>"julho",
"08"=>"agosto",
"09"=>"setembro",
"10"=>"outubro",
"11"=>"novembro",
"12"=>"dezembro"
];

$d = date("d", strtotime($data));
$m = $meses[date("m", strtotime($data))];
$a = date("Y", strtotime($data));

return "$d de $m de $a";
}
?>