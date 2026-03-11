<?php

error_reporting(0);
ini_set('display_errors', 0);

require('fpdf/fpdf.php');

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
$processo = $p['processo'];
$autor = $p['autor'];
$vara = $p['vara'];
$data = dataExtenso($p['data_portaria']);

if($sexo=="F"){
    $titulo="Dra.";
    $descricao="brasileira, advogada, inscrita na OAB/ES sob o nº $oab";
}else{
    $titulo="Dr.";
    $descricao="brasileiro, advogado, inscrito na OAB/ES sob o nº $oab";
}

class PDF extends FPDF{
    function Header(){
        $largura = 30;
        $altura = 30;
        $x = (210 - $largura)/2;
        $y = 10;      
        if(file_exists('img/logoserra.png')){
            $this->Image('img/logoserra.png', $x, $y, $largura, $altura);
        }
    }
}

ob_end_clean();

$pdf = new PDF();
$pdf->AddPage();

// Cabeçalho do PDF
$pdf->SetFont('Arial','B',10);
$pdf->Ln(35); 
$pdf->Cell(0,10,utf8_decode('PREFEITURA MUNICIPAL DA SERRA'),0,1,'C');
$pdf->Cell(0,8,utf8_decode('ESTADO DO ESPÍRITO SANTO'),0,1,'C');
$pdf->Cell(0,8,utf8_decode('PROCURADORIA-GERAL DO MUNICÍPIO'),0,1,'C');

$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode("PORTARIA Nº $numero/$ano"),0,1,'C');

$pdf->Ln(4);

$pdf->SetFont('Arial','',12);
$intro="A PROCURADORA-GERAL DO MUNICÍPIO DE SERRA, nomeada por força do Decreto nº. 027, de 02 de janeiro 2025, no uso de suas atribuições legais,";
$pdf->MultiCell(0,7,utf8_decode($intro));

$pdf->Ln(12);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode("R    E    S    O    L    V    E:"),0,1,'C');

$pdf->Ln(10);

$pdf->SetFont('Arial','',12);
$texto="Designar o Procurador Municipal, $titulo $procurador, $descricao, para promover, acompanhar e praticar todos os atos necessários, decorrentes do processo sob o nº $processo, impetrado por $autor, em face do MUNICÍPIO DE SERRA, perante $vara.";
$pdf->MultiCell(0,8,utf8_decode($texto));

$pdf->Ln(15);
$pdf->Cell(0, 10, utf8_decode("Serra/ES, $data."), 0, 1, 'C'); 

$pdf->Ln(40); 

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode("ALESSANDRA COSTA FERREIRA NUNES"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, utf8_decode("Procuradora-Geral do Município de Serra"), 0, 1, 'C');

// Lógica de Saída
if(isset($_GET['download']) && $_GET['download'] == '1'){
    $nomeArquivo = 'Portaria_'.str_replace('/', '_', $numero).'_'.$ano.'.pdf';
    $pdf->Output('D', $nomeArquivo); 
} else {
    $pdf->Output('I'); 
}
?>