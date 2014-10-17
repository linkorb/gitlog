<?php

namespace GitLog\Formatter;

use GitLog\CommitCollection;

interface FormatterInterface
{
    public function formatCommitCollection(CommitCollection $collection);
}
