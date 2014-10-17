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
class BranchCommand extends Command
{
    private $command;
    private $output;
    private $repoPath;
    private $repository;
    private $type = null;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this
            ->setName('gitlog:branch')
            ->setDescription('Show branches')
            ->addArgument(
                'repositorypath',
                InputArgument::REQUIRED,
                'Repository path'
            )
            ->addOption(
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'Show local or remote branches, default all.'
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
        $this->type = $input->getOption('type');
        $this->repository = new Repository($this->repoPath);
        $this->showBranches();
    }

    private function getBranches()
    {
        return $this->repository->getReferences()->getBranches();
    }

    private function showBranches()
    {
        $branches = $this->getBranches();
        $i = 0;
        foreach ($branches as $branch) {
            switch ($this->type) {
                case 'local':
                    if ($branch->isLocal()) {
                        $this->output->writeln('- '.$branch->getName());
                        $i++;
                    }
                    break;
                case 'remote':
                    if ($branch->isRemote()) {
                        $this->output->writeln('- '.$branch->getName());
                        $i++;
                    }
                    break;
                default:
                    $this->output->writeln('- '.$branch->getName());
                    $i++;
                    break;
            }
        }
        $this->output->writeln('Total '.$i.' '.$this->type.' branches.');
    }
}
