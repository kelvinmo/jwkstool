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

use SimpleJWT\Keys\KeyInterface;
use SimpleJWT\Keys\KeyException;

abstract class AbstractSelectKeyCommand extends AbstractCommand {
    protected function configure() {
        parent::configure();
        $this->addArgument('index', InputArgument::OPTIONAL, 'Select the key with this index or ID value');
        $this->addOption('thumb', 't', InputOption::VALUE_REQUIRED, 'Select the key with this thumbprint');
        $this->addOption('query', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Select the key matching the criterion');

        $this->setHelp('For the --query option, the criterion is specified as PROPERTY=VALUE.');
    }

    /**
     * @return KeyInterface|null
     */
    protected function selectKey(InputInterface $input, OutputInterface $output) {
        $index = $input->getArgument('index');
        $thumb = $input->getOption('thumb');
        /** @var array<string> $query */
        $query = $input->getOption('query');
        $key = null;
        $stderr = $this->stderr($output);

        if ($index != null) {
            if ($thumb || $query) {
                $stderr->writeln('<comment>Warning: key id specified, ignoring --thumb and --query</comment>');
            }
            if (is_numeric($index) && (intval($index) >= 0)) {
                $keys = $this->set->getKeys();
                if ($index < count($keys)) $key = $keys[intval($index)];
            } else {
                $key = $this->set->getById($index, true);
            }
        } elseif ($thumb) {
            if ($query) {
                $stderr->writeln('<comment>Warning: --thumb specified, ignoring --query</comment>');
            }
            $key = $this->set->getByThumbnail($thumb, true);
        } elseif ($query) {
            $criteria = [];
            foreach ($query as $q) {
                list($property, $value) = explode('=', $q, 2);
                $criteria[$property] = $value;
            }
            $key = $this->set->get($criteria);
        }

        if ($key != null) return $key;

        $stderr->writeln('<error>Key not found</error>');
        return null;
    }
}

?>
