<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Cek Mutasi</title>
    <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
</head>
<body>
<div style='width:800px;margin:50px auto;'>
<?php

?>
<p>
    Perhatian, dengan membuka halaman ini tidak akan mempengaruhi transaksi yang sudah terjadi. <br />
    Fungsi dari halaman ini adalah hanya untuk pengecekan data, bukan untuk automatisasi aktivasi. <br />
    Data yang dicek adalah data dari tanggal <?php echo date("d F Y",strtotime("-7 day")); ?>
</p>

<?php
if(0 < count($mutation_data) ) :
    ?><br />
    <h3>HASIL DARI MUTASI BCA</h3>
    <table width='100%' class="pure-table">
    <thead>
        <tr>
            <th>Trans</th>
            <!-- <th>Nominal</th> -->
            <th>Note</th>
        </tr>
    </thead>
    <?php
    foreach($mutation_data as $key => $_detail) :
        if(is_array($_detail) && 0 < count($_detail)) :
            foreach($_detail as $id => $_more_detail) :
                ?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <!-- <td><?php echo $_more_detail['nominal']; ?></td> -->
                    <td><?php echo $_more_detail['note']; ?></td>
                </tr>
                <?php
            endforeach;
        endif;
    endforeach;
    ?></table><?php
else :
    ?><p>Mutasi kosong</p><?php
endif;

if( $order_data ) :
    ?>
    <br /><br />
    <h3>HASIL ANTRIAN KONFIRMASI PEMBAYARAN</h3>
    <table width='100%' class="pure-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Unique Code</th>
            <th>Total</th>
            <th>Time</th>
            <th>Curr Stat</th>
            <th>Should Stat</th>
        </tr>
    </thead>
    <?php
    foreach($order_data as $id => $_detail) :

        $status 	= "NOT PAID";
        $price      = intval($_detail->total);

        if(isset($mutation_data[$price])) :
            $status 	= "PAID";
        endif;
        ?>
        <tr>
            <td><?php echo $_detail->order_id; ?></td>
            <td><?php echo $_detail->unique_code; ?></td>
            <td><?php echo intval($_detail->total); ?></td>
            <td><?php echo date("Y-m-d", strtotime($_detail->created_at)); ?></td>
            <td><?php echo $_detail->status; ?></td>
            <td><?php echo $status; ?></td>
        </tr>
        <?php
    endforeach;
    ?></table><?php

else :
    ?><p>Antrian kosong</p><?php
endif;
?>

</body>
</html>
