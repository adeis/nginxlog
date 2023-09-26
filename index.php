<?php
$rootPath = "/var/log/nginx/";
$logFile = $rootPath.'access.log';
$limit = $_GET["limit"] ?? 100;
if(isset($_GET["file"])) {
    $file = $_GET["file"] ?? "access.log";
    $file = str_replace("/", "", $file);
    $file = str_replace("..", "", $file);
    $logFile = $rootPath.$file;
}
if(!file_exists($logFile)) {
    die("$logFile Log not found");
}

// list log file
$access = [];
if ($handle = opendir($rootPath)) {

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != "..") {

            $isAccess = strpos($entry, 'access');

            if($isAccess !== false) {
                $access[]=$entry;
            }

        }
    }

    closedir($handle);
}
sort($access);

// Membaca isi file access.log
$log = file_get_contents($logFile);

// Pola-pola untuk mencocokkan informasi dalam log
$patterns = [
    '/(\d+\.\d+\.\d+\.\d+)/', // 1. IP Address
    '/\[(\d{2}\/[A-Za-z]{3}\/\d{4}:\d{2}:\d{2}:\d{2} \+\d{4})\]/', // 2. Tanggal dan Waktu
    '/"([^"]+)"/', // 3. Domain
    '/"(GET|POST|DELETE|PUT|PATCH|OPTIONS|HEAD) ([^"]+) HTTP/',// 4. Metode HTTP
    '/([^"]+) HTTP/', //request
    '/HTTP\/1.1" (\d{3}+)/', // 5. Status HTTP
    '/HTTP\/1.1" \d+ ([^"]+)"/', // 6. Ukuran Respons
    '/"([^"]+)"rt=/', // 7. User Agent
    '/rt=([\d.]+)/', // 8. Waktu Respons (rt)
    '/uct="([\d.]+)/', // 9. Waktu Koneksi Atas (uct)
    '/uht="([\d.]+)/', // 10. Waktu Kepala Atas (uht)
    '/urt="([\d.]+)/', // 11. Waktu Respons Atas (urt)
];

// Memecah log menjadi baris-baris
$logLines = explode("\n", trim($log));
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" ></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js" ></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js" ></script>

    <title>Nginx log</title>
  </head>
  <body>
    <div class="container">
    <h1>Nginx log!</h1>
    <div class="table-responsive">
    <form name="tes" method="GET">
	<div class="row">
	   <div class="col-2">
		<select name="file" class="form-control">
		<?php foreach($access as $r) : ?>
			<?php if($file == $r): ?>
				<option selected value="<?= $r ?>"><?= $r ?></option>
			<?php else: ?>
				<option value="<?= $r ?>"><?= $r ?></option>
			<?php endif ?>

		<?php endforeach;?>
		</select>
		
	   </div>
	   <div class="col-2">
	   <input type="text" class="form-control" placeholder="limit" value="<?=$limit ?>" name="limit"> 
	   </div>
	   <div class="col">
		<button type="submit" class="btn btn-primary">send</button>
	   </div>
	</div>
    </form>
<?php
// Membuka tabel HTML
echo "<table border='1' class='table table-striped table-bordered table-sm dt-table' >
        <thead class='thead-dark'>
            <tr class='table-primary'>
                <th>IP Address</th>
                <th>Tanggal dan Waktu</th>
                <th>Domain</th>
                <th>Metode HTTP</th>
                <th>Request  HTTP</th>
                <th>Status HTTP</th>
                <th>Ukuran Respons</th>
                <th>User Agent</th>
                <th title ='Waktu Respons/ Response time'> (rt)</th>
                <th title ='Waktu Koneksi Atas / Upstream connection time'> (uct)</th>
                <th title ='Waktu Kepala Atas / Upstream header time'> (uht)</th>
                <th title ='Waktu Respons Atas / Upstream Response time'> (urt)</th>
                <th>Log Lengkap</th>
            </tr>
        </thead>
        <tbody>";

// Loop melalui setiap baris log
$lineNumber = 1;
foreach ($logLines as $line) {
    if ($lineNumber >= 1 && $lineNumber <= $limit) {


        echo "<tr>";
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                echo "<td>" . $matches[1] . "</td>"; // Anda dapat menyesuaikan indeks sesuai dengan pola yang sesuai
            } else {
                echo "<td>-</td>";
            }
        }
        echo "<td title='".$line."'><textarea col='4' readonly='readonly' row='4'>".$line."</textarea> </td>";
        echo "</tr>";
    } else {
        break;
    }
    $lineNumber++;
}

// Menutup tabel HTML
echo "</tbody></table>";
?>


    </div>
 </div>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script>
	$(document).ready( function () {
		$('.dt-table').DataTable({ 
			dom: 'Blfrtip',
			buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ]
	   	});
	} );
    </script>
  </body>
</html>
