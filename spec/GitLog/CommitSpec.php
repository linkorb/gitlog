<?php

namespace spec\GitLog;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GitLog\FileDiff;

class CommitSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('linkorbrules');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('GitLog\Commit');
    }

    public function it_should_handle_subject()
    {
        $this->setSubject('test subject')->shouldReturn($this);
        $this->getSubject()->shouldReturn('test subject');
    }

    public function it_should_contain_file_diffs(FileDiff $fd)
    {
        $fd->getFileName()->willReturn('test.tdxt');
        $this->addFileDiff($fd);

        $diffs = $this->getFileDiffs();

        foreach ($diffs as $d) {
            $d->getFileName()->shouldReturn('test.txt');
        }
    }
}
