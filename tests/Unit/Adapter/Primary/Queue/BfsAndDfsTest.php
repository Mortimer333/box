<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Primary\Queue;

use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Attribute\Examples;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class BfsAndDfsTest extends BaseUnitAbstract
{
    protected TestTransport $messenger;

    public function _before(): void
    {
        parent::_before();
        $this->messenger = $this->tester->getMessenger('bus');
        $this->messenger->reset();
    }

    public function testBfsUsingSymfonyMessageQueueDungeonMasterProblem(): void
    {
        // Count the steps
        $end = 'E';
        $rock = '#';
        $dungeon = [
            ['.', '.', '#', '.', '#', '.'],
            ['.', '.', '.', '.', '.', '.'],
            ['.', '#', '.', '.', '.', '.'],
            ['.', '#', '#', '#', '#', '.'],
            ['.', '.', '#', 'E', '.', '.'],
            ['.', '#', '.', '#', '#', '.'],
        ];
        $startX = 0;
        $startY = 0;
        $visited = [];
        // left, top, right, bottom
        $directionX = [-1, 0, 1, 0];
        $directionY = [0, -1, 0, 1];
        $n = count($dungeon);
        $m = count($dungeon[0]);

        $visited[$startX][$startY] = true;
        $this->enqueueCoordinate($startX, $startY);
        $loopMax = ($n * $m) + $n;
        $i = 0;
        $endFound = false;
        $expectedSteps = 12;
        while ($this->messenger->queue()->count() > 0) {
            ++$i;
            $message = $this->dequeue();
            $x = $message->x;
            $y = $message->y;
            $path = $message->path;
            foreach (array_keys($directionX) as $directionI) {
                $newX = $x + $directionX[$directionI];
                $newY = $y + $directionY[$directionI];
                if ($visited[$newX][$newY] ?? false) {
                    continue;
                }
                $visited[$newX][$newY] = true;

                $node = $dungeon[$newX][$newY] ?? null;
                if (is_null($node) || $node === $rock) {
                    continue;
                }

                if ($node === $end) {
                    $path[] = [$newX, $newY];
                    $endFound = true;
                    $this->assertEquals($expectedSteps, count($path));
                    break 2;
                }

                $this->enqueueCoordinate($newX, $newY, $path);
            }

            if ($i >= $loopMax) {
                $this->fail('Too much iterations');
            }
        }
        $this->assertEquals(true, $endFound, 'Unable to find exit');
    }

    #[Examples([[1, 0, 7], [2, 0, 6], [3, 4, 5], [0, 3, 0], [9, 0, 20]], 28)]
    #[Examples([[0, 6, 0], [5, 8, 7], [0, 9, 0]], 24)]
    public function testDfsWithFindMaxGoldProblem(array $grid, int $expected): void
    {
        $result = 0;
        foreach ($grid as $i => $row) {
            foreach ($row as $j => $cell) {
                $result = max($result, $this->travel($grid, $i, $j));
            }
        }

        $this->assertEquals($expected, $result);
    }

    public function travel(array $grid, int $i, int $j, int $gold = 0): int
    {
        $currentGold = $grid[$i][$j] ?? 0;
        if (0 === $currentGold) {
            return 0;
        }
        $result = $currentGold + $gold;
        $grid[$i][$j] = 0;
        $search = 0;
        if ($i - 1 >= 0) {
            $search = max($search, $this->travel($grid, $i - 1, $j));
        }

        if ($j - 1 >= 0) {
            $search = max($search, $this->travel($grid, $i, $j - 1));
        }

        if ($i + 1 < count($grid)) {
            $search = max($search, $this->travel($grid, $i + 1, $j));
        }

        if ($j + 1 < count($grid[$i])) {
            $search = max($search, $this->travel($grid, $i, $j + 1));
        }

        return $result + $search;
    }

    private function enqueueCoordinate(int $x, int $y, array $path = []): void
    {
        $message = fn (int $x, int $y, array $path) => new class($x, $y, $path) {
            public function __construct(
                public int $x,
                public int $y,
                public array $path,
            ) {
            }
        };
        $path[] = [$x, $y];
        $this->messenger->send($message($x, $y, $path));
    }

    private function dequeue(): object
    {
        $message = $this->messenger->queue()->first()->getMessage();
        $this->messenger->process(1);

        return $message;
    }
}
