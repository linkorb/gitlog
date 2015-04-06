<?php

namespace GitLog;

use DateTimeInterface;

class Commit
{
    private $hash;
    private $subject;
    private $authorName;
    private $authorEmail;
    private $authorDate;
    private $committerName;
    private $committerEmail;
    private $committerDate;
    private $body;
    private $filediffs = array();

    private $parsed = false;
    private $cleanmessage = null;
    private $logs = array();
    private $meta = array();

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setAuthor($name, $email, DateTimeInterface $date)
    {
        $this->authorName = $name;
        $this->authorEmail = $email;
        $this->authorDate = $date;
        return $this;
    }

    public function getAuthorName()
    {
        return $this->authorName;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function getAuthorDate()
    {
        return $this->authorDate;
    }

    public function setCommitter($name, $email, DateTimeInterface $date)
    {
        $this->committerName = $name;
        $this->committerEmail = $email;
        $this->committerDate = $date;
        return $this;
    }

    public function getCommitterName()
    {
        return $this->committerName;
    }

    public function getCommitterEmail()
    {
        return $this->committerEmail;
    }

    public function getCommitterDate()
    {
        return $this->committerDate;
    }


    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function addFileDiff(FileDiff $filediff)
    {
        $this->filediffs[] = $filediff;
    }

    public function getFileDiffs()
    {
        return $this->filediffs;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getCleanMessage()
    {
        return $this->cleanmessage;
    }

    /**
     *  Parse commit message body
     * @return array Array containing parsed commit message body
     */
    public function parseBody()
    {
        if ($this->parsed) {
            return $this;
        }

        $lines = explode("\n", trim($this->getBody(), "\n"));

        $i = 0;
        foreach ($lines as $line) {
            if ($this->parseBodyLine($line, $i) === false) {
                $this->parsed = true;
                return $this;
            }
            $i++;
        }

        $this->parsed = true;
        return $this;
    }

    private function parseBodyLine($line, $index)
    {
        $lineInfo = explode(':', $line);
        $key = $lineInfo[0];
        $value = (count($lineInfo) > 1) ? $lineInfo[1] : null;

        if ($index == 0) {
            /*
            if ($key == 'gitlog') {
                $this->logs = explode(',', trim($value));
            } else {
                return false;
            }
            */
            if ($this->parseBodyLineMeta($key, $value) === false) {
                return false;
            }
        } else {
            /*
            if ($value) {
                $this->meta[trim($key)] = trim($value);
            } else {
                $this->cleanmessage = $key;
            }
            */
            $this->parseBodyLineMessage($key, $value);
        }
        return true;
    }

    private function parseBodyLineMeta($key, $value)
    {
        if ($key == 'gitlog') {
            $this->logs = explode(',', trim($value));
        } else {
            return false;
        }
    }
    private function parseBodyLineMessage($key, $value)
    {
        if ($value) {
            $this->meta[trim($key)] = trim($value);
        } else {
            $this->cleanmessage = $key;
        }
    }
}
