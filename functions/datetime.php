<?php

function sejolisa_datetime_indonesia( $datetime ){

    /* ARRAY u/ hari dan bulan */
    $Hari = array ("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
    $Bulan = array ("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
 
    /* Memisahkan format tanggal bulan dan tahun menggunakan substring */
    $tahun 	 = substr($datetime, 0, 4);
    $bulan 	 = substr($datetime, 5, 2);
    $tgl	 = substr($datetime, 8, 2);
    $waktu	 = substr($datetime,11, 5);
    $hari	 = date("w", strtotime($datetime));
    
    $result = $Hari[$hari].", ".$tgl." ".$Bulan[(int)$bulan-1]." ".$tahun." ".$waktu." WIB";

    return $result;

}