<?php
function parseRow($row)
{
   if (empty($row)) {
       return null;
   }
   $columns = explode(",", $row);
   if (count($columns) < 3) {
       throw new Exception("Invalid input format");
   }
   $cardNumber = trim(extractValue($columns[0]), "\"");
   $amount = (float) extractValue($columns[1]);
   $currency = trim(extractValue($columns[2]), "\"}");
 
   return compact('cardNumber', 'amount', 'currency');
}
function extractValue($column)
{
   $parts = explode(':', $column);
   if (count($parts) !== 2) {
       throw new Exception("Invalid input format");
   }
   return trim($parts[1]);
}
function isEu($countryCode) {
   $euCountries = [
       'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
       'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
   ];
   return in_array($countryCode, $euCountries, true);
}
function convertCurrency($amount, $currency)
{
   $rate = @json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true)['rates'][$currency];
 
   if ($currency === 'EUR' || $rate === 0) {
       return $amount;
   }
   return $amount / $rate;
}
function calculateTransactionFee($cardNumber, $amount, $currency)
{
   $binResults = file_get_contents('https://lookup.binlist.net/' . $cardNumber);
   if (!$binResults) {
       throw new Exception('Error getting BIN information');
   }
   $binInfo = json_decode($binResults);
   $isEu = isEu($binInfo->country->alpha2);
   $amountEur = convertCurrency($amount, $currency);
   $feeRate = $isEu ? 0.01 : 0.02;
 
   return $amountEur * $feeRate;
}
 
function processInputFile($inputFilePath)
{
   $inputFile = fopen($inputFilePath, 'r');
   while (($row = fgets($inputFile)) !== false) {
       $transactionData = parseRow($row);
 
       if ($transactionData === null) {
           continue;
       }
 
       try {
           $transactionFee = calculateTransactionFee($transactionData['cardNumber'], $transactionData['amount'], $transactionData['currency']);
           echo $transactionFee . "\n";
       } catch (Exception $e) {
           error_log($e->getMessage());
       }
   }
   fclose($inputFile);
}
 
$inputFilePath = $argv[1] ?? null;
if ($inputFilePath === null) {
   throw new Exception("Input file path not provided");
}
 
processInputFile($inputFilePath);