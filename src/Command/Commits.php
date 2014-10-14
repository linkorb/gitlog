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
    protected $commits = null;

    protected function populateCommits($start = null)
    {
        $log = $this->repository->getLog($this->ref, null, $start, $this->limit);
        if ($this->commits === null) {
            $this->commits = $log->getCommits();
        }
        return $this;
    }

    protected function getCommits()
    {
        return $this->commits;
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
}
