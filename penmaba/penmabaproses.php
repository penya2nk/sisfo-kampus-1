<?php
function PMBStat() {
  global $arrID;
  $s = "select p.ProdiID, p.Nama 
    from prodi p 
    left outer join fakultas f on p.FakultasID=f.FakultasID
    where p.NA='N' and f.KodeID='$arrID[Kode]' 
    order by p.ProdiID";
  $r = mysqli_query($koneksi, $s);
  $_arr = array();
  // Ambil data & masukkan array
  while ($w = mysqli_fetch_array($r)) {
    $trm = AmbilOneField('pmb', "PMBPeriodID='$_SESSION[pmbperiod]' and LulusUjian='Y' and ProdiID", $w['ProdiID'], "count(PMBID)");
    $ggl = AmbilOneField('pmb', "PMBPeriodID='$_SESSION[pmbperiod]' and LulusUjian='N' and ProdiID", $w['ProdiID'], "count(PMBID)");
    $prc = AmbilOneField('pmb', "PMBPeriodID='$_SESSION[pmbperiod]' and MhswID<>'' and ProdiID", $w['ProdiID'], "count(PMBID)");
    $tot = $trm+$ggl;
    //echo "$w[ProdiID] - $w[Nama] : $trm+$ggl=$tot<br>";
    $_arr[] = "$w[ProdiID],$w[Nama],$trm,$ggl,$tot,$prc";
  }
  // Tampilkan
  $ttrm = 0;
  $tggl = 0;
  $ttot = 0;
  $c = 'class=ul';
  echo "<p><div class='card'>
  <div class='card-header'>
  <div class='table-responsive'>
  <table class=box cellspacing=1 cellpadding=4>
    <tr><td $c colspan=7><strong>$arrID[Nama]</strong></td></tr>
    <tr><td $c colspan=7>Periode PMB: <b>$_SESSION[pmbperiod]</b></td></tr>
    <tr><th class=ttl>Kode</th><th class=ttl>Program Studi</th>
    <th class=ttl>Lulus</th><th class=ttl>Gagal</th><th class=ttl>Total</th>
    <th class=ttl>Diproses</th>
    </tr>";
  for ($i=0; $i<sizeof($_arr); $i++) {
    $_dat = explode(",", $_arr[$i]);
    $ttrm += $_dat[2];
    $tggl += $_dat[3];
    $ttot += $_dat[4];
    //<td $c><input type=button name='token' value='Proses' onClick=\"location='?ndelox=pmbproses&token=PMBPRC&prid=$_dat[0]'\"></td>
    echo "<tr><td $c align=center>$_dat[0]</td>
    <td $c>$_dat[1]</td>
    <td $c align=right>$_dat[2]</td>
    <td $c align=right>$_dat[3]</td>
    <td $c align=right>$_dat[4]</td>
    <td $c align=right>$_dat[5]</td>
    
    </tr>";
  }
  echo "<tr><td colspan=2 align=right><b>Total:</b>
    <td class=box align=right>$ttrm</td>
    <td class=box align=right>$tggl</td>
    <td class=box align=right>$ttot</td>
    </tr>
  </table></div>
  </div>
  </div></p>";
}
function PMBPRC() {
  global $tokendef;
  $tokendef();
}

// *** Parameters ***
$pmbperiod = GainVariabelx("pmbperiod");
if (empty($pmbperiod)) {
  $pmbperiod = AmbilOneField("pmbperiod", "NA", 'N', "PMBPeriodID");
  $_SESSION['pmbperiod'] = $pmbperiod;
}
$tokendef = 'PMBStat';
$token = (empty($_REQUEST['token']))? $tokendef : $_REQUEST['token'];

// *** Main ***
TitleApps("Monitor Penerimaan Mahasiswa Baru");
$token();
?>
