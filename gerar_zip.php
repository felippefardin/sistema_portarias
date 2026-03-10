<?php
// gerar_zip.php
require('fpdf/fpdf.php');
$db = new SQLite3('data/portarias.db');

// Funções de formatação (mesma lógica do gerar_pdf.php)
function dataExtensoZip($data){
    $meses = ["01"=>"janeiro","02"=>"fevereiro","03"=>"março","04"=>"abril","05"=>"maio","06"=>"junho","07"=>"julho","08"=>"agosto","09"=>"setembro","10"=>"outubro","11"=>"novembro","12"=>"dezembro"];
    $d = date("d",strtotime($data));
    $m = $meses[date("m",strtotime($data))];
    $a = date("Y",strtotime($data));
    return "$d de $m de $a";
}

$res = $db->query("SELECT * FROM portarias ORDER BY id DESC");

$zip = new ZipArchive();
$zipName = "Portarias_Completo_" . date('d-m-Y') . ".zip";

if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    while ($p = $res->fetchArray()) {
        // Lógica de geração do PDF (baseada no seu gerar_pdf.php)
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',10);
        $pdf->Image('img/logoserra.png', 90, 10, 30, 30);
        $pdf->Ln(35);
        $pdf->Cell(0,10,utf8_decode('PREFEITURA MUNICIPAL DA SERRA'),0,1,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,utf8_decode("PORTARIA Nº ".$p['numero']."/".$p['ano']),0,1,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',12);
        $texto = "Designar o Procurador Municipal, ".$p['procurador'].", para o processo ".$p['processo'].".";
        $pdf->MultiCell(0,8,utf8_decode($texto));
        
        // Adiciona o PDF ao ZIP
        $pdfContent = $pdf->Output('S');
        $zip->addFromString("Portaria_".$p['numero']."_".$p['ano'].".pdf", $pdfContent);
    }
    $zip->close();

    // Enviar para download e deletar arquivo temporário
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.$zipName);
    header('Content-Length: ' . filesize($zipName));
    readfile($zipName);
    unlink($zipName);
}
?>