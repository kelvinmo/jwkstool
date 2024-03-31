<?php
/*
 * SimpleJWT
 *
 * Copyright (C) Kelvin Mo 2015-2024
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 *
 * 3. The name of the author may not be used to endorse or promote
 *    products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace SimpleJWT\JWKSTool\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SimpleJWT\Keys\PEMInterface;

class ExportCommand extends AbstractSelectKeyCommand {
    protected function configure() {
        parent::configure();
        $this->setName('export')->setDescription('Exports a key in the key store');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Export to this file or stdout if omitted');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Export in this key format: json, pem', 'json');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);

        $stderr = $this->stderr($output);

        try {
            $this->loadKeySet();
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }

        $key = $this->selectKey($input, $output);

        if ($key) {
            switch ($input->getOption('format')) {
                case 'json':
                    $export = json_encode($key->getKeyData());
                    if ($export === false) {
                        $stderr->writeln('<error>Error in exporting to JSON</error>');
                        return self::FAILURE;
                    }
                    break;
                case 'pem':
                    if (!($key instanceof PEMInterface)) {
                        $stderr->writeln('<error>This kind of key cannot be exported into PEM</error>');
                        return self::INVALID;
                    }
                    try {
                        $export = $key->toPEM();
                    } catch (\Exception $e) {
                        $stderr->writeln('<error>' . $e->getMessage() . '</error>');
                        return self::FAILURE;
                    }
                    break;
                default:
                    $stderr->writeln('<error>Invalid format: ' . $input->getOption('format') . '</error>');
                    return self::INVALID;
            }

            if ($input->getOption('output')) {
                file_put_contents($input->getOption('output'), $export);
            } else {
                $output->write($export);
            }
        }

        return self::SUCCESS;
    }
}

?>
