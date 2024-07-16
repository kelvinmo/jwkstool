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

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use SimpleJWT\Keys\KeyInterface;
use SimpleJWT\Keys\KeyException;
use SimpleJWT\Keys\KeySet;

abstract class AbstractCommand extends SymfonyCommand {
    /** @var string */
    protected $jwksFile;

    /** @var KeySet $set */
    protected $set;

    /** @var string|null $password */
    private $password = null;

    protected function configure() {
        $this->addArgument('jwks_file', InputArgument::REQUIRED, 'The file name of the key store');
        $this->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The password used to encrypt the key store', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        /** @var string|false|null $password_option */
        $password_option = $input->getOption('password');

        if ($password_option !== false) {
            // --password is specified, but option may not be specified
            if ($password_option == null) {
                /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
                $helper = $this->getHelper('question');

                $question = new Question('Enter the password to the key store: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $question->setValidator(function ($value): string {
                    if (($value == null) || (trim($value) == '')) throw new \RuntimeException('The password cannot be empty');
                    return $value;
                });

                $this->password = $helper->ask($input, $output, $question);
            } else {
                $this->password = $password_option;
            }
        }

        $this->jwksFile = $input->getArgument('jwks_file');
        return self::SUCCESS;
    }

    /**
     * @return void
     */
    protected function loadKeySet(bool $create = false) {
        if (file_exists($this->jwksFile)) {
            $jwks_contents = file_get_contents($this->jwksFile);
            if ($jwks_contents === false) {
                throw new \RuntimeException('Cannot read key set file: ' . $this->jwksFile);
            }
            $this->set = new KeySet();
            $this->set->load($jwks_contents, $this->password);
        } else {
            if ($create) {
                $this->set = new KeySet();
            } else {
                throw new \RuntimeException('Key set file not found: ' . $this->jwksFile);
            }
        }
    }

    /**
     * @return void
     */
    protected function saveKeySet() {
        $results = file_put_contents($this->jwksFile, $this->set->toJWKS($this->password));
        if ($results === false) {
            throw new \RuntimeException('Cannot write key set file: ' . $this->jwksFile);
        }
    }

    protected function formatKey(KeyInterface $key): string {
        $result = $key->getThumbnail();
        if ($key->getKeyId() != null) {
            $result .= ' (kid: ' . $key->getKeyId() . ')';
        }
        return $result;
    }

    protected function stderr(OutputInterface $output): OutputInterface {
        return ($output instanceof ConsoleOutputInterface) ? $output->getErrorOutput() : $output;
    }
}

?>
