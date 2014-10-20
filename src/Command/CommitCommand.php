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
use GitLog\Formatter\ArrayFormatter;
use GitLog\Formatter\ConsoleFormatter;
use GitLog\Formatter\MDFormatter;

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
        $params = $this->parseParameters($input);

        $commitCollection = new CommitCollection();
        $commitCollection->populateCommits($params['repository'], $params['ref'], $params['start'], $params['limit']);

        switch (strtolower($input->getOption('format'))) {
            case 'array':
                $formatter = new ArrayFormatter();
                print_r($formatter->formatCommitCollection($commitCollection));
                break;
            case 'json':
                $formatter = new ArrayFormatter();
                echo json_encode($formatter->formatCommitCollection($commitCollection))."\n";
                break;
            case 'md':
                $dir = $params['path'] . '/gitlog';
                if (!is_dir($dir)) {
                    $output->writeLn(
                        '<fg=red>gitlog directory is not found.</fg=red> Please create this directory in your repo.'
                    );
                    die;
                }
                $formatter = new MDFormatter($output, $dir);
                $formatter->formatCommitCollection($commitCollection);
                break;
            case 'console':
            default:
                $formatter = new ConsoleFormatter($output);
                $formatter->formatCommitCollection($commitCollection);
                break;
        }
    }

    private function parseParameters(InputInterface $input)
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

        return array('path' => $path, 'repository' => $repository, 'limit' => $limit, 'start' => $start, 'ref' => $ref);
    }
}
