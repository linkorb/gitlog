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
        $path = $input->getArgument('repositorypath');
        $repository = new Repository($path);

        $limit = (int)$input->getOption('limit');
        if ($limit <= 0) {
            $limit = 1;
        }

        $start = (int)$input->getOption('start');
        if (!$start) {
            $start = 0;
        }

        $ref = $input->getOption('ref');
        if (!$ref) {
            $ref = 'master';
        }

        $commitCollection = new CommitCollection();
        $commitCollection->populateCommits($repository, $ref, $start, $limit);

        switch (strtolower($input->getOption('format'))) {
            case 'array':
                print_r($commitCollection->toArray());
                break;
            case 'json':
                echo $commitCollection->toJSON()."\n";
                break;
            case 'md':
                echo $commitCollection->toMD($path.'/gitlog', $output)."\n";
                break;
            case 'console':
            default:
                $commitCollection->toConsole($output);
                break;
        }
    }
}
