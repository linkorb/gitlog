<?php

namespace GitLog\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Gitonomy\Git\Repository;
use GitLog\CommitCollection;

class CommitCommand extends Command
{
    protected $command;
    protected $output;
    protected $repoPath;
    protected $repository;
    protected $limit = 1;
    protected $ref = 'master';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('gitlog:commit')
            ->setDescription('Show commits info')
            ->addArgument(
                'repositorypath',
                InputArgument::REQUIRED,
                'Repository path'
            )
            ->addOption(
                'ref',
                null,
                InputOption::VALUE_REQUIRED,
                'Ref'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of the commits.'
            )
            ->addOption(
                'start',
                null,
                InputOption::VALUE_REQUIRED,
                'Index to start.'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format: array, JSON, or print'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->repoPath = $input->getArgument('repositorypath');
        $this->repository = new Repository($this->repoPath);

        $limit = (int)$input->getOption('limit');
        if ($limit > 0) {
            $this->limit = $limit;
        }

        $start = (int)$input->getOption('start');
        if (!$start) {
            $start = null;
        }

        $ref = trim((string)$input->getOption('ref'));
        if ($ref != '') {
            $this->ref = $ref;
        }

        $commitCollection = new CommitCollection();
        $commitCollection->populateCommits($this->repository, $this->ref, $start, $this->limit);

        switch (strtolower($input->getOption('format'))) {
            case 'array':
                print_r($commitCollection->toArray());
                break;
            case 'json':
                echo $commitCollection->toJSON()."\n";
                break;
            case 'md':
                echo $commitCollection->toMD()."\n";
                break;
            case 'console':
            default:
                $commitCollection->toConsole($output);
                break;
        }
    }
}
