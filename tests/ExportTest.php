<?php

namespace SimpleJWT\JWKSTool;

use SimpleJWT\JWKSTool\Command\ExportCommand;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class ExportTest extends TestCase {
    protected function createTester() {
        return new CommandTester(new ExportCommand());
    }

    function testExportPrivateWithoutFlag() {
        $tester = $this->createTester();
        $tester->execute([ 'jwks_file' => 'test.jwks', 'index' => 'ec_key' ], [ 'capture_stderr_separately' => true ]);
        $output = $tester->getDisplay();
        $key = json_decode($output, true);
        $this->assertArrayNotHasKey('d', $key);
    }

    function testExportPrivateWithFlag() {
        $tester = $this->createTester();
        $tester->execute([ 'jwks_file' => 'test.jwks', 'index' => 'ec_key', '--export-private' => true ], [ 'capture_stderr_separately' => true ]);
        $output = $tester->getDisplay();
        $key = json_decode($output, true);
        $this->assertArrayHasKey('d', $key);
    }
}