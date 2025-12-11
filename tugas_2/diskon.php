
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas 2</title>
    <style>
        body 
        { 
            font-family: sans-serif; 
            background-color: #f4f4f9; 
            display: flex; 
            justify-content: center; 
            padding-top: 50px; 
        }

        .container 
        { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
            width: 400px; 
        }

        h2 
        { 
            text-align: center; 
            color: #333; 
        }

        input[type="number"] 
        { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            box-sizing: border-box; 
        }
        button 
        { 
            width: 100%; 
            padding: 10px; 
            background-color: #282ca7ff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }

        button:hover 
        { background-color: #212488ff; }

        .result 
        { margin-top: 20px; border-top: 2px dashed #ccc; padding-top: 10px; }

        .row 
        { display: flex; justify-content: space-between; margin-bottom: 5px; }

        .total-pay 
        { font-weight: bold; font-size: 1.2em; color: #d9534f; }
    </style>
</head>
<body>

<div class="container">
    <h2>Hitung Diskon</h2>
    <form method="POST">
        <label>Masukkan Total Belanja:</label>
        <input type="number" name="totalBelanja" required min="0">
        <button type="submit" name="hitung">Hitung Diskon</button>
    </form>

    <?php
    if (isset($_POST['hitung'])) {
        $totalBelanjaInput = $_POST['totalBelanja'];

        function hitungDiskon($total) {
            $diskon = 0;
            
            if ($total >= 100000) {
                $diskon = 0.1 * $total; 
            } elseif ($total >= 50000) {
                $diskon = 0.05 * $total;
            } 
            
            return $diskon;
        }

        $nilaiDiskon = hitungDiskon($totalBelanjaInput);
        $totalBayar = $totalBelanjaInput - $nilaiDiskon;

        echo '<div class="result">';
        echo '<div class="row"><span>Total Belanja:</span> <span>Rp ' . number_format($totalBelanjaInput, 0, ',', '.') . '</span></div>';
        echo '<div class="row"><span>Diskon:</span> <span>- Rp ' . number_format($nilaiDiskon, 0, ',', '.') . '</span></div>';
        echo '<hr>';
        echo '<div class="row total-pay"><span>Total Bayar:</span> <span>Rp ' . number_format($totalBayar, 0, ',', '.') . '</span></div>';
        echo '</div>';
    }
    ?>
</div>

</body>
</html>
