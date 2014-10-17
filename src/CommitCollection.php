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
     *  Get commits 
     * @return The commits
     */
    private function getCommits()
    {
        return $this->commits;
    }

    /**
     * Output the commits to the CLI
     */
    public function toConsole(OutputInterface $output)
    {
        $i = 0;
        foreach ($this->getCommits() as $commit) {
            $output->writeLn('#<fg=white>' . $commit->getHash() . '</fg=white>: <info>' . $commit->getSubject().'</info>');
            $output->writeLn("Author: <info>" . $commit->getAuthorName() . '</info> [' . $commit->getAuthorEmail() . '] <fg=magenta>' .  $commit->getAuthorDate()->format('Y-m-d H:i').'</fg=magenta>');
            $output->writeLn("Committer: <info>" . $commit->getCommitterName() . '</info>  [' . $commit->getCommitterEmail() . '] <fg=magenta>' . $commit->getCommitterDate()->format('Y-m-d H:i').'</fg=magenta>');
            
            $body = $commit->getBody();
            if ($body) {
                $output->writeLn("<comment>BODY: [\n" . $body . "]</comment>");
            }
            
            foreach ($commit->getFileDiffs() as $fileDiff) {
                $output->writeLn(
                    " - <fg=cyan>" . $fileDiff->getFileName() .
                    "</fg=cyan> [Additions: <info>" . $fileDiff->getAdditions() . "</info> Deletions: <fg=red>" . $fileDiff->getDeletions() . "</fg=red>]"
                );
            }
            $output->writeLn("\n");
            $i++;
        }
        $output->writeln('Displayed: '.$i.' commits.');
    }

    /**
     * Get array version of the commits
     * @return array Array of commits with parsed commit message body
     */
    public function toArray()
    {
        $cs = array();
        foreach ($this->commits as $commit) {
            $c = array();
            $c['hash'] = $commit->getHash();
            $c['subject'] = $commit->getSubject();
            $c['author'] = $commit->getAuthorName();
            $c['email'] = $commit->getAuthorEmail();
            $c['date'] = $commit->getAuthorDate();
            $c['body'] = $commit->parseBody($commit);

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

    /**
     * Get JSON version of the commits
     * @return string JSON of commits with parsed commit message body
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * Write MD file into the target repo
     */
    public function toMD($path, OutputInterface $output)
    {
        if (!is_dir($path)) {
            $output->writeLn(
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
                    $output->writeLn(
                        '<comment><fg=cyan>'. $file .'</fg=cyan> - already exists.</comment>'
                    );
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
                    $output->writeLn(
                        '<info><fg=cyan>'. $file .'</fg=cyan> - added.</info>'
                    );
                } else {
                    $output->writeLn(
                        '<fg=red>Failed generating MD file: "<comment>'.$file.'</comment>". Please check directory permissions.</fg=red>'
                    );
                }
            }
        }
    }
}
