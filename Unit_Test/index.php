<?php

public function testEmptyInputFile()
{
   $inputFile = __DIR__ . '/fixtures/empty.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $this->assertEmpty($output);
}
 
public function testInvalidInputFile()
{
   $inputFile = __DIR__ . '/fixtures/nonexistent.txt';
   $this->expectException(\Exception::class);
   $this->expectExceptionMessage("Failed to read input file: $inputFile");
   $this->runCommand('php', 'currency.php', $inputFile);
}
 
public function testInvalidBinResults()
{
   $inputFile = __DIR__ . '/fixtures/invalid-bin-results.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $this->assertStringContainsString('error!', $output);
}
 
public function testInvalidExchangeRatesApiResults()
{
   $inputFile = __DIR__ . '/fixtures/invalid-exchange-rates-api-results.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $this->assertStringContainsString('error!', $output);
}
 
public function testInvalidCurrency()
{
   $inputFile = __DIR__ . '/fixtures/invalid-currency.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $this->assertEmpty($output);
}
 
public function testValidInput()
{
   $inputFile = __DIR__ . '/fixtures/valid-input.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $expectedOutput = "0.47\n0.93\n0.84\n";
   $this->assertEquals($expectedOutput, $output);
}
 
public function testValidInputWithNoCommission()
{
   $inputFile = __DIR__ . '/fixtures/valid-input-no-commission.txt';
   $output = $this->runCommand('php', 'currency.php', $inputFile);
   $expectedOutput = "0.00\n0.00\n0.00\n";
   $this->assertEquals($expectedOutput, $output);
}
 
private function runCommand(string ...$args): string
{
   $command = implode(' ', $args);
   $process = new Process($command);
   $process->run();
   if (!$process->isSuccessful()) {
       throw new \Exception("Failed to run command: $command");
   }
   return trim($process->getOutput());
}

?>