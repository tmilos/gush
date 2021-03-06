<?php

/*
 * This file is part of Gush package.
 *
 * (c) 2013-2014 Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Command\PullRequest;

use Gush\Command\BaseCommand;
use Gush\Feature\GitRepoFeature;
use Gush\Feature\TemplateFeature;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class PullRequestCreateCommand extends BaseCommand implements GitRepoFeature, TemplateFeature
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pull-request:create')
            ->setDescription('Launches a pull request')
            ->addOption('base', null, InputOption::VALUE_REQUIRED, 'Base Branch - remote branch name')
            ->addOption(
                'source-org',
                null,
                InputOption::VALUE_REQUIRED,
                'Source Organization - source organization name (defaults to current)'
            )
            ->addOption(
                'source-branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Source Branch - source branch name (defaults to current)'
            )
            ->addOption('issue', null, InputOption::VALUE_REQUIRED, 'Issue Number')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'PR Title')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command is used to make a pull request
against the configured organization and repository.

    <info>$ gush %command.name%</info>

The remote branch to make the PR against can be specified with the
<comment>--base</comment> option, and the local organization / branch with the <comment>--source-org</comment> /
<comment>--source-branch</comment> options, when these options are omitted they are determined from the current
context.

    <info>$ gush %command.name% --source-branch=my_branch --source-org=my_org --base=dev</info>

A pull request template can be specified with the <info>template</info> option:

    <info>$ gush %command.name% --template=symfony</info>

This will use the symfony specific pull request template, the full list of
available templates is displayed in the description of the <info>template</info>
option.

Note: The "custom" template is only supported when you have configured this in
your local <comment>.gush.yml</comment> file like:
<comment>
table-pr:
    bug_fix: ['Bug Fix?', no]
    new_feature: ['New Feature?', no]
    bc_breaks: ['BC Breaks?', no]
    deprecations: ['Deprecations?', no]
    tests_pass: ['Tests Pass?', no]
    fixed_tickets: ['Fixed Tickets', '']
    license: ['License', MIT]
</comment>

Each key in "table-pr" list is the name used internally by the command engine, you can choose any name
you like but note that "description" is preserved for internal usage and is not changeable
and you can only use underscores for separating words.

The value of each key is an array with "exactly two values" like ['the label', 'the default value'].

If you don't want to configure any fields at all use the following.
<comment>
table-pr: []
</comment>
<info>This will still ask the title and description, but no additional fields.</info>


The command %command.name% can also accept an issue number along with the other options:

    <info>$ gush %command.name% --issue=10430</info>

Passing an issue number will turn the issue into a pull request, provided permissions
allow it. Turning an issue in a pull request will keep the original title and existing comments.

When using a template you will be prompted to fill out the required parameters.

EOF
            )
        ;
    }

    public function getTemplateDomain()
    {
        return 'pull-request-create';
    }

    public function getTemplateDefault()
    {
        return 'symfony';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $org = $input->getOption('org');
        $issueNumber = $input->getOption('issue');
        $sourceOrg = $input->getOption('source-org');
        $sourceBranch = $input->getOption('source-branch');
        $template = $input->getOption('template');

        $config = $this->getApplication()->getConfig();
        /** @var \Gush\Config $config */

        $base = $input->getOption('base');
        if (null === $base) {
            $base = $config->get('base') ?: 'master';
        }

        if (null === $sourceOrg) {
            $sourceOrg = $this->getParameter('authentication')['username'];
        }

        if (null === $sourceBranch) {
            $sourceBranch = $this->getHelper('git')->getActiveBranchName();
        }

        $title = '';
        $body = '';
        if (null === $issueNumber) {
            $defaultTitle = $this->getHelper('git')->getFirstCommitTitle($base, $sourceBranch);
            if (!$title = $input->getOption('title')) {
                $title = $this->getHelper('question')->ask(
                    $input,
                    $output,
                    new Question(sprintf('Title: [%s]', $defaultTitle), $defaultTitle)
                );
            }

            $body = $this->getHelper('template')->askAndRender($output, $this->getTemplateDomain(), $template);
        }

        if (true === $config->get('remove-promote')) {
            $body = $this->appendPlug($body);
        }

        if (true === $input->getOption('verbose')) {
            $message = sprintf(
                'Making PR from <info>%s:%s</info> to <info>%s:%s</info>',
                $sourceOrg,
                $sourceBranch,
                $org,
                $base
            );

            if (null !== $issueNumber) {
                $message = $message.' for issue #'.$issueNumber;
            }

            $output->writeln($message);
        }

        $parameters = $issueNumber ? ['issue' => $issueNumber] : [];

        $pullRequest = $this
            ->getAdapter()
            ->openPullRequest(
                $base,
                $sourceOrg.':'.$sourceBranch,
                $title,
                $body,
                $parameters
            )
        ;

        $output->writeln($pullRequest['html_url']);

        return self::COMMAND_SUCCESS;
    }
}
