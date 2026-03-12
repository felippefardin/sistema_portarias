<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

$db = new SQLite3('data/portarias.db');

function dataExtenso($data){
    $meses = [
        "01"=>"janeiro","02"=>"fevereiro","03"=>"março","04"=>"abril",
        "05"=>"maio","06"=>"junho","07"=>"julho","08"=>"agosto",
        "09"=>"setembro","10"=>"outubro","11"=>"novembro","12"=>"dezembro"
    ];

    $d = date("d",strtotime($data));
    $m = $meses[date("m",strtotime($data))];
    $a = date("Y",strtotime($data));

    return "$d de $m de $a";
}

if(!isset($_GET['id'])){
    die("ID da portaria não fornecido.");
}

$id = intval($_GET['id']);
$res = $db->query("SELECT * FROM portarias WHERE id=$id");
$p = $res->fetchArray();

if(!$p){
    die("Portaria não encontrada.");
}

$numero = $p['numero'];
$ano = $p['ano'];
$procurador = $p['procurador'];
$sexo = $p['sexo'];
$oab = $p['oab'];
$matricula = $p['matricula'] ?? 'N/A';
$processo = $p['processo'];
$autor = $p['autor'];
$vara = $p['vara'];
$data = dataExtenso($p['data_portaria']);

if($sexo=="F"){
    $titulo="Dra.";
    $descricao="brasileira, advogada, inscrita na OAB/ES sob o nº $oab, matrícula nº $matricula";
}else{
    $titulo="Dr.";
    $descricao="brasileiro, advogado, inscrito na OAB/ES sob o nº $oab, matrícula nº $matricula";
}

$phpWord = new PhpWord();

// Configuração de Margens (2,5 cm nas laterais)
$sectionStyle = [
    'marginLeft'   => Converter::cmToTwip(2.5),
    'marginRight'  => Converter::cmToTwip(2.5),
    'marginTop'    => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
];
$section = $phpWord->addSection($sectionStyle);

// Cabeçalho com Logo
if(file_exists('img/logoserra.png')){
    $section->addImage('img/logoserra.png', [
        'width' => 80,
        'height' => 80,
        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
    ]);
}

$section->addTextBreak(1);

// Cabeçalho de Texto
$fontStyleBold = ['name' => 'Arial', 'size' => 10, 'bold' => true];
$paragraphStyleCenter = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0];

$section->addText('PREFEITURA MUNICIPAL DA SERRA', $fontStyleBold, $paragraphStyleCenter);
$section->addText('ESTADO DO ESPÍRITO SANTO', $fontStyleBold, $paragraphStyleCenter);
$section->addText('PROCURADORIA-GERAL DO MUNICÍPIO', $fontStyleBold, $paragraphStyleCenter);

$section->addTextBreak(1);

$section->addText("PORTARIA Nº $numero/$ano", ['name' => 'Arial', 'size' => 12, 'bold' => true], $paragraphStyleCenter);

$section->addTextBreak(1);

// Introdução
$fontStyleNormal = ['name' => 'Arial', 'size' => 12];
$paragraphStyleJustify = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'lineSpacing' => 1.5];

$section->addText("A PROCURADORA-GERAL DO MUNICÍPIO DE SERRA, nomeada por força do Decreto nº. 027, de 02 de janeiro 2025, no uso de suas atribuições legais,", $fontStyleNormal, $paragraphStyleJustify);

$section->addTextBreak(1);
$section->addText("R    E    S    O    L    V    E:", ['name' => 'Arial', 'size' => 12, 'bold' => true], $paragraphStyleCenter);
$section->addTextBreak(1);

// Texto Principal com Negritos Específicos
$textRun = $section->addTextRun($paragraphStyleJustify);
$textRun->addText("Designar o Procurador Municipal, ", $fontStyleNormal);
$textRun->addText("$titulo $procurador", ['name' => 'Arial', 'size' => 12, 'bold' => true]);
$textRun->addText(", $descricao, para promover, acompanhar e praticar todos os atos necessários, decorrentes do processo sob o nº $processo, impetrado por ", $fontStyleNormal);
$textRun->addText($autor, ['name' => 'Arial', 'size' => 12, 'bold' => true]);
$textRun->addText(", em face do ", $fontStyleNormal);
$textRun->addText("MUNICÍPIO DE SERRA", ['name' => 'Arial', 'size' => 12, 'bold' => true]);
$textRun->addText(", perante $vara.", $fontStyleNormal);

$section->addTextBreak(2);
$section->addText("Serra/ES, $data.", $fontStyleNormal, $paragraphStyleCenter);

$section->addTextBreak(3);

// Assinatura
$section->addText("ALESSANDRA COSTA FERREIRA NUNES", $fontStyleBold, $paragraphStyleCenter);
$section->addText("Procuradora-Geral do Município de Serra", $fontStyleNormal, $paragraphStyleCenter);

// Saída do Arquivo
$fileName = 'Portaria_'.str_replace('/', '_', $numero).'_'.$ano.'.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="'.$fileName.'"');
header('Cache-Control: max-age=0');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
exit;