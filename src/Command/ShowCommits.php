<?php

namespace GitLog\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command;
use Gitonomy\Git\Repository;

/**
 *
 */
class ShowCommits extends Commits
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('gitlog:showcommits')
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

        $ref = $input->getOption('ref');
        if ($ref) {
            $this->ref = $ref;
        }

        $this->populateCommits($start);

        switch (strtolower($input->getOption('format'))) {
            case 'array':
                print_r($this->toArray());
                break;
            case 'json':
                echo $this->toJSON()."\n";
                break;
            default:
                $this->write();
                break;
        }
    }

    private function write()
    {
        $i = 0;
        foreach ($this->getCommits() as $commit) {
            $this->output->writeLn('#' . $commit->getHash() . ': ' . $commit->getSubjectMessage());
            $this->output->writeLn("Author: " . $commit->getAuthorName() . ' [' . $commit->getAuthorEmail() . '] ' .  $commit->getAuthorDate()->format('d/M/Y H:i'));
            $this->output->writeLn("Committer: " . $commit->getCommitterName() . '  [' . $commit->getCommitterEmail() . '] ' . $commit->getCommitterDate()->format('d/M/Y H:i'));
            $this->output->writeLn("BODY: [\n" . $commit->getBodyMessage() . "]");
            
            $diff = $this->repository->getDiff($commit->getHash() . '~1..' . $commit->getHash() . '');
            $files = $diff->getFiles();
            foreach ($files as $fileDiff) {
                $this->output->writeLn(
                    " - " . $fileDiff->getNewName() .
                    " [Additions: " . $fileDiff->getAdditions() . " Deletions: " . $fileDiff->getDeletions() . "]"
                );
            }
            $this->output->writeLn("");
            $i++;
        }
        $this->output->writeln('Displayed: '.$i.' commits in '. $this->ref. ' (Repo: '.$this->repoPath.')');
    }
}
