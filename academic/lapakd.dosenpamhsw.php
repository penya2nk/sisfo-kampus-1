<?php
error_reporting(0);
session_start();

include_once "../pengembang.lib.php";
include_once "../konfigurasi.mysql.php";
include_once "../sambungandb.php";
include_once "../setting_awal.php";
include_once "../check_setting.php";
include_once "../fpdf.php";

$TahunID = GainVariabelx('TahunID');
$ProdiID = GainVariabelx('ProdiID');

ob_start();
$pdf = new FPDF();
$pdf->SetTitle("Daftar Mahasiswa Berdasarkan Dosen PA");
$pdf->SetAutoPageBreak(true, 5);
$lbr = 190;

BuatIsinya($TahunID, $ProdiID, $pdf);

$pdf->Output();

function BuatIsinya($TahunID, $ProdiID, $p) {
  global $koneksi;
  $maxentryperpage = 45;
  
  $whr_prodi = (empty($ProdiID))? '' : "and h.ProdiID = '$ProdiID' ";
  $whr_tahun = (empty($TahunID))? '' : "and h.TahunID = '$TahunID' ";
  $s = "select h.*,
      m.Nama as NamaMhsw, m.PenasehatAkademik,
      d.Nama as NamaPA, d.Gelar
    from khs h
      left outer join mhsw m on m.MhswID = h.MhswID and m.KodeID = '".KodeID."'
      left outer join dosen d on d.Login = m.PenasehatAkademik and d.KodeID = '".KodeID."'
    where h.KodeID = '".KodeID."'
      and h.TahunID = '$TahunID'
      and h.SKS > 0
      $whr_prodi
	  $whr_tahun
    order by m.PenasehatAkademik, h.MhswID";
  $r = mysqli_query($koneksi, $s);
  
  $n = 0; $t = 5; $_pa = 'laksdjfalksdfh';
  $ttl = 0;
  while ($w = mysqli_fetch_array($r)) {
    if ($_pa != $w['PenasehatAkademik']) {
      $p->Ln($t);
	  $p->SetFont('Helvetica', 'B', 12);
	  $p->Cell(100, $t, 'Jumlah Mahasiswa: '.$ttl, 0, 0);
	  $p->SetFont('Helvetica', '', 10);
	  
	  $_pa = $w['PenasehatAkademik'];
	  $p->AddPage();
	  HeaderLogo("Daftar Mahasiswa Berdasarkan Dosen Penasehat", $p, 'P');
      BuatHeader($TahunID, $w['NamaPA'], 1, $p);
	  $ttl = 0;
    }
	
	if($ttl > 0 and $ttl%$maxentryperpage == 0)
	{  $p->AddPage();
	   HeaderLogo("Daftar Mahasiswa Berdasarkan Dosen Penasehat", $p, 'P');	
	   BuatHeader($TahunID, $w['NamaPA'], ceil($ttl/$maxentryperpage)+1, $p);
	}
	
    $n++;
    //$NamaPA = (empty($w['NamaPA']))? '(Belum diset)' : $w['NamaPA'];
    $p->SetFont('Helvetica', '', 10);
    $p->Cell(15, $t, $ttl+1, 'LB', 0); 
    $p->Cell(25, $t, $w['MhswID'], 'B', 0);
    $p->Cell(60, $t, $w['NamaMhsw'], 'B', 0);
    $p->Cell(10, $t, $w['Sesi'], 'B', 0, 'R');
    $p->Cell(10, $t, $w['SKS'], 'B', 0, 'R');
    $p->Cell(10, $t, $w['MaxSKS'], 'BR', 0, 'R');
    //$p->Cell(60, $t, $NamaPA, 'BR', 0);
    $p->Ln($t);
	$ttl++;
  }
  $p->Ln($t);
  $p->SetFont('Helvetica', 'B', 12);
  $p->Cell(100, $t, 'Jumlah Mahasiswa: '.$ttl, 0, 0);
}
function BuatHeader($TahunID, $NamaPA, $page, $p) {
  global $lbr;
  $t = 6;
  $p->SetFont('Helvetica', 'B', 14);
  $p->Cell(150, $t, "Nama Dosen Penasehat: $NamaPA", 0, 0, 'L');
  $p->Cell(40, $t, "Halaman: $page", 0, 1, 'R');
  $p->Ln($t+2);
  // Header tabel
  $p->SetFont('Helvetica', 'B', 10);
  $p->Cell(15, $t, 'Nmr', 1, 0);
  $p->Cell(25, $t, 'N I M', 1, 0);
  $p->Cell(60, $t, 'Nama Mahasiswa', 1, 0);
  $p->Cell(10, $t, 'Smtr', 1, 0);
  $p->Cell(10, $t, 'SKS', 1, 0);
  $p->Cell(10, $t, 'Max', 1, 0);
  //$p->Cell(60, $t, 'Penasehat Akd.', 1, 0);
  $p->Ln($t);
}

function HeaderLogo($jdl, $p, $orientation='P')
{	$pjg = 110;
	$logo = (file_exists("../img/logo.jpg"))? "../img/logo.jpg" : "img/logo.jpg";
    $identitas = AmbilFieldx('identitas', 'Kode', KodeID, 'Nama, Alamat1, Telepon, Fax');
	$p->Image($logo, 12, 8, 18);
	$p->SetY(5);
    $p->SetFont("Helvetica", '', 8);
    $p->Cell($pjg, 5, $identitas['Yayasan'], 0, 1, 'C');
    $p->SetFont("Helvetica", 'B', 10);
    $p->Cell($pjg, 7, $identitas['Nama'], 0, 0, 'C');
    
	//Judul
	if($orientation == 'L')
	{
		$p->SetFont("Helvetica", 'B', 16);
		$p->Cell(20, 7, '', 0, 0);
		$p->Cell($pjg, 7, $jdl, 0, 1, 'C');
	}
	else
	{	$p->SetFont("Helvetica", 'B', 12);
		$p->Cell(80, 7, $jdl, 0, 1, 'R');
	}
	
    $p->SetFont("Helvetica", 'I', 6);
	$p->Cell($pjg, 3,
      $identitas['Alamat1'], 0, 1, 'C');
    $p->Cell($pjg, 3,
      "Telp. ".$identitas['Telepon'].", Fax. ".$identitas['Fax'], 0, 1, 'C');
    $p->Ln(3);
	if($orientation == 'L') $length = 275;
	else $length = 190;
    $p->Cell($length, 0, '', 1, 1);
    $p->Ln(2);
}

?>
