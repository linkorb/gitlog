<?php

namespace GitLog\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Gitonomy\Git\Repository;

/**
 *
 */
class ShowCommits extends Command
{
    private $command;
    private $output;
    private $repoPath;
    private $repository;
    private $limit = 1;
    private $ref = 'master';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('gitlog:showcommits')
            ->setDescription('Show commits')
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->repoPath = $input->getArgument('repositorypath');
        // $output->writeln($this->repoPath);
        $this->repository = new Repository($this->repoPath);

        $limit = (int)$input->getOption('limit');
        if ($limit > 0) {
            $this->limit = $limit;
        }

        $ref = $input->getOption('ref');
        if ($ref) {
            $this->ref = $ref;
        }
        
        $this->showCommits();
    }

    private function showCommits($start = null)
    {
        $log = $this->repository->getLog($this->ref, null, $start, $this->limit);
        $commits = $log->getCommits();
        
        $i = 0;
        foreach ($commits as $commit) {
            $this->output->writeLn('#' . $commit->getHash() . ': ' . $commit->getSubjectMessage());
            $this->output->writeLn("Author: " . $commit->getAuthorName() . ' [' . $commit->getAuthorEmail() . '] ' .  $commit->getAuthorDate()->format('d/M/Y H:i'));
            $this->output->writeLn("Committer: " . $commit->getCommitterName() . '  [' . $commit->getCommitterEmail() . '] ' . $commit->getCommitterDate()->format('d/M/Y H:i'));
            $this->output->writeLn("BODY: [" . $commit->getBodyMessage() . "]");
            
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
