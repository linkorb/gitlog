<?php

namespace GitLog;

use Gitonomy\Git\Repository;
use Symfony\Component\Console\Output\OutputInterface;
use GitLog\Commit;

class CommitCollection
{
    
    private $commits = null;

    public function __construct()
    {
    }

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
     * Output the commits to the CLI
     */
    public function toConsole(OutputInterface $output)
    {
        $i = 0;
        foreach ($this->getCommits() as $commit) {
            $output->writeLn('#<fg=white>' . $commit->getHash() . '</fg=white>: <info>' . $commit->getSubjectMessage().'</info>');
            $output->writeLn("Author: <info>" . $commit->getAuthorName() . '</info> [' . $commit->getAuthorEmail() . '] <fg=magenta>' .  $commit->getAuthorDate()->format('Y-m-d H:i').'</fg=magenta>');
            $output->writeLn("Committer: <info>" . $commit->getCommitterName() . '</info>  [' . $commit->getCommitterEmail() . '] <fg=magenta>' . $commit->getCommitterDate()->format('Y-m-d H:i').'</fg=magenta>');
            
            $body = $commit->getBodyMessage();
            if ($body) {
                $output->writeLn("<comment>BODY: [\n" . $body . "]</comment>");
            }
            
            $diff = $this->repository->getDiff($commit->getHash() . '~1..' . $commit->getHash() . '');
            $files = $diff->getFiles();
            foreach ($files as $fileDiff) {
                $output->writeLn(
                    " - <fg=cyan>" . $fileDiff->getNewName() .
                    "</fg=cyan> [Additions: <info>" . $fileDiff->getAdditions() . "</info> Deletions: <fg=red>" . $fileDiff->getDeletions() . "</fg=red>]"
                );
            }
            $output->writeLn("\n");
            $i++;
        }
        $output->writeln('Displayed: '.$i.' commits in '. $this->ref. ' (Repo: '.$this->repoPath.')');
    }
    /**
     *  Get commits 
     * @return The commits
     */
    private function getCommits()
    {
        return $this->commits;
    }

    /**
     *  Parse commit message body
     * @return array Array containing parsed commit message body
     */
    private function parseBody($commit)
    {
        $body = array(
            'original' => $commit->getBodyMessage(),
            'log' => null,
            'meta' => array()
        );
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

    /**
     * Get array version of the commits
     * @return array Array of commits with parsed commit message body
     */
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

    /**
     * Get JSON version of the commits
     * @return string JSON of commits with parsed commit message body
     */
    protected function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * Write MD file into the target repo
     */
    protected function toMD()
    {
        $path = $this->repoPath . '/gitlog';
        if (!is_dir($path)) {
            $this->output->writeLn(
                '<fg=red>gitlog directory is not found.</fg=red> Please create this directory in your repo.'
            );
            die;
        }

        $commits = $this->toArray();
        foreach ($commits as $commit) {
            if ($commit['body']['log'] === null) {
                continue;
            }
            foreach ($commit['body']['log'] as $subdir) {
                $dir = $path.'/'.$subdir;
                if (!is_dir($dir)) {
                    mkdir($dir);
                }
                
                $file = $dir.'/'.$commit['hash'].'.md';
                if (file_exists($file)) {
                    $this->output->writeLn(
                        '<comment><fg=cyan>'. $file .'</fg=cyan> - already exists.</comment>'
                    );
                    // die;
                    continue;
                }

                $o = '';
                $o .= 'Hash: '.$commit['hash']."\n";
                $o .= 'Subject: '.$commit['subject']."\n";
                $o .= 'Author: '.$commit['author']."\n";
                $o .= 'E-mail: '.$commit['email']."\n";
                $o .= 'Time: '.$commit['date']->format('Y-m-d H:i')."\n\n";

                foreach ((array)$commit['meta'] as $k => $v) {
                    $o .= $k. ': '.$v."\n";
                }

                $o .= "\n". $commit['body']['message'];

                if (file_put_contents($file, $o)) {
                    $this->output->writeLn(
                        '<info><fg=cyan>'. $file .'</fg=cyan> - added.</info>'
                    );
                } else {
                    $this->output->writeLn(
                        '<fg=red>Failed generating MD file: "<comment>'.$file.'</comment>". Please check directory permissions.</fg=red>'
                    );
                }
            }
        }
    }
}
