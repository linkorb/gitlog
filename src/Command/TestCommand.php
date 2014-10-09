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
class TestCommand extends Command
{
    private $command;
    private $output;
    private $repoPath;
    private $repository;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('gitlog:test')
            ->setDefinition(array(
                new InputArgument('repositorypath', InputArgument::REQUIRED, 'repositorypath')
            ))
            ->setDescription('Test command')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->repoPath = $input->getArgument('repositorypath');
        
        $this->output = $output;
        // $output->writeln($this->repoPath);

        $this->repository = new Repository($this->repoPath);

        $this->showCommits('master');
    }

    private function showBranches()
    {
        foreach ($this->repository->getReferences()->getBranches() as $branch) {
            $this->output->writeln("- ".$branch->getName());
        }
    }

    private function showCommits($ref, $limit = 10, $start = null)
    {
        $log = $this->repository->getLog($ref, null, $start, $limit);
        $commits = $log->getCommits();
        
        foreach ($commits as $commit) {
            $this->output->writeLn('#' . $commit->getHash() . ': ' . $commit->getSubjectMessage());
            $this->output->writeLn("Author: " . $commit->getAuthorName() . ' [' . $commit->getAuthorEmail() . '] ' .  $commit->getAuthorDate()->format('d/M/Y H:i'));
            $this->output->writeLn("Committer: " . $commit->getCommitterName() . '  [' . $commit->getCommitterEmail() . '] ' . $commit->getCommitterDate()->format('d/M/Y H:i'));
            $this->output->writeLn("BODY: [" . $commit->getBodyMessage() . "]");
            
            //$tree = $commit->getTree();
            //print_r($tree);
            $diff = $this->repository->getDiff($commit->getHash() . '~1..' . $commit->getHash() . '');
            $files = $diff->getFiles();
            foreach ($files as $fileDiff) {
                $this->output->writeLn(
                    " - " . $fileDiff->getNewName() .
                    " [Additions: " . $fileDiff->getAdditions() . " Deletions: " . $fileDiff->getDeletions() . "]"
                );
            }
            $this->output->writeLn("");
        }

    }
}
