<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'genres' => ['小説'],
                'description' => '吾輩は猫である。名前はまだ無い。',
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人を動かす原則を説いた名著。',
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'genres' => ['技術書'],
                'description' => 'より良いコードを書くためのシンプルで実践的なテクニック。',
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人格主義の回復と永続的な成功のための原則。',
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'genres' => ['小説'],
                'description' => '親譲りの無鉄砲で小供の時から損ばかりしている。',
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'genres' => ['歴史', '科学'],
                'description' => '文明の誕生から現代までの人類の歴史を俯瞰する。',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'genres' => ['技術書'],
                'description' => 'アジャイルソフトウェア達人によるクリーンコードの書き方。',
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'genres' => ['自己啓発'],
                'description' => 'アドラー心理学の教えを対話形式で解き明かす。',
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'genres' => ['小説'],
                'description' => 'お笑い芸人の生き様を描いた芥川賞受賞作。',
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'genres' => ['ビジネス', '科学'],
                'description' => 'データに基づいて世界を正しく見る習慣。',
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'genres' => ['ビジネス', '歴史'],
                'description' => '世界を変えたのは、箱の発明だった。',
            ],
        ];

        foreach ($books as $index => $bookData) {
            $number = $index + 1;

            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                [
                    'user_id' => $user->id,
                    'title' => $bookData['title'],
                    'author' => $bookData['author'],
                    'published_at' => $bookData['published_at'],
                    'description' => $bookData['description'],
                    'image_url' => "https://placehold.co/200x300/e2e8f0/475569?text={$number}",
                ]
            );

            $genreIds = Genre::whereIn('name', $bookData['genres'])->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
