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

        // $this->showBranches();
        $this->showCommits('master');
    }

    private function showBranches()
    {
        foreach ($this->repository->getReferences()->getBranches() as $branch) {
            $this->output->writeln("- ".$branch->getName());
        }
        // $repository->run('fetch', array('--all'));
    }

    private function showCommits($limit = 10, $start = 0)
    {
        $log = $this->repository->getLog('master', null, $start, $limit);
        $this->output->writeln($log->countCommits(). ' commits');

        // $commits = $log->getCommits();
        // echo count($log->getRevisions());exit;
        
        foreach ($commits as $commit) {
            // $l->getRevisions()
            // $commit = $revision->getCommit();
            $this->output->writeln($commit->getAuthorName());
        }
    }
}
