<?php

namespace Ernestdefoe\GroupMessages\Tests\integration\api;

use Carbon\Carbon;
use Ernestdefoe\GroupMessages\Tests\integration\GroupMessagesTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The Messages page's search: flarum/messages' message search, narrowed to one
 * conversation when asked. A group's messages turn up for its members only.
 */
class SearchTest extends GroupMessagesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $message = fn (int $id, int $dialog, int $number, string $text) => ['id' => $id, 'dialog_id' => $dialog, 'number' => $number, 'user_id' => 2, 'content' => "<t><p>$text</p></t>", 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];

        $this->prepareDatabase([
            'dialog_messages' => [
                $message(11, 1, 2, 'Pizza tonight at eight'),
                $message(12, 1, 3, 'Bring the pizza menus'),
                $message(21, 2, 2, 'Pizza later maybe'),
            ],
        ]);
    }

    /** @return int[] */
    private function found(int $actor, array $filter): array
    {
        [$status, $body] = $this->call('GET', '/api/dialog-messages', $actor, null, ['filter' => $filter, 'sort' => '-number']);
        $this->assertSame(200, $status, json_encode($body));

        return array_map(fn ($m) => (int) $m['id'], $body['data']);
    }

    #[Test]
    public function every_conversation_is_searched_by_default()
    {
        $found = $this->found(2, ['q' => 'pizza']);
        sort($found);

        $this->assertSame([11, 12, 21], $found);
    }

    #[Test]
    public function one_conversation_is_searched_newest_first()
    {
        $this->assertSame([12, 11], $this->found(2, ['q' => 'pizza', 'dialog' => '1']));
    }

    #[Test]
    public function a_group_member_finds_the_groups_messages_and_not_others()
    {
        $found = $this->found(4, ['q' => 'pizza']);
        sort($found);

        $this->assertSame([11, 12], $found);
    }

    #[Test]
    public function someone_outside_a_group_cannot_search_it()
    {
        $this->assertSame([], $this->found(5, ['q' => 'pizza', 'dialog' => '1']));
        $this->assertSame([21], $this->found(5, ['q' => 'pizza']));
    }
}
