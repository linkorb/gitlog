<?php

namespace GitLog\Formatter;

use GitLog\CommitCollection;
use GitLog\Commit;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;

class MDFormatter implements FormatterInterface
{
    private $basedir;
    private $br = "\n";
    private $output;

    public function __construct(OutputInterface $output, $basedir)
    {
        if (!$basedir) {
            throw new InvalidArgumentException('Please pass base dir');
        }
        $this->basedir = $basedir;
        $this->output = $output;
    }

    public function formatCommitCollection(CommitCollection $collection)
    {
        foreach ($collection->getCommits() as $commit) {
            $commit->parseBody();
            if (count($commit->getLogs()) === 0) {
                continue;
            }
            $this->commitToMD($commit);
        }
    }

    private function commitToMD(Commit $commit)
    {
        foreach ($commit->getLogs() as $subdir) {
            $dir = $this->ensureLogDir($subdir);

            $file = $dir. '/' .$commit->getHash().'.md';
            if (file_exists($file)) {
                $this->output->writeLn(
                    '<comment><fg=cyan>'. $file .'</fg=cyan> - already exists.</comment>'
                );
                continue;
            }

            $o = $this->constructMD($commit);

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

    private function ensureLogDir($log)
    {
        $dir = $this->basedir . '/' . $log;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        return $dir;
    }

    private function constructMD(Commit $commit)
    {
        $o = '';
        $o .= 'Hash: '. $commit->getHash() .$this->br;
        $o .= 'Subject: '. $commit->getSubject() .$this->br;
        $o .= 'Author: '. $commit->getAuthorName(). $this->br;
        $o .= 'E-mail: '. $commit->getAuthorEmail(). $this->br;
        $o .= 'Time: '. $commit->getAuthorDate()->format('Y-m-d H:i'). $this->br. $this->br;

        foreach ($commit->getMeta() as $k => $v) {
            $o .= $k. ': '.$v. $this->br;
        }

        $o .= $this->br . $commit->getCleanMessage();
        return $o;
    }
}
