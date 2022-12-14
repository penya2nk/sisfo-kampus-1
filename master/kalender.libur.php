<?php
error_reporting(0);

session_start();
include_once "../academic_sisfo1.php";

$tanggal = $_REQUEST['tanggal'];
$tanggal = (empty($tanggal))? date('Y-m-d') : $tanggal;
$md = $_REQUEST['md'];
$id = $_REQUEST['id'];

ViewHeaderApps("Kalender Tahunan");

$lungo = (empty($_REQUEST['lungo']))? 'Edit' : $_REQUEST['lungo'];
$lungo($tanggal, $md, $id);

function Edit($tanggal, $md, $id)
{	if($md == 0)
	{	$jdl = "Edit Hari Libur";	
		$w = AmbilFieldx('harilibur', "HariLiburID='$id' and KodeID", KodeID, '*');
	}
	else if($md == 1)
	{	$jdl = "Tambah Hari Libur";
		$w = array();
		$w['Warna'] = '#';
		$w['TidakAdaKuliah'] = 'Y';
		$w['NA'] = 'N';
		$w['TanggalMulai'] = $tanggal;
		$w['TanggalSelesai'] = $tanggal;
	}
	else
	{	die(PesanError("Error", "Mode penyimpanan tidak diketahui"));
	}
	CheckFormScript('Keterangan');
	$opttanggalmulai = AmbilComboTgl($w['TanggalMulai'], 'TanggalMulai');
	$opttanggalselesai = AmbilComboTgl($w['TanggalSelesai'], 'TanggalSelesai');
	$optjenislibur = AmbilCombo2('jenislibur', 'Nama', 'JenisLiburID', $w['JenisLiburID'], "KodeID='".KodeID."'", 'JenisLiburID', 0, 1); 
	$checkadakuliah = ($w['TidakAdaKuliah']== 'Y')? 'checked' : '';
	$NA = ($w['NA']== 'Y')? 'checked' : '';
	echo "<table class=bsc cellspacing=1 width=100%>
			<form name='datalibur' id='datalibur' action='?ndelox=$_SESSION[ndelox]&lungo=SavData' method=POST onSubmit=\"return CheckForm(this)\">
			<input type=hidden name='md' value='$md'>
			<input type=hidden name='id' value='$id'>
			<tr><td class=inp>Tanggal Mulai:</td>
			  <td class=ul1>$opttanggalmulai</td>
			  </tr>
			<tr><td class=inp>Tanggal Selesai:</td>
			  <td class=ul1>$opttanggalselesai</td>
			  </tr>
			<tr><td class=inp>Keterangan:</td>
			  <td class=ul1><textarea name='Keterangan' cols=30 row=2>$w[Keterangan]</textarea></td>
			  </tr>
			<tr><td class=inp>Jenis Libur:</td>
			  <td class=ul1><select name='JenisLiburID'>$optjenislibur</select></td></tr>
			<tr><td class=inp>Tidak ada kuliah/ujian?</td>
			  <td class=ul1><input type=checkbox name='TidakAdaKuliah' value='Y' $checkadakuliah></td>
			  </tr>
			<tr><td class=inp>Tidak Aktif?</td>
			  <td class=u1l><input type=checkbox name='NA' value='Y' $NA></td></tr>
			<tr><td colspan=2 align=center><input type=submit name='Simpan' value='Simpan Hari Libur' onClick=\"return CekTanggal(this.form)\">
										   <input type=button name='Batal' value='Batal' onClick=\"window.close()\"></td>
			  </tr>
			 </form>
		  </table>
		  <script>
			function CekTanggal(frm)
			{	var mulai = new Date(frm.TanggalMulai_y.value, frm.TanggalMulai_m.value, frm.TanggalMulai_d.value);
				var selesai = new Date(frm.TanggalSelesai_y.value, frm.TanggalSelesai_m.value, frm.TanggalSelesai_d.value);
			
				if(selesai < mulai) 
				{	alert('Tanggal Mulai tidak boleh melebihi Tanggal Selesai');
					return false;
				}
				
				return true;
			}
		  </script>
		 ";	
}

function SavData($tanggal, $md, $id)
{	
	global $koneksi;
	$TanggalMulai = "$_REQUEST[TanggalMulai_y]-$_REQUEST[TanggalMulai_m]-$_REQUEST[TanggalMulai_d]";
	$TanggalSelesai = "$_REQUEST[TanggalSelesai_y]-$_REQUEST[TanggalSelesai_m]-$_REQUEST[TanggalSelesai_d]";
	$Keterangan = $_REQUEST['Keterangan'];
	$JenisLiburID = $_REQUEST['JenisLiburID'];
	$TidakAdaKuliah = (!empty($_REQUEST['TidakAdaKuliah']))? 'Y' : 'N';
	$NA = (!empty($_REQUEST['NA']))? 'Y' : 'N';
	
	if($md == 0)
	{
		$s = "update harilibur 
				set TanggalMulai = '$TanggalMulai',
					TanggalSelesai = '$TanggalSelesai',
					Keterangan = '$Keterangan',
					JenisLiburID = '$JenisLiburID',
					TidakAdaKuliah = '$TidakAdaKuliah',
					TanggalEdit= now(),
					LoginEdit = '$_SESSION[_Login]',
					NA = '$NA'
				where HariLiburID='$id'
				";
		$r = mysqli_query($koneksi, $s);
	}
	else if($md == 1) 
	{	
		$s = "insert into harilibur 
					set TanggalMulai = '$TanggalMulai',
						TanggalSelesai = '$TanggalSelesai',
						KodeID='".KodeID."',
						Keterangan = '$Keterangan',
						JenisLiburID = '$JenisLiburID',
						TidakAdaKuliah = '$TidakAdaKuliah',
						TanggalBuat= now(),
						LoginBuat = '$_SESSION[_Login]',
						NA = '$NA'";
		$r = mysqli_query($koneksi, $s);
	}
	TutupScript();
}

function TutupScript() {
echo <<<SCR
<SCRIPT>
  function ttutup() {
    opener.location='../index.php?ndelox=$_SESSION[ndelox]';
    self.close();
    return false;
  }
  ttutup();
</SCRIPT>
SCR;
}
?>
