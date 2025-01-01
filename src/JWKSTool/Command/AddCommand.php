<?php
/*
 * SimpleJWT
 *
 * Copyright (C) Kelvin Mo 2015-2025
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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SimpleJWT\Keys\KeyFactory;
use SimpleJWT\Keys\KeyException;

class AddCommand extends AbstractCommand {
    protected function configure() {
        parent::configure();
        $this->setName('add')->setDescription('Adds a key to the key store');
        $this->addArgument('key_file', InputArgument::REQUIRED, 'The file name of the key to add');
        $this->addOption('create', 'c', InputOption::VALUE_NONE, 'Create a new key store if it does not exist');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The key format: auto, json, pem (and bin, base64, base64url for symmetric keys)', 'auto');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Set the key id');
        $this->addOption('generate-id', null, InputOption::VALUE_REQUIRED, 'Generate the key id: thumbnail, iso-time, timestamp');
        $this->addOption('use', null, InputOption::VALUE_REQUIRED, 'Set the key use: usually one of sig, enc');
        $this->addOption('ops', null, InputOption::VALUE_REQUIRED, 'Set the key operations, delimited by commas');
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        parent::execute($input, $output);

        $stderr = $this->stderr($output);

        // Validate command line arguments
        if ($input->getOption('id') && $input->getOption('generate-id')) {
            $stderr->writeln('<error>Only one of --id or --generate-id can be specified</error>');
            return self::INVALID;
        }

        // Load the key set
        try {
            $this->loadKeySet($input->getOption('create'));
        } catch (\RuntimeException $e) {
            $stderr->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }

        $key_file = $input->getArgument('key_file');
        if (!file_exists($key_file)) {
            $stderr->writeln('<error>Key file not found: ' . $key_file . '</error>');
            return self::FAILURE;
        }
        $key_contents = file_get_contents($key_file);
        if ($key_contents === false) {
            $stderr->writeln('<error>Cannot read key file: ' . $key_file . '</error>');
            return self::FAILURE;
        }

        try {
            $key = KeyFactory::create($key_contents, $input->getOption('format'));
        } catch (KeyException $e) {
            $stderr->writeln('<error>' . $e->getMessage() . '</error>');
            return self::INVALID;
        }
        if ($key == null) {
            $stderr->writeln('<error>Key format or type not recognised</error>');
            return self::FAILURE;
        }

        if ($input->getOption('id')) $key->setKeyId($input->getOption('id'));
        if ($input->getOption('generate-id')) {
            $prefix = ($input->getOption('use')) ? $input->getOption('use') : 'key';
            switch ($input->getOption('generate-id')) {
                case 'thumbnail':
                    $key->setKeyId($key->getThumbnail());
                    break;
                case 'iso-time':
                    $key->setKeyId($prefix . '-' . gmdate('c'));
                    break;
                case 'timestamp':
                    $key->setKeyId($prefix . '-' . time());
                    break;
            }
        }
        if ($input->getOption('use')) $key->setUse($input->getOption('use'));
        if ($input->getOption('ops')) $key->setOperations(explode(',', $input->getOption('ops')));

        try {
            $this->set->add($key);
        } catch (KeyException $e) {
            $stderr->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }
        
        try {
            $this->saveKeySet();
        } catch (\RuntimeException $e) {
            $stderr->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }
        
        $output->writeln('<info>Added key: ' . $this->formatKey($key) . '</info>');
        
        return self::SUCCESS;
    }
}

?>
