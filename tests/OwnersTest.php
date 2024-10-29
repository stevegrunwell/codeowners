<?php

declare(strict_types=1);

namespace Kellegous\CodeOwners;

use PHPUnit\Framework\TestCase;

/**
 * @covers Owners
 */
class OwnersTest extends TestCase
{
    /**
     * @return iterable<string, array{string, Entry[]}>
     * @throws ParseException
     */
    public static function getEntriesTests(): iterable
    {
        yield 'empty file' => [
            '',
            []
        ];

        yield 'all blanks' => [
            "\n\n",
            [
                new Blank(new SourceInfo(1, null)),
                new Blank(new SourceInfo(2, null)),
                new Blank(new SourceInfo(3, null)),
            ]
        ];

        yield 'all comments' => [
            self::fromLines([
                '  # comment 1',
                ' # comment 2',
                '# comment 3',
            ]),
            [
                new Comment('# comment 1', new SourceInfo(1, null)),
                new Comment('# comment 2', new SourceInfo(2, null)),
                new Comment('# comment 3', new SourceInfo(3, null)),
            ]
        ];

        yield 'mixed entries' => [
            self::fromLines([
                ' # first rule',
                '',
                ' /a/ # no owner',
                ' # second rule',
                '     /b/ @a',
                '    # third rule',
                '/c/ @a @b # inline comment',
            ]),
            [
                new Comment('# first rule', new SourceInfo(1, null)),
                new Blank(new SourceInfo(2, null)),
                new Rule(
                    Pattern::parse('/a/'),
                    [],
                    new SourceInfo(3, null),
                    '# no owner'
                ),
                new Comment('# second rule', new SourceInfo(4, null)),
                new Rule(
                    Pattern::parse('/b/'),
                    ['@a'],
                    new SourceInfo(5, null),
                    null
                ),
                new Comment('# third rule', new SourceInfo(6, null)),
                new Rule(
                    Pattern::parse('/c/'),
                    ['@a', '@b'],
                    new SourceInfo(7, null),
                    '# inline comment'
                )
            ]
        ];
    }

    /**
     * @param string[] $lines
     * @return string
     */
    private static function fromLines(array $lines): string
    {
        return implode(PHP_EOL, $lines);
    }

    /**
     * @param string $owners_file
     * @param Entry[] $expected
     * @return void
     * @throws ParseException
     *
     * @dataProvider getEntriesTests
     */
    public function testEntries(
        string $owners_file,
        array $expected
    ): void {
        $owners = Owners::fromString($owners_file);
        self::assertEquals(
            $expected,
            $owners->getEntries()
        );
    }
}
