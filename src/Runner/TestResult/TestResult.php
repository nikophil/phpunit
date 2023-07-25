<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function count;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\TestRunner\TestResult\Issues\Deprecation;
use PHPUnit\TestRunner\TestResult\Issues\Notice;
use PHPUnit\TestRunner\TestResult\Issues\PhpDeprecation;
use PHPUnit\TestRunner\TestResult\Issues\PhpNotice;
use PHPUnit\TestRunner\TestResult\Issues\PhpWarning;
use PHPUnit\TestRunner\TestResult\Issues\Warning;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private readonly int $numberOfTests;
    private readonly int $numberOfTestsRun;
    private readonly int $numberOfAssertions;

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private readonly array $testErroredEvents;

    /**
     * @psalm-var list<Failed>
     */
    private readonly array $testFailedEvents;

    /**
     * @psalm-var list<MarkedIncomplete>
     */
    private readonly array $testMarkedIncompleteEvents;

    /**
     * @psalm-var list<TestSuiteSkipped>
     */
    private readonly array $testSuiteSkippedEvents;

    /**
     * @psalm-var list<TestSkipped>
     */
    private readonly array $testSkippedEvents;

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private readonly array $testConsideredRiskyEvents;

    /**
     * @psalm-var array<string,list<PhpunitDeprecationTriggered>>
     */
    private readonly array $testTriggeredPhpunitDeprecationEvents;

    /**
     * @psalm-var array<string,list<ErrorTriggered>>
     */
    private readonly array $testTriggeredErrorEvents;

    /**
     * @psalm-var array<string,list<PhpunitErrorTriggered>>
     */
    private readonly array $testTriggeredPhpunitErrorEvents;

    /**
     * @psalm-var array<string,list<PhpunitWarningTriggered>>
     */
    private readonly array $testTriggeredPhpunitWarningEvents;

    /**
     * @psalm-var list<TestRunnerDeprecationTriggered>
     */
    private readonly array $testRunnerTriggeredDeprecationEvents;

    /**
     * @psalm-var list<TestRunnerWarningTriggered>
     */
    private readonly array $testRunnerTriggeredWarningEvents;

    /**
     * @psalm-var list<Deprecation>
     */
    private readonly array $deprecations;

    /**
     * @psalm-var list<Notice>
     */
    private readonly array $notices;

    /**
     * @psalm-var list<Warning>
     */
    private readonly array $warnings;

    /**
     * @psalm-var list<PhpDeprecation>
     */
    private readonly array $phpDeprecations;

    /**
     * @psalm-var list<PhpNotice>
     */
    private readonly array $phpNotices;

    /**
     * @psalm-var list<PhpWarning>
     */
    private readonly array $phpWarnings;

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @psalm-param list<Failed> $testFailedEvents
     * @psalm-param array<string,list<ConsideredRisky>> $testConsideredRiskyEvents
     * @psalm-param list<TestSuiteSkipped> $testSuiteSkippedEvents
     * @psalm-param list<TestSkipped> $testSkippedEvents
     * @psalm-param list<MarkedIncomplete> $testMarkedIncompleteEvents
     * @psalm-param array<string,list<PhpunitDeprecationTriggered>> $testTriggeredPhpunitDeprecationEvents
     * @psalm-param array<string,list<ErrorTriggered>> $testTriggeredErrorEvents
     * @psalm-param array<string,list<PhpunitErrorTriggered>> $testTriggeredPhpunitErrorEvents
     * @psalm-param array<string,list<PhpunitWarningTriggered>> $testTriggeredPhpunitWarningEvents
     * @psalm-param list<TestRunnerDeprecationTriggered> $testRunnerTriggeredDeprecationEvents
     * @psalm-param list<TestRunnerWarningTriggered> $testRunnerTriggeredWarningEvents
     * @psalm-param list<Deprecation> $deprecations
     * @psalm-param list<Notice> $notices
     * @psalm-param list<Warning> $warnings
     * @psalm-param list<PhpDeprecation> $phpDeprecations
     * @psalm-param list<PhpNotice> $phpNotices
     * @psalm-param list<PhpWarning> $phpWarnings
     */
    public function __construct(int $numberOfTests, int $numberOfTestsRun, int $numberOfAssertions, array $testErroredEvents, array $testFailedEvents, array $testConsideredRiskyEvents, array $testSuiteSkippedEvents, array $testSkippedEvents, array $testMarkedIncompleteEvents, array $testTriggeredPhpunitDeprecationEvents, array $testTriggeredErrorEvents, array $testTriggeredPhpunitErrorEvents, array $testTriggeredPhpunitWarningEvents, array $testRunnerTriggeredDeprecationEvents, array $testRunnerTriggeredWarningEvents, array $deprecations, array $notices, array $warnings, array $phpDeprecations, array $phpNotices, array $phpWarnings)
    {
        $this->numberOfTests                         = $numberOfTests;
        $this->numberOfTestsRun                      = $numberOfTestsRun;
        $this->numberOfAssertions                    = $numberOfAssertions;
        $this->testErroredEvents                     = $testErroredEvents;
        $this->testFailedEvents                      = $testFailedEvents;
        $this->testConsideredRiskyEvents             = $testConsideredRiskyEvents;
        $this->testSuiteSkippedEvents                = $testSuiteSkippedEvents;
        $this->testSkippedEvents                     = $testSkippedEvents;
        $this->testMarkedIncompleteEvents            = $testMarkedIncompleteEvents;
        $this->testTriggeredPhpunitDeprecationEvents = $testTriggeredPhpunitDeprecationEvents;
        $this->testTriggeredErrorEvents              = $testTriggeredErrorEvents;
        $this->testTriggeredPhpunitErrorEvents       = $testTriggeredPhpunitErrorEvents;
        $this->testTriggeredPhpunitWarningEvents     = $testTriggeredPhpunitWarningEvents;
        $this->testRunnerTriggeredDeprecationEvents  = $testRunnerTriggeredDeprecationEvents;
        $this->testRunnerTriggeredWarningEvents      = $testRunnerTriggeredWarningEvents;
        $this->deprecations                          = $deprecations;
        $this->notices                               = $notices;
        $this->warnings                              = $warnings;
        $this->phpDeprecations                       = $phpDeprecations;
        $this->phpNotices                            = $phpNotices;
        $this->phpWarnings                           = $phpWarnings;
    }

    public function numberOfTestsRun(): int
    {
        return $this->numberOfTestsRun;
    }

    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    /**
     * @psalm-return list<BeforeFirstTestMethodErrored|Errored>
     */
    public function testErroredEvents(): array
    {
        return $this->testErroredEvents;
    }

    public function numberOfTestErroredEvents(): int
    {
        return count($this->testErroredEvents);
    }

    public function hasTestErroredEvents(): bool
    {
        return $this->numberOfTestErroredEvents() > 0;
    }

    /**
     * @psalm-return list<Failed>
     */
    public function testFailedEvents(): array
    {
        return $this->testFailedEvents;
    }

    public function numberOfTestFailedEvents(): int
    {
        return count($this->testFailedEvents);
    }

    public function hasTestFailedEvents(): bool
    {
        return $this->numberOfTestFailedEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<ConsideredRisky>>
     */
    public function testConsideredRiskyEvents(): array
    {
        return $this->testConsideredRiskyEvents;
    }

    public function numberOfTestsWithTestConsideredRiskyEvents(): int
    {
        return count($this->testConsideredRiskyEvents);
    }

    public function hasTestConsideredRiskyEvents(): bool
    {
        return $this->numberOfTestsWithTestConsideredRiskyEvents() > 0;
    }

    /**
     * @psalm-return list<TestSuiteSkipped>
     */
    public function testSuiteSkippedEvents(): array
    {
        return $this->testSuiteSkippedEvents;
    }

    public function numberOfTestSuiteSkippedEvents(): int
    {
        return count($this->testSuiteSkippedEvents);
    }

    public function hasTestSuiteSkippedEvents(): bool
    {
        return $this->numberOfTestSuiteSkippedEvents() > 0;
    }

    /**
     * @psalm-return list<TestSkipped>
     */
    public function testSkippedEvents(): array
    {
        return $this->testSkippedEvents;
    }

    public function numberOfTestSkippedEvents(): int
    {
        return count($this->testSkippedEvents);
    }

    public function hasTestSkippedEvents(): bool
    {
        return $this->numberOfTestSkippedEvents() > 0;
    }

    /**
     * @psalm-return list<MarkedIncomplete>
     */
    public function testMarkedIncompleteEvents(): array
    {
        return $this->testMarkedIncompleteEvents;
    }

    public function numberOfTestMarkedIncompleteEvents(): int
    {
        return count($this->testMarkedIncompleteEvents);
    }

    public function hasTestMarkedIncompleteEvents(): bool
    {
        return $this->numberOfTestMarkedIncompleteEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpunitDeprecationTriggered>>
     */
    public function testTriggeredPhpunitDeprecationEvents(): array
    {
        return $this->testTriggeredPhpunitDeprecationEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitDeprecationEvents(): int
    {
        return count($this->testTriggeredPhpunitDeprecationEvents);
    }

    public function hasTestTriggeredPhpunitDeprecationEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitDeprecationEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<ErrorTriggered>>
     */
    public function testTriggeredErrorEvents(): array
    {
        return $this->testTriggeredErrorEvents;
    }

    public function numberOfTestsWithTestTriggeredErrorEvents(): int
    {
        return count($this->testTriggeredErrorEvents);
    }

    public function hasTestTriggeredErrorEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredErrorEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpunitErrorTriggered>>
     */
    public function testTriggeredPhpunitErrorEvents(): array
    {
        return $this->testTriggeredPhpunitErrorEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitErrorEvents(): int
    {
        return count($this->testTriggeredPhpunitErrorEvents);
    }

    public function hasTestTriggeredPhpunitErrorEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitErrorEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpunitWarningTriggered>>
     */
    public function testTriggeredPhpunitWarningEvents(): array
    {
        return $this->testTriggeredPhpunitWarningEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitWarningEvents(): int
    {
        return count($this->testTriggeredPhpunitWarningEvents);
    }

    public function hasTestTriggeredPhpunitWarningEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitWarningEvents() > 0;
    }

    /**
     * @psalm-return list<TestRunnerDeprecationTriggered>
     */
    public function testRunnerTriggeredDeprecationEvents(): array
    {
        return $this->testRunnerTriggeredDeprecationEvents;
    }

    public function numberOfTestRunnerTriggeredDeprecationEvents(): int
    {
        return count($this->testRunnerTriggeredDeprecationEvents);
    }

    public function hasTestRunnerTriggeredDeprecationEvents(): bool
    {
        return $this->numberOfTestRunnerTriggeredDeprecationEvents() > 0;
    }

    /**
     * @psalm-return list<TestRunnerWarningTriggered>
     */
    public function testRunnerTriggeredWarningEvents(): array
    {
        return $this->testRunnerTriggeredWarningEvents;
    }

    public function numberOfTestRunnerTriggeredWarningEvents(): int
    {
        return count($this->testRunnerTriggeredWarningEvents);
    }

    public function hasTestRunnerTriggeredWarningEvents(): bool
    {
        return $this->numberOfTestRunnerTriggeredWarningEvents() > 0;
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringPhpunitWarnings() &&
               !$this->hasTestRunnerTriggeredWarningEvents() &&
               !$this->hasTestTriggeredPhpunitWarningEvents();
    }

    public function wasSuccessfulIgnoringPhpunitWarnings(): bool
    {
        return !$this->hasTestErroredEvents() &&
               !$this->hasTestFailedEvents();
    }

    public function wasSuccessfulAndNoTestHasIssues(): bool
    {
        return $this->wasSuccessful() && !$this->hasTestsWithIssues();
    }

    public function hasTestsWithIssues(): bool
    {
        return $this->hasRiskyTests() ||
               $this->hasIncompleteTests() ||
               $this->hasDeprecations() ||
               !empty($this->testErroredEvents) ||
               $this->hasNotices() ||
               $this->hasWarnings();
    }

    /**
     * @psalm-return list<Deprecation>
     */
    public function deprecations(): array
    {
        return $this->deprecations;
    }

    /**
     * @psalm-return list<Notice>
     */
    public function notices(): array
    {
        return $this->notices;
    }

    /**
     * @psalm-return list<Warning>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @psalm-return list<PhpDeprecation>
     */
    public function phpDeprecations(): array
    {
        return $this->phpDeprecations;
    }

    /**
     * @psalm-return list<PhpNotice>
     */
    public function phpNotices(): array
    {
        return $this->phpNotices;
    }

    /**
     * @psalm-return list<PhpWarning>
     */
    public function phpWarnings(): array
    {
        return $this->phpWarnings;
    }

    public function hasTests(): bool
    {
        return $this->numberOfTests > 0;
    }

    public function hasDeprecations(): bool
    {
        return $this->numberOfDeprecations() > 0;
    }

    public function numberOfDeprecations(): int
    {
        return count($this->deprecations) +
               count($this->phpDeprecations) +
               count($this->testTriggeredPhpunitDeprecationEvents) +
               count($this->testRunnerTriggeredDeprecationEvents);
    }

    public function hasNotices(): bool
    {
        return $this->numberOfNotices() > 0;
    }

    public function numberOfNotices(): int
    {
        return count($this->notices) +
               count($this->phpNotices);
    }

    public function hasWarnings(): bool
    {
        return $this->numberOfWarnings() > 0;
    }

    public function numberOfWarnings(): int
    {
        return count($this->warnings) +
               count($this->phpWarnings) +
               count($this->testTriggeredPhpunitWarningEvents) +
               count($this->testRunnerTriggeredWarningEvents);
    }

    public function hasErrors(): bool
    {
        return !empty($this->testErroredEvents) ||
               !empty($this->testTriggeredPhpunitErrorEvents);
    }

    public function hasIncompleteTests(): bool
    {
        return !empty($this->testMarkedIncompleteEvents);
    }

    public function hasRiskyTests(): bool
    {
        return !empty($this->testConsideredRiskyEvents);
    }

    public function hasSkippedTests(): bool
    {
        return !empty($this->testSkippedEvents);
    }
}
