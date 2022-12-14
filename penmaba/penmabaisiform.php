<?php
error_reporting(0);
session_start();

if ($_SESSION['_LevelID'] == 1) {
  $PMBID = GainVariabelx('_pmbLoginPMBID');
}
elseif ($_SESSION['_LevelID'] == 33) {
  $PMBID = $_SESSION['_Login'];
}
else die(PesanError('Error',
  "Anda tidak berhak menjalankan modul ini."));

CekBolehAksesModul();
$oke = BolehAksesData($_SESSION['_Login']);
  
  if ($oke) {
    $gel = AmbilOneField('pmbperiod', "KodeID='".KodeID."' and NA", 'N', "PMBPeriodID");
	
	$lungo = sqling($_REQUEST['lungo']);
    if (empty($lungo)) {
      Main($gel, $PMBID);
    }
    else $lungo($gel, $PMBID);
  }
  
// *** Functions ***

function Main($gel, $pmbid)
{	$ButuhGanti = AmbilOneField('pmb', 'PMBID', $pmbid, 'PasswordBaru');
	if($ButuhGanti == 'Y') CekGantiPassword($gel, $pmbid);	
	else Edit(0, $gel, $pmbid);
}

function CekBolehAksesModul() {
  $arrAkses = array(1, 33);
  $key = array_search($_SESSION['_LevelID'], $arrAkses);
  if ($key === false)
    die(PesanError('Error',
      "Anda tidak berhak mengakses modul ini.<br />
      Hubungi Staf PMB untuk informasi lebih lanjut."));
}

function CekGantiPassword($gel, $pmbid)
{	echo Info("Password Baru", 
		"Anda harus mensetup password baru terlebih dahulu.<br>
		<br>
		<a href='#' onClick=\"location='?ndelox=pmb/pmbpassword'\">Setup Password Baru</a>");
}	

function BolehAksesData($pmbid) {
  if ($_SESSION['_LevelID'] == 33 && $_SESSION['_Login'] != $pmbid) {
    echo PesanError('Peringatan',
      "Anda tidak boleh melihat data PMB calon mahasiswa lain.<br />
      Anda hanya boleh mengakses data dari PMBID <b>$pmbid</b>.<br />
      Hubungi Staf PMB untuk informasi lebih lanjut");
    return false;
  } 
  else if(AmbilOneField('pmb', "PMBID='$pmbid' and KodeID", KodeID, 'LulusUjian') == 'Y') {
    echo PesanError('Gagal Akses',
      "Anda tidak dapat mengakses data dari PMBID: <b>$pmbid</b> lagi.<br />
	  Anda telah didaftarkan menjadi mahasiswa. <br />
      Hubungi Staf PMB untuk informasi lebih lanjut");
    return false;
  }
  else return true;
}

function GetOptionsFromData($sourceArray, $chosen)
	{	
			$optresult = "";
			if($chosen == '' or empty($chosen))	
			{ 	$optresult .= "<option value='' selected></option>"; }
			else { $optresult .= "<option value=''></option>"; }
			for($i=0; $i < count($sourceArray); $i++)
			{	if($chosen == $sourceArray[$i])
				{	$optresult .= "<option value='$sourceArray[$i]' selected>$sourceArray[$i]</option>"; }
				else
				{ 	$optresult .= "<option value='$sourceArray[$i]'>$sourceArray[$i]</option>"; }
			}
			return $optresult;
	}
	
function GetPilihan2($w, $da2, $da3) {
  $a = '';
  for ($i = 1; $i <= 3; $i++) {
    $opt = AmbilCombo2('prodi', "concat(ProdiID, ' - ', Nama)", 'ProdiID', $w['Pilihan'.$i], "KodeID='".KodeID."'", 'ProdiID');
    $da = ''; 
	$da = ($i == 2)? $da2 : $da;
	$da = ($i == 3)? $da3 : $da;
	$a .= "<tr>
      <td class=ul1>Pilihan $i:</td>
      <td class=ul1 colspan=3>
      <select name='Pilihan$i' $da>$opt</select>
      </td>
      </tr>";
  }
  return $a;
}

function CekSudahProsesNIM($pmb) {
  if (!empty($pmb['MhswID'])) {
    die(PesanError('Error',
      "Anda sudah tidak dapat mengubah data ini karena Cama sudah diproses menjadi mahasiswa.<br />
      Nomer Induk Mahasiswa-nya adalah: <b>$pmb[MhswID]</b>.<br />
      Hubungi Sysadmin atau Bagian Administrasi Akademik untuk informasi lebih lanjut.
      <hr size=1 color=silver />
      <input type=button name='Tutup' value='Tutup Jendela Ini' onClick=\"window.close()\" />"));
  }
}

function Edit($md, $gel, $id) {
	global $koneksi;
  $sisfo = AmbilFieldx('identitas', 'Kode', KodeID, '*');
  if ($md == 0) {
    $jdl = 'Edit Data PMB';
    $w = AmbilFieldx('pmb', 'PMBID', $id, '*');
    CekSudahProsesNIM($w);
    $JumlahPilihan = AmbilOneField('pmbformulir', "PMBFormulirID = '$w[PMBFormulirID]' and KodeID", KodeID, 'JumlahPilihan');
	if(empty($JumlahPilihan)) $JumlahPilihan = 3;
	if($JumlahPilihan == 1) { $da2 = "disabled"; $da3 = "disabled"; }
	else if($JumlahPilihan == 2) { $da2 = ''; $da3 = "disabled"; }
	else { $da2 = ''; $da3 = ''; }
	$_PMBID = "<b>$w[PMBID]</b>";
  }
  elseif ($md == 1) {
    $jdl = 'Masukkan Data PMB';
    $w = array();
    $w['TanggalLahir'] = date('Y-m-d');
    $_PMBID = "<font color=red>Auto-generated</font>";
	$da2 = ''; $da3 = '';
  }
  else die(PesanError('Error',
    "Mode edit <b>$md</b> tidak dikenali.<br />
    Hubungi Sysadmin untuk informasi lebih lanjut.
    <hr size=1 color=silver />
    <input type=button name='Tutup' value='Tutup' onClick=\"window.close()\" />"));
  // Parameters
  $TanggalLahir = AmbilComboTgl($w['TanggalLahir'], 'TanggalLahir');
  $optfrm = AmbilCombo2('pmbformulir', "concat(Nama, ' (', JumlahPilihan, ' pilihan)')", 'Nama', $w['PMBFormulirID'], "KodeID='".KodeID."'", 'PMBFormulirID');
  $optstawal = AmbilCombo2('statusawal', "concat(StatusAwalID, ' - ', Nama)",
    'StatusAwalID', $w['StatusAwalID'], '', 'StatusAwalID');
  $optkel = AmbilCombo2('kelamin', "concat(Kelamin, ' - ', Nama)", 'Kelamin', $w['Kelamin'], '', 'Kelamin');
  $optagm = AmbilCombo2('agama', "concat(Agama, ' - ', Nama)", 'Agama', $w['Agama'], '', 'Agama');
  $optagamaayah = AmbilCombo2('agama', "concat(Agama, ' - ', Nama)", 'Agama', $w['AgamaAyah'], '', 'Agama');
  $optagamaibu = AmbilCombo2('agama', "concat(Agama, ' - ', Nama)", 'Agama', $w['AgamaIbu'], '', 'Agama');
  $optsipil = AmbilCombo2('statussipil', "concat(StatusSipil, ' - ', Nama)", 'StatusSipil', $w['StatusSipil'], '', 'StatusSipil');
  $optpendidikan = AmbilCombo2('pendidikanterakhir', "PendidikanTerakhir", 'Urutan', $w['PendidikanTerakhir'], '', 'PendidikanTerakhir');
  $optpendidikanayah = AmbilCombo2('jenjang', "concat(JenjangID, '. ', Nama)", 'JenjangID', $w['PendidikanAyah'], '', 'JenjangID');
  $optpendidikanibu = AmbilCombo2('jenjang', "concat(JenjangID, '. ', Nama)", 'JenjangID', $w['PendidikanIbu'], '', 'JenjangID');
  $opthidupayah = AmbilCombo2('hidup', 'Nama', 'Hidup', $w['HidupAyah'], '', 'Hidup');
  $opthidupibu  = AmbilCombo2('hidup', 'Nama', 'Hidup', $w['HidupIbu'], '', 'Hidup');
  $optprg = AmbilCombo2('program', "concat(ProgramID, ' - ', Nama)", 'ProgramID', $w['ProgramID'], "KodeID='".KodeID."'", 'ProgramID');
  $optwarganegara = AmbilCombo2('warganegara', "WargaNegara", 'WargaNegara', $w['WargaNegara'], '', 'WargaNegara');
  $Pilihan2 = GetPilihan2($w, $da2, $da3);
  $arraytinggal = array('Orang Tua', 'Wali', 'Sendiri');
  $opttinggaldengan = GetOptionsFromData($arraytinggal, $w['TinggalDengan']);
  $optpenghasilanayah = AmbilCombo2('penghasilanortu', "Nama", 'PenghasilanOrtuID', $w['PenghasilanAyah'], '', 'Nama');
  $optpenghasilanibu = AmbilCombo2('penghasilanortu', "Nama", 'PenghasilanOrtuID', $w['PenghasilanIbu'], '', 'Nama');
  $arrPrestasiTambahan = explode('~', $w['PrestasiTambahan']);
  $s1 = "select PMBFormulirID, JumlahPilihan from pmbformulir where KodeID='".KodeID."' and NA='N'";
  $r1 = mysqli_query($koneksi, $s1);
  while($w1 = mysqli_fetch_array($r1))
  {	$hiddenformdata .= "<input type=hidden id='Form$w1[PMBFormulirID]' name='Form$w1[PMBFormulirID]' value='$w1[JumlahPilihan]' />";
  }
  // Tampilkan
  TitleApps($jdl);
  CheckFormScript("Nama,TempatLahir,Kelamin,ProgramID,PMBFormulirID");
  JumlahPilihanScript();
  echo "<div class='card'>
  <div class='card-header'>
  <div class='table-responsive'><table id='example' class='table table-sm table-striped' style='width:70%' align='center'>
  <form name='frmisi' action='?ndelox=$_SESSION[ndelox]' method=POST onSubmit=\"return CheckForm(this)\" />
  <input type=hidden name='gel' value='$gel' />
  <input type=hidden name='md' value='$md' />
  <input type=hidden name='lungo' value='Simpan' />
  $hiddenformdata
  
  <tr>
      <td class=ul1>No. Peserta:</td>
      <td class=ul1>$_PMBID</td>
	  <td class=ul1>Status:</td>
	  <td class=ul1><select name='StatusAwalID' disabled>$optstawal</select></td>
  </tr>
  
  <tr style='background:purple;color:white'><th class=ttl colspan=4>Data Pribadi Cama</th></tr>
  <tr><td class=ul1>Nama Lengkap:</td>
      <td class=ul1 colspan=3>
      <input type=text name='Nama' value='$w[Nama]' size=30 maxlength=50 />
      </td></tr>
  <tr><td class=ul1>Warga Negara:</td>
      <td class=ul1><select name='WargaNegara'>$optwarganegara</select></td>
      <td class=ul1>Kebangsaan:</td>
      <td class=ul1><input type=text name='Kebangsaan' value='$w[Kebangsaan]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Tanggal Lahir:</td>
      <td class=ul1>$TanggalLahir</td>
	  <td class=ul1>Tempat Lahir:</td>
      <td class=ul1><input type=text name='TempatLahir' value='$w[TempatLahir]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Jenis Kelamin:</td>
      <td class=ul1><select name='Kelamin'>$optkel</select></td>
      <td class=ul1>Golongan Darah:</td>
      <td class=ul1><input style='text-transform: uppercase' type=text name='GolonganDarah' value='$w[GolonganDarah]' size=5 maxlength=10 /></td>
      </tr>
  <tr><td class=ul1>Agama:</td>
      <td class=ul1><select name='Agama'>$optagm</select></td>
      <td class=ul1>Status Perkawinan:</td>
      <td class=ul1><select name='StatusSipil'>$optsipil</select></td>
      </tr>
  <tr><td class=ul1>Tinggi Badan:</td>
      <td class=ul1><input type=text name='TinggiBadan' value='$w[TinggiBadan]' size=3 maxlength=5 /> cm</td>
      <td class=ul1>Berat Badan:</td>
      <td class=ul1><input type=text name='BeratBadan' value='$w[BeratBadan]' size=3 maxlength=5 /> kg</td>
      </tr>
	  
  <tr style='background:purple;color:white'><th class=ttl colspan=4>Pilihan Program Studi</th></tr>
  <tr>
      <td class=ul1>Formulir:</td>
      <td class=ul1 colspan=3>
        <select name='PMBFormulirID' onChange=\"UbahJumlahPilihan()\">$optfrm</select > <br />
        </td>
      </tr>
  <tr><td class=ul1>Program Pendidikan:</td>
      <td class=ul1 colspan=3>
      <select name='ProgramID'>$optprg</select>
      </td></tr>    
  $Pilihan2
  <tr style='background:purple;color:white'><th class=ttl colspan=4>Pendidikan Terakhir dan Prestasi Lainnya:</th></tr>
  <tr><td class=ul1>Pendidikan Terakhir:</td>
      <td class=ul1><select name='PendidikanTerakhir'>$optpendidikan</select></td>
      <td class=ul1>Asal Sekolah:</td>
      <td class=ul1><input type=text name='AsalSekolah' value='$w[AsalSekolah]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Tahun Lulus:</td>
      <td class=ul1><input type=text name='TahunLulus' value='$w[TahunLulus]' size=10 maxlength=10 /></td>
      <td class=ul1>Nilai Ijazah:</td>
      <td class=ul1><input type=text name='NilaiSekolah' value='$w[NilaiSekolah]' size=10 maxlength=10 />
					<br><font color=red>*) Masukkan dalam rentang 0.00-10.00</font></td>
      </tr>
  <tr><td class=ul1>Prestasi Lainnya:</td>
	  <td class=ul1 colspan=3><input type=text name='PrestasiTambahan1' value='$arrPrestasiTambahan[0]' size=80 maxlength=100 ></td>
  </tr>
  <tr><td></td>
	  <td class=ul1 colspan=3><input type=text name='PrestasiTambahan2' value='$arrPrestasiTambahan[1]' size=80 maxlength=100></td>
  </tr>
  <tr><td></td>
      <td class=ul1 colspan=3><input type=text name='PrestasiTambahan3' value='$arrPrestasiTambahan[2]' size=80 maxlength=100></br>
	  *) <i>Prestasi dapat berupa prestasi apa saja di bidang <b>seni</b>, <b>olahraga</b>, atau <b>akademik</b>. </i></br>
	  *) <i>Harap menuliskan <b>peringkat</b>, <b>nama lengkap kompetisi/event</b> dan <b>tahun</b> peraihan prestasi bila memungkinkan<i> 
	  </td>
  </tr>
  
  <tr style='background:purple;color:white'><th class=ttl colspan=4>Alamat</th></tr>
  <tr><td class=ul1>Alamat lengkap:</td>
      <td class=ul1 colspan=3>
      <textarea name='Alamat' cols=70 rows=4>$w[Alamat]</textarea>
      </td></tr>
  <tr><td class=ul1>RT/RW:</td>
      <td class=ul1><input type=text name='RT' value='$w[RT]' size=10 maxlength=10 />
      / <input type=text name='RW' value='$w[RW]' size=10 maxlength=10 /></td>
      <td class=ul1>Kode Pos:</td>
      <td class=ul1><input type=text name='KodePos' value='$w[KodePos]' size=10 maxlength=10 /></td>
      </tr>
  <tr><td class=ul1>Kota/Kabupaten:</td>
      <td class=ul1><input type=text name='Kota' value='$w[Kota]' size=20 maxlength=50 /></td>
      <td class=ul1>Propinsi:</td>
      <td class=ul1><input type=text name='Propinsi' value='$w[Propinsi]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Telpon/ponsel:</td>
      <td class=ul1>
        <input type=text name='Telepon' value='$w[Telepon]' size=10 maxlength=50 /> /
        <input type=text name='Handphone' value='$w[Handphone]' size=10 maxlength=50 />
      </td>
      <td class=ul1>E-mail:</td>
      <td class=ul1>
        <input type=text name='Email' value='$w[Email]' size=20 maxlength=50 />
      </td></tr>
  
  <tr style='background:purple;color:white'><th class=ttl colspan=4>Data Orangtua</th></tr>
  <tr><td class=ul1>Nama Ayah:</td>
      <td class=ul1><input type=text name='NamaAyah' value='$w[NamaAyah]' size=20 maxlength=50 /></td>
      <td class=ul1>Nama Ibu:</td>
      <td class=ul1><input type=text name='NamaIbu' value='$w[NamaIbu]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Agama Ayah:</td>
      <td class=ul1><select name='AgamaAyah' />$optagamaayah</td>
      <td class=ul1>Agama Ibu:</td>
      <td class=ul1><select name='AgamaIbu' />$optagamaibu</td>
      </tr>
  <tr><td class=ul1>Pendidikan Ayah:</td>
      <td class=ul1><select name='PendidikanAyah'>$optpendidikanayah</select></td>
      <td class=ul1>Pendidikan Ibu:</td>
      <td class=ul1><select name='PendidikanIbu'>$optpendidikanibu</select></td>
      </tr>
  <tr><td class=ul1>Pekerjaan Ayah:</td>
      <td class=ul1><input type=text name='PekerjaanAyah' value='$w[PekerjaanAyah]' size=20 maxlength=50 /></td>
      <td class=ul1>Pekerjaan Ibu:</td>
      <td class=ul1><input type=text name='PekerjaanIbu' value='$w[PekerjaanIbu]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Penghasilan/bulan:</td>
      <td class=ul1><select name='PenghasilanAyah'>$optpenghasilanayah</select></td>
      <td class=ul1>Penghasilan/bulan:</td>
      <td class=ul1><select name='PenghasilanIbu'>$optpenghasilanibu</select></td>
      </tr>
  <tr><td class=ul1>Status Kehidupan:</td>
      <td class=ul1><select name='HidupAyah'>$opthidupayah</select></td>
      <td class=ul1>Status kehidupan:</td>
      <td class=ul1><select name='HidupIbu'>$opthidupibu</select></td>
      </tr>
  
  <tr><th class=ttl colspan=4>Alamat Orangtua</th></tr>
  <tr><td class=ul1>Alamat lengkap:</td>
      <td class=ul1 colspan=3>
      <textarea name='AlamatOrtu' cols=70 rows=4>$w[AlamatOrtu]</textarea>
      </td></tr>
  <tr><td class=ul1>RT/RW:</td>
      <td class=ul1><input type=text name='RTOrtu' value='$w[RTOrtu]' size=10 maxlength=10 />
      / <input type=text name='RWOrtu' value='$w[RWOrtu]' size=10 maxlength=10 /></td>
      <td class=ul1>Kode Pos:</td>
      <td class=ul1><input type=text name='KodePosOrtu' value='$w[KodePosOrtu]' size=10 maxlength=10 /></td>
      </tr>
  <tr><td class=ul1>Kota:</td>
      <td class=ul1><input type=text name='KotaOrtu' value='$w[KotaOrtu]' size=20 maxlength=50 /></td>
      <td class=ul1>Propinsi:</td>
      <td class=ul1><input type=text name='PropinsiOrtu' value='$w[PropinsiOrtu]' size=20 maxlength=50 /></td>
      </tr>
  <tr><td class=ul1>Telpon/ponsel:</td>
      <td class=ul1>
        <input type=text name='TeleponOrtu' value='$w[TeleponOrtu]' size=10 maxlength=50 /> /
        <input type=text name='HandphoneOrtu' value='$w[HandphoneOrtu]' size=10 maxlength=50 />
      </td>
      <td class=ul1>E-mail:</td>
      <td class=ul1>
        <input type=text name='EmailOrtu' value='$w[EmailOrtu]' size=20 maxlength=50 />
      </td></tr>
  
  <tr><td class=ul1 colspan=4 align=center>
      <input type=submit name='Simpan' value='Simpan' />
      <input type=button name='Batal' value='Batal' onClick=\"window.close()\" />
      </td></tr>
  
  </form>
  </table></div>
  </div>
  </div>";
}

function Simpan($gel, $id) {
	global $koneksi;
  include_once "statusaplikan.lib.php";

  $Nama = sqling($_REQUEST['Nama']);
  $TempatLahir = sqling($_REQUEST['TempatLahir']);
  $Kelamin = sqling($_REQUEST['Kelamin']);
  $TanggalLahir = "$_REQUEST[TanggalLahir_y]-$_REQUEST[TanggalLahir_m]-$_REQUEST[TanggalLahir_d]";
  $GolonganDarah = sqling($_REQUEST['GolonganDarah']);
  $Agama = sqling($_REQUEST['Agama']);
  $StatusSipil = sqling($_REQUEST['StatusSipil']);
  $TinggiBadan = sqling($_REQUEST['TinggiBadan']);
  $BeratBadan = sqling($_REQUEST['BeratBadan']);
  $WargaNegara = sqling($_REQUEST['WargaNegara']);
  $Kebangsaan = sqling($_REQUEST['Kebangsaan']);
  $Alamat = sqling($_REQUEST['Alamat']);
  $RT = sqling($_REQUEST['RT']);
  $RW = sqling($_REQUEST['RW']);
  $KodePos = sqling($_REQUEST['KodePos']);
  $Kota = sqling($_REQUEST['Kota']);
  $Propinsi = sqling($_REQUEST['Propinsi']);
  $Telepon = sqling($_REQUEST['Telepon']);
  $Handphone = sqling($_REQUEST['Handphone']);
  $Email = sqling($_REQUEST['Email']);
  $TinggalDengan = $_REQUEST['TinggalDengan'];
  $JarakRumah = $_REQUEST['JarakRumah'];
  $PendidikanTerakhir = sqling($_REQUEST['PendidikanTerakhir']);
  $AsalSekolah = sqling($_REQUEST['AsalSekolah']);
  $TahunLulus = sqling($_REQUEST['TahunLulus']);
  $NilaiSekolah = sqling($_REQUEST['NilaiSekolah']);
  $arrPT = array();
  for($i = 1; $i <= 3; $i++)
  {	$arrPT[] = str_replace('~', '-', sqling($_REQUEST['PrestasiTambahan'.$i]));
  }
  foreach($arrPT as $PT)
  {	$PrestasiTambahan = implode('~', $arrPT);
  }
  $NamaAyah = sqling($_REQUEST['NamaAyah']);
  $AgamaAyah = $_REQUEST['AgamaAyah'];
  $PendidikanAyah = $_REQUEST['PendidikanAyah'];
  $PekerjaanAyah = sqling($_REQUEST['PekerjaanAyah']);
  $AlamatAyah = sqling($_REQUEST['AlamatAyah']);
  $PenghasilanAyah = $_REQUEST['PenghasilanAyah']+0;
  $HidupAyah = $_REQUEST['HidupAyah'];
  
  $NamaIbu = sqling($_REQUEST['NamaIbu']);
  $AgamaIbu = $_REQUEST['AgamaIbu'];
  $PendidikanIbu = $_REQUEST['PendidikanIbu'];
  $PekerjaanIbu = sqling($_REQUEST['PekerjaanIbu']);
  $AlamatIbu = sqling($_REQUEST['AlamatIbu']);
  $PenghasilanIbu = $_REQUEST['PenghasilanIbu']+0;
  $HidupIbu = $_REQUEST['HidupIbu'];
  
  $AlamatOrtu = sqling($_REQUEST['AlamatOrtu']);
  $RTOrtu = sqling($_REQUEST['RTOrtu']);
  $RWOrtu = sqling($_REQUEST['RWOrtu']);
  $KodePosOrtu = sqling($_REQUEST['KodePosOrtu']);
  $KotaOrtu = sqling($_REQUEST['KotaOrtu']);
  $PropinsiOrtu = sqling($_REQUEST['PropinsiOrtu']);
  $TeleponOrtu = sqling($_REQUEST['TeleponOrtu']);
  $HandphoneOrtu = sqling($_REQUEST['HandphoneOrtu']);
  $EmailOrtu = sqling($_REQUEST['EmailOrtu']);
  
  $PMBFormulirID = $_REQUEST['PMBFormulirID'];
  $ProgramID = sqling($_REQUEST['ProgramID']);
  
  
  $frm = AmbilFieldx('pmbformulir', 'PMBFormulirID', $PMBFormulirID, '*');
  $pil = array();
  $vpil = array();
  $epil = array();
  for ($i = 1; $i <= $frm['JumlahPilihan']; $i++) {
    $pil[] = 'Pilihan'.$i;
    $vpil[] = "'".sqling($_REQUEST['Pilihan'.$i])."'";
    $epil[] = 'Pilihan'.$i."='".$_REQUEST['Pilihan'.$i]."'";
  }
  $_pil = implode(', ', $pil);
  $_vpil = implode(', ', $vpil);
  $_epil = implode(', ', $epil);
  
  // simpan
  if ($md == 0) {
    $s = "update pmb
      set 
          Nama = '$Nama',
          TempatLahir = '$TempatLahir', TanggalLahir = '$TanggalLahir',
          Kelamin = '$Kelamin', GolonganDarah = '$GolonganDarah',
          Agama = '$Agama', StatusSipil = '$StatusSipil',
          TinggiBadan = '$TinggiBadan', BeratBadan = '$BeratBadan',
          WargaNegara = '$WargaNegara', Kebangsaan = '$Kebangsaan',
          Alamat = '$Alamat',
          RT = '$RT', RW = '$RW', KodePos = '$KodePos',
          Kota = '$Kota', Propinsi = '$Propinsi',
          Telepon = '$Telepon', Handphone = '$Handphone', Email = '$Email',
          PendidikanTerakhir = '$PendidikanTerakhir',
          AsalSekolah = '$AsalSekolah', TahunLulus = '$TahunLulus', NilaiSekolah = '$NilaiSekolah', PrestasiTambahan = '$PrestasiTambahan',
		  
		  NamaAyah = '$NamaAyah', PendidikanAyah = '$PendidikanAyah', AgamaAyah = '$AgamaAyah', 
          PekerjaanAyah = '$PekerjaanAyah', HidupAyah = '$HidupAyah', PenghasilanAyah = '$PenghasilanAyah',
          NamaIbu = '$NamaIbu', PendidikanIbu = '$PendidikanIbu', AgamaIbu = '$AgamaIbu',
          PekerjaanIbu = '$PekerjaanIbu', HidupIbu = '$HidupIbu', PenghasilanIbu = '$PenghasilanIbu',
          
		  AlamatOrtu = '$AlamatOrtu',
          RTOrtu = '$RTOrtu', RWOrtu = '$RWOrtu', KodePosOrtu = '$KodePosOrtu',
          KotaOrtu = '$KotaOrtu', PropinsiOrtu = '$PropinsiOrtu',
          TeleponOrtu = '$TeleponOrtu', HandphoneOrtu = '$HandphoneOrtu', EmailOrtu = '$EmailOrtu',
          PMBFormulirID = '$PMBFormulirID', ProgramID = '$ProgramID',
		  
          $_epil,
          LoginEdit = '$_SESSION[_Login]', TanggalEdit = now()
      where PMBID = '$id' ";
    $r = mysqli_query($koneksi, $s);
    SuksesTersimpan("?ndelox=$_SESSION[ndelox]", 1000);
  }
  elseif ($md == 1) {
    // Cek jika ID manual
    if (!empty($id)) {
      $ada = AmbilOneField('pmb', "KodeID='".KodeID."' and PMBID", $id, 'PMBID');
      if (!empty($ada)) {
        die(PesanError('Error',
          "Nomer PMB sudah ada.<br />
          Anda harus memasukkan nomer PMB yang lain.<br />
          Atau kosongkan untuk mendapatkan nomer secara otomatis.<br />
          Hubungi Sysadmin untuk informasi lebih lanjut.
          <hr size=1 color=silver />
          Opsi: <input type=button name='Kembali' value='Kembali'
            onClick=\"javascript:history.go(-1)\" />
            <input type=button name='Tutup' value='Tutup'
            onClick=\"window.close()\" />"));
      }
    }
    // Jika menggunakan penomeran otomatis
    else {
      $id = GetNextPMBID($gel);
    }
    // Baru kemudian disimpan
    $ProdiID = $_REQUEST['Pilihan1'];
    $s = "insert into pmb
      (PMBID, PMBPeriodID, KodeID, Nama, 
      TempatLahir, TanggalLahir, Kelamin, GolonganDarah,
      Agama, StatusSipil, TinggiBadan, BeratBadan,
      WargaNegara, Kebangsaan,
      Alamat, RT, RW, KodePos, Kota, Propinsi, 
      Telepon, Handphone, Email,
      PendidikanTerakhir, AsalSekolah,
      TahunLulus, NilaiSekolah, PrestasiTambahan, 
      NamaAyah, AgamaAyah, PendidikanAyah, PekerjaanAyah, HidupAyah, 
      NamaIbu, AgamaIbu, PendidikanIbu, PekerjaanIbu, HidupIbu, 
      AlamatOrtu, RTOrtu, RWOrtu, KodePosOrtu, KotaOrtu, PropinsiOrtu,
      TeleponOrtu, HandphoneOrtu, EmailOrtu,
      PMBFormulirID, ProgramID, ProdiID, $_pil,
	  
      LoginBuat, TanggalBuat)
      values
      ('$id', '$gel', '".KodeID."', '$Nama', 
      '$TempatLahir', '$TanggalLahir', '$Kelamin', '$GolonganDarah',
      '$Agama', '$StatusSipil', '$TinggiBadan', '$BeratBadan',
      '$WargaNegara', '$Kebangsaan',
      '$Alamat', '$RT', '$RW', '$KodePos', '$Kota', '$Propinsi', 
      '$Telepon', '$Handphone', '$Email',
      '$PendidikanTerakhir', '$AsalSekolah',
      '$TahunLulus', '$NilaiSekolah', '$PrestasiTambahan', 
      '$NamaAyah', '$AgamaAyah', '$PendidikanAyah', '$PekerjaanAyah', '$HidupAyah', 
      '$NamaIbu', '$AgamaIbu', '$PendidikanIbu', '$PekerjaanIbu', '$HidupIbu', 
      '$AlamatOrtu', '$RTOrtu', '$RWOrtu', '$KodePosOrtu', '$KotaOrtu', '$PropinsiOrtu',
      '$TeleponOrtu', '$HandphoneOrtu', '$EmailOrtu',
      '$PMBFormulirID', '$ProgramID', '$ProdiID', $_vpil,
	  
      '$_SESSION[_Login]', now())";
    $r = mysqli_query($koneksi, $s);
    SuksesTersimpan("?ndelox=$_SESSION[ndelox]", 1000);
  }
  else {
    die(PesanError('Error',
      "Terjadi kesalahan mode edit.<br />
      Mode <b>$md</b> tidak dikenali oleh sistem.
      <hr size=1 color=silver />
      <input type=button name='Tutup' value='Tutup' onClick=\"window.close()\" />"));
  }
  
}

function JumlahPilihanScript()
{	echo <<<SCR
	<SCRIPT>
		function UbahJumlahPilihan()
		{	id = frmisi.PMBFormulirID.value;
			var jmlpil = 0;
			
			if(id != '')
			{	jmlpil = document.getElementById('Form'+id).value;
			}
			else jmlpil = 3;
			
			if(jmlpil <= 1)
			{	frmisi.Pilihan2.value = '';
				frmisi.Pilihan2.disabled = true;
				frmisi.Pilihan3.value = '';
				frmisi.Pilihan3.disabled = true;
			}
			else if(jmlpil <=2)
			{	frmisi.Pilihan2.disabled = false;
				frmisi.Pilihan3.value = '';
				frmisi.Pilihan3.disabled = true;
			}
			else
			{	frmisi.Pilihan2.disabled = false;
				frmisi.Pilihan3.disabled = false;
			}
		}
	</SCRIPT>

SCR;
}
?>
