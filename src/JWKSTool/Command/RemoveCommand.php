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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveCommand extends AbstractSelectKeyCommand {
    protected function configure() {
        parent::configure();
        $this->setName('remove')->setDescription('Removes a key from the key store');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not prompt');
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        parent::execute($input, $output);

        $stderr = $this->stderr($output);

        try {
            $this->loadKeySet();
        } catch (\RuntimeException $e) {
            $stderr->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }

        $key = $this->selectKey($input, $output);
        $format = $this->formatKey($key);

        if ($key) {
            if (!$input->getOption('force')) {
                /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $output->writeln('<question>About to remove key: ' . $format . '</question>');
                $question = new ConfirmationQuestion('Do you wish to delete this key (y/N)? ', false);
                if (!$helper->ask($input, $output, $question)) {
                    return 0;
                }
            }

            $this->set->remove($key);

            try {
                $this->saveKeySet();
            } catch (\RuntimeException $e) {
                $stderr->writeln('<error>' . $e->getMessage() . '</error>');
                return self::FAILURE;
            }
            $output->writeln('<info>Removed key: ' . $format . '</info>');
        }

        return self::SUCCESS;
    }
}

?>
