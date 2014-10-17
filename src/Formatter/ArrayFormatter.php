<?php

namespace GitLog\Formatter;

use GitLog\CommitCollection;

class ArrayFormatter implements FormatterInterface
{
    public function formatCommitCollection(CommitCollection $collection)
    {
        $cs = array();
        foreach ($collection->getCommits() as $commit) {
            $c = array();
            $c['hash'] = $commit->getHash();
            $c['subject'] = $commit->getSubject();
            $c['author'] = $commit->getAuthorName();
            $c['email'] = $commit->getAuthorEmail();
            $c['date'] = $commit->getAuthorDate();

            $c['body'] = $commit->getBody();
            $commit->parseBody();
            $c['meta'] = $commit->getMeta();
            $c['cleanmessage'] = $commit->getCleanMessage();
            $c['logs'] = $commit->getLogs();

            $c['diff'] = array();
            foreach ($commit->getFileDiffs() as $fileDiff) {
                $c['diff']['filename'] = $fileDiff->getFileName();
                $c['diff']['additions'] = $fileDiff->getAdditions();
                $c['diff']['deletions'] = $fileDiff->getDeletions();
            }

            $cs[]= $c;
        }
        return $cs;
    }
}
