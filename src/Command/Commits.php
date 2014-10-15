<?php

namespace GitLog\Command;

use Symfony\Component\Console\Command\Command;

/**
 *
 */
class Commits extends Command
{
    protected $command;
    protected $output;
    protected $repoPath;
    protected $repository;
    protected $limit = 1;
    protected $ref = 'master';
    private $commits = null;

    protected function populateCommits($start = null)
    {
        $log = $this->repository->getLog($this->ref, null, $start, $this->limit);
        if ($this->commits === null) {
            $this->commits = $log->getCommits();
        }
        return $this;
    }

    private function getCommits()
    {
        return $this->commits;
    }

    private function parseBody($commit)
    {
        $body = array('original' => $commit->getBodyMessage());
        $lines = explode("\n", trim($body['original'], "\n"));
        $i = 0;
        while ($i < count($lines)) {
            list($key, $value) = explode(':', $lines[$i]);
            if ($i == 0) {
                if ($key == 'gitlog') {
                    $body['log'] = explode(',', trim($value));
                } else {
                    return $body;
                }
            } else {
                if ($value) {
                    $body['meta'][trim($key)] = trim($value);
                } else {
                    $body['message'] .= $key;
                }
            }

            $i++;
        }

        return $body;
    }

    protected function toArray()
    {
        $cs = array();
        foreach ($this->commits as $commit) {
            $c = array();
            $c['hash'] = $commit->getHash();
            $c['subject'] = $commit->getSubjectMessage();
            $c['author'] = $commit->getAuthorName();
            $c['email'] = $commit->getAuthorEmail();
            $c['date'] = $commit->getAuthorDate();
            $c['body'] = $this->parseBody($commit);

            $c['diff'] = array();
            $diff = $this->repository->getDiff($commit->getHash() . '~1..' . $commit->getHash() . '');
            $files = $diff->getFiles();
            foreach ($files as $fileDiff) {
                $c['diff']['filename'] = $fileDiff->getNewName();
                $c['diff']['additions'] = $fileDiff->getAdditions();
                $c['diff']['deletions'] = $fileDiff->getDeletions();
            }

            $cs[]= $c;
        }
        return $cs;
    }

    protected function toJSON()
    {
        return json_encode($this->toArray());
    }

    protected function toConsole()
    {
        $i = 0;
        foreach ($this->getCommits() as $commit) {
            $this->output->writeLn('#<info>' . $commit->getHash() . '</info>: <comment>' . $commit->getSubjectMessage().'</comment>');
            $this->output->writeLn("Author: <info>" . $commit->getAuthorName() . '</info> [' . $commit->getAuthorEmail() . '] <comment>' .  $commit->getAuthorDate()->format('d/M/Y H:i').'</comment>');
            $this->output->writeLn("Committer: <info>" . $commit->getCommitterName() . '</info>  [' . $commit->getCommitterEmail() . '] <comment>' . $commit->getCommitterDate()->format('d/M/Y H:i').'</comment>');
            $this->output->writeLn("BODY: [\n<comment>" . $commit->getBodyMessage() . "</comment>]");
            
            $diff = $this->repository->getDiff($commit->getHash() . '~1..' . $commit->getHash() . '');
            $files = $diff->getFiles();
            foreach ($files as $fileDiff) {
                $this->output->writeLn(
                    " - <fg=cyan>" . $fileDiff->getNewName() .
                    "</fg=cyan> [<info>Additions: " . $fileDiff->getAdditions() . "</info> <fg=red>Deletions: " . $fileDiff->getDeletions() . "</fg=red>]"
                );
            }
            $this->output->writeLn("");
            $i++;
        }
        $this->output->writeln('Displayed: '.$i.' commits in '. $this->ref. ' (Repo: '.$this->repoPath.')');
    }
}
