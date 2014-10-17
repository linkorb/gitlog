<?php

namespace GitLog\Formatter;

use GitLog\CommitCollection;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleFormatter implements FormatterInterface
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function formatCommitCollection(CommitCollection $collection)
    {
        $i = 0;
        foreach ($collection->getCommits() as $commit) {
            $this->output->writeLn('#<fg=white>' . $commit->getHash() . '</fg=white>: <info>' . $commit->getSubject().'</info>');
            $this->output->writeLn("Author: <info>" . $commit->getAuthorName() . '</info> [' . $commit->getAuthorEmail() . '] <fg=magenta>' .  $commit->getAuthorDate()->format('Y-m-d H:i').'</fg=magenta>');
            $this->output->writeLn("Committer: <info>" . $commit->getCommitterName() . '</info>  [' . $commit->getCommitterEmail() . '] <fg=magenta>' . $commit->getCommitterDate()->format('Y-m-d H:i').'</fg=magenta>');
            
            $body = $commit->getBody();
            if ($body) {
                $this->output->writeLn("<comment>BODY: [\n" . $body . "]</comment>");
            }
            
            foreach ($commit->getFileDiffs() as $fileDiff) {
                $this->output->writeLn(
                    " - <fg=cyan>" . $fileDiff->getFileName() .
                    "</fg=cyan> [Additions: <info>" . $fileDiff->getAdditions() . "</info> Deletions: <fg=red>" . $fileDiff->getDeletions() . "</fg=red>]"
                );
            }
            $this->output->writeLn("\n");
            $i++;
        }
        $this->output->writeln('Displayed: '.$i.' commits.');
    }
}
