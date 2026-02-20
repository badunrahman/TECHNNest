<!DOCTYPE html>
<html lang="en">
<head>


    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }

        header { text-align: center; margin-bottom: 20px; }

        h1 { margin: 0; }

        .date { color: #666; font-size: 11px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }

        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
    </style>


</head>

<body>

    <header>

        <h1><?= $title ?></h1>
        <div class="date"><?= $date ?></div>
    </header>

    <table>
        <thead>

            <tr>
                <th><?= $headers['id'] ?></th>
                <th><?= $headers['name'] ?></th>
                <th><?= $headers['category'] ?></th>
                <th><?= $headers['price'] ?></th>
                <th><?= $headers['stock'] ?></th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($products as $product): ?>

                <tr>

                    <td><?= $product['id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                    <td>$<?= number_format((float)$product['price'], 2) ?></td>
                    <td class="text-right"><?= $product['stock_quantity'] ?></td>
                </tr>

            <?php endforeach; ?>
            
        </tbody>
    </table>
</body>
</html>
