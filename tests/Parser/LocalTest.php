<?php

namespace Foodkit\ReleaseNote\Test\Parser;

use Foodkit\ReleaseNote\Parser\Local;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Foodkit\ReleaseNote\Agent\Shell;

class LocalTest extends TestCase
{
    /** @var Shell|MockObject */
    private $agent;
    /** @var Local */
    private $parser;

    public function setUp()
    {
        $this->agent = $this->createMock(Shell::class);
        $this->parser = new Local($this->agent, [
            'github.com' => Local::SERVICE_TYPE_GITHUB,
            'bitbucket.org' => Local::SERVICE_TYPE_BITBUCKET
        ]);
    }

    public function testGetCommitsProperlyFormatsValidRefs()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git log ' . $start . '...' . $end . ' --oneline')
            ->willReturn('44daa1b The user credential parameters can be omitted if your Jira issue api is public.
84ccf4c Make sure script does not fail silently on JIRA API error');

        $commits = $this->parser->getCommits($start, $end);
        $this->assertEquals($commits[0], '44daa1b The user credential parameters can be omitted if your Jira issue api is public.');
        $this->assertEquals($commits[1], '84ccf4c Make sure script does not fail silently on JIRA API error');
    }

    public function testGetCommitsWithUnknownRevision()
    {
        $start = '0.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git log ' . $start . '...' . $end . ' --oneline')
            ->willReturn(null);

        $commits = $this->parser->getCommits($start, $end);
        $this->assertInternalType('array', $commits);
        $this->assertEmpty($commits);
    }

    public function testGetCompareUrlWithValidRefsGithubHttpsClone()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('https://github.com/foodkit/automated-release-notes.git');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertEquals('https://github.com/foodkit/automated-release-notes/compare/1.0.8...1.0.9', $url);
    }

    public function testGetCompareUrlWithValidRefsBitbucketHttpsClone()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('https://bitbucket.org/foodkit/automated-release-notes.git');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertEquals('https://bitbucket.org/projects/foodkit/repos/automated-release-notes/compare/diff?targetBranch=refs%2Ftags%2F1.0.8&sourceBranch=refs%2Ftags%2F1.0.9', $url);
    }

    public function testGetCompareUrlWithValidRefsBitbucketPortHttpsClone()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('https://bitbucket.org:7999/foodkit/automated-release-notes.git');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertEquals('https://bitbucket.org/projects/foodkit/repos/automated-release-notes/compare/diff?targetBranch=refs%2Ftags%2F1.0.8&sourceBranch=refs%2Ftags%2F1.0.9', $url);
    }

    public function testGetCompareUrlWithValidRefsGithubSshClone()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('git@github.com:foodkit/automated-release-notes.git');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertEquals('https://github.com/foodkit/automated-release-notes/compare/1.0.8...1.0.9', $url);
    }

    public function testGetCompareUrlWithInvalidTrackerReturnsNull()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('https://packagist.org/packages/foodkit/automated-release-notes');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertNull($url);
    }

    public function testGetCompareUrlWithValidRefsSshClone()
    {
        $start = '1.0.8';
        $end = '1.0.9';

        $this->agent->expects($this->once())
            ->method('execute')
            ->with('git config --get remote.origin.url')
            ->willReturn('ssh://git@github.com:foodkit/automated-release-notes.git');

        $url = $this->parser->getCompareUrl($start, $end);

        $this->assertEquals('https://github.com/foodkit/automated-release-notes/compare/1.0.8...1.0.9', $url);
    }
}
