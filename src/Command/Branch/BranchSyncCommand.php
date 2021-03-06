<?php

/*
 * This file is part of Gush package.
 *
 * (c) 2013-2014 Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Command\Branch;

use Gush\Command\BaseCommand;
use Gush\Feature\GitRepoFeature;
use Gush\Helper\GitHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BranchSyncCommand extends BaseCommand implements GitRepoFeature
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('branch:sync')
            ->setDescription('Syncs local branch with its upstream version')
            ->addArgument('branch_name', InputArgument::OPTIONAL, 'Branch name to sync')
            ->addArgument(
                'remote',
                InputArgument::OPTIONAL,
                'Git remote to pull from (defaults to origin)', 'origin'
            )
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command syncs local branch with it's origin version:

    <info>$ gush %command.name% develop</info>

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitHelper = $this->getHelper('git');
        /** @var GitHelper $gitHelper */

        $remote = $input->getArgument('remote');
        $branchName = $input->getArgument('branch_name');

        if (null === $branchName) {
            $branchName = $gitHelper->getActiveBranchName();
        }

        $gitHelper->syncWithRemote($remote, $branchName);

        $output->writeln(sprintf('Branch "%s" has been synced with remote "%s".', $branchName, $remote));

        return self::COMMAND_SUCCESS;
    }
}
