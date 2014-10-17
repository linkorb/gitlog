<?php

namespace GitLog;

use Gitonomy\Git\Repository;
use Symfony\Component\Console\Output\OutputInterface;
use GitLog\Commit;

class CommitCollection
{
    private $commits = null;

    /**
     * Fill commits
     * @param int|null $start Starting offset
     * @return Commits The object itself
     */
    public function populateCommits(Repository $repository, $ref, $start = null, $limit = 1)
    {
        if ($this->commits === null) {
            $this->commits = array();
            $commits = $repository->getLog($ref, null, $start, $limit);
            foreach ($commits as $c) {
                $commit = new Commit(
                    $c->getHash()
                );
                $commit->setSubject($c->getSubjectMessage())
                ->setAuthor($c->getAuthorName(), $c->getAuthorEmail(), $c->getAuthorDate())
                ->setCommitter($c->getCommitterName(), $c->getCommitterEmail(), $c->getCommitterDate())
                ->setBody($c->getBodyMessage());

                $diff = $repository->getDiff($c->getHash() . '~1..' . $c->getHash() . '');
                $files = $diff->getFiles();
                foreach ($files as $diff) {
                    $newFileDiff = new FileDiff($diff->getNewName());
                    $newFileDiff->setAdditions($diff->getAdditions());
                    $newFileDiff->setDeletions($diff->getDeletions());
                    $commit->addFileDiff($newFileDiff);
                }

                $this->commits[]= $commit;
            }
        }
        return $this;
    }

    /**
     * Get commits 
     * @return The commits
     */
    public function getCommits()
    {
        return $this->commits;
    }
}
