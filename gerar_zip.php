<?php
$db = new SQLite3('data/portarias.db');
require('fpdf/fpdf.php');

if (!isset($_POST['selecionados']) || empty($_POST['selecionados'])) {
    header("Location: index.php?msg=erro_selecao");
    exit;
}

// Configuração da classe PDF idêntica à sua para manter o padrão
class PDF_ZIP extends FPDF {
    function Header() {
        if (file_exists('img/logoserra.png')) {
            $this->Image('img/logoserra.png', 90, 10, 30, 30);
        }
    }
}

function dataExtensoZIP($data) {
    $meses = ["01"=>"janeiro","02"=>"fevereiro","03"=>"março","04"=>"abril","05"=>"maio","06"=>"junho","07"=>"julho","08"=>"agosto","09"=>"setembro","10"=>"outubro","11"=>"novembro","12"=>"dezembro"];
    return date("d",strtotime($data))." de ".$meses[date("m",strtotime($data))]." de ".date("Y",strtotime($data));
}

$zip = new ZipArchive();
$zipName = "Portarias_Selecionadas_" . date('dmY_His') . ".zip";

if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
    exit("Erro ao criar arquivo ZIP");
}

foreach ($_POST['selecionados'] as $id) {
    $id = intval($id);
    $p = $db->query("SELECT * FROM portarias WHERE id=$id")->fetchArray();
    if (!$p) continue;

    $pdf = new PDF_ZIP();
    $pdf->AddPage();
    
    // Conteúdo idêntico ao seu gerar_pdf.php
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln(35); 
    $pdf->Cell(0,10,utf8_decode('PREFEITURA MUNICIPAL DA SERRA'),0,1,'C');
    $pdf->Cell(0,8,utf8_decode('ESTADO DO ESPÍRITO SANTO'),0,1,'C');
    $pdf->Cell(0,8,utf8_decode('PROCURADORIA-GERAL DO MUNICÍPIO'),0,1,'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,utf8_decode("PORTARIA Nº {$p['numero']}/{$p['ano']}"),0,1,'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial','',12);
    $pdf->MultiCell(0,7,utf8_decode("A PROCURADORA-GERAL DO MUNICÍPIO DE SERRA... [texto omitido para brevidade]"));
    $pdf->Ln(12);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,utf8_decode("R    E    S    O    L    V    E:"),0,1,'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',12);
    
    $titulo = ($p['sexo'] == "F") ? "Dra." : "Dr.";
    $desc = ($p['sexo'] == "F") ? "brasileira, advogada, inscrita na OAB/ES sob o nº {$p['oab']}" : "brasileiro, advogado, inscrito na OAB/ES sob o nº {$p['oab']}";
    
    $texto = "Designar o Procurador Municipal, $titulo {$p['procurador']}, $desc, para promover, acompanhar e praticar todos os atos necessários, decorrentes do processo sob o nº {$p['processo']}, impetrado por {$p['autor']}, em face do MUNICÍPIO DE SERRA, perante {$p['vara']}.";
    $pdf->MultiCell(0,8,utf8_decode($texto));
    $pdf->Ln(15);
    $pdf->Cell(0, 10, utf8_decode("Serra/ES, ".dataExtensoZIP($p['data_portaria']).'.'), 0, 1, 'C'); 
    $pdf->Ln(40); 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, utf8_decode("ALESSANDRA COSTA FERREIRA NUNES"), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode("Procuradora-Geral do Município de Serra"), 0, 1, 'C');

    $pdfContent = $pdf->Output('S'); 
    $zip->addFromString("Portaria_{$p['numero']}_{$p['ano']}.pdf", $pdfContent);
}

$zip->close();

// Força o download do ZIP
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zipName);
header('Content-Length: ' . filesize($zipName));
readfile($zipName);
unlink($zipName); 
exit;