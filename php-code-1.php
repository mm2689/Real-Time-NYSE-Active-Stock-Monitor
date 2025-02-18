<?php
require 'vendor/autoload.php';

use MongoDB\Client;

$client = new Client('mongodb://localhost:27017');
$collection = $client->stock_database->stocks;

// Determine sort field and order
$sortField = $_GET['sort'] ?? null; 
$sortOrder = ($_GET['order'] ?? 'asc') == 'asc' ? 1 : -1; // Order can be 'asc' or 'desc'

// Fetch the sorted data from MongoDB
$options = [];
if ($sortField && $sortField != 'index') { 
    $options['sort'] = [$sortField => $sortOrder];
}
$stocks = $collection->find([], $options);

// HTML output
echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Most Active Stocks</title>';
echo '</head>';
echo '<body>';
echo '<h1>Most Active Stocks</h1>';
echo '<table border="1">';
echo '<tr>';
echo '<th><a href="?">Index</a></th>'; 
echo '<th><a href="?sort=symbol&order=' . ($sortField === 'symbol' && $sortOrder === 1 ? 'desc' : 'asc') . '">Symbol</a></th>';
echo '<th>Name</th>';
echo '<th>Price (Intraday)</th>';
echo '<th>Change</th>';
echo '<th>Volume</th>';
echo '</tr>';

$index = 1; // Initialize a counter for the index
foreach ($stocks as $stock) {
    echo '<tr>';
    echo '<td>' . $index++ . '</td>'; // Dynamic index that changes with the sort
    echo '<td>' . htmlspecialchars($stock['symbol']) . '</td>';
    echo '<td>' . htmlspecialchars($stock['name']) . '</td>';
    echo '<td>' . htmlspecialchars($stock['price']) . '</td>';
    echo '<td>' . htmlspecialchars($stock['change']) . '</td>';
    echo '<td>' . htmlspecialchars($stock['volume']) . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';
?>

