<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class BlogPostSeeder extends Seeder
{
    /**
     * Seed the public blog with five entries, so the catalogue, the feed, the
     * shelves and the translation screens all have something to show.
     *
     * The translations are deliberately uneven. One entry is written in every
     * language, one is still being translated, one has gone outdated behind an
     * edited English source, and one is English only. That is what the instance
     * admin actually looks like in use, and a uniformly translated blog would
     * leave half of those screens untested.
     *
     * The English row of an entry is its source and always exists.
     */
    public function run(): void
    {
        // The reference carries on from whatever is already there, the way
        // CreateBlogPost assigns it, so seeding an instance that already holds
        // entries does not collide with them.
        $reference = (int) BlogPost::query()->max('reference');

        foreach ($this->entries() as $entry) {
            $post = BlogPost::factory()->create([
                'reference' => ++$reference,
                'shelf' => $entry['shelf'],
                'status' => $entry['status'],
                'published_at' => $entry['status'] === BlogPostStatus::Draft ? null : Date::now()->subDays($entry['days_ago']),
                'is_featured' => $entry['is_featured'],
                'author_name' => $entry['author'],
            ]);

            foreach ($entry['translations'] as $locale => $translation) {
                BlogPostTranslation::factory()->create([
                    'blog_post_id' => $post->id,
                    'locale' => $locale,
                    'slug' => $translation['slug'],
                    'title' => $translation['title'],
                    'meta_description' => $translation['meta_description'],
                    'body' => $translation['body'],
                    'state' => $locale === 'en' ? BlogTranslationState::Source : $translation['state'],
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entries(): array
    {
        return [
            [
                'shelf' => BlogShelf::Releases,
                'status' => BlogPostStatus::Published,
                'days_ago' => 40,
                'is_featured' => true,
                'author' => 'Regis Freyd',
                'translations' => $this->launchEntry(),
            ],
            [
                'shelf' => BlogShelf::Collecting,
                'status' => BlogPostStatus::Published,
                'days_ago' => 28,
                'is_featured' => false,
                'author' => 'Regis Freyd',
                'translations' => $this->copiesEntry(),
            ],
            [
                'shelf' => BlogShelf::SelfHosting,
                'status' => BlogPostStatus::Published,
                'days_ago' => 17,
                'is_featured' => false,
                'author' => 'Regis Freyd',
                'translations' => $this->selfHostingEntry(),
            ],
            [
                'shelf' => BlogShelf::Engineering,
                'status' => BlogPostStatus::Published,
                'days_ago' => 6,
                'is_featured' => false,
                'author' => 'Regis Freyd',
                'translations' => $this->encryptionEntry(),
            ],
            [
                'shelf' => BlogShelf::Collecting,
                'status' => BlogPostStatus::Draft,
                'days_ago' => 0,
                'is_featured' => false,
                'author' => 'Regis Freyd',
                'translations' => $this->insuranceEntry(),
            ],
        ];
    }

    /**
     * The launch entry, written in every language. It is the one carrying a real
     * screenshot of the marketing site, so the body of a published entry is
     * proven to render an image and not only prose.
     *
     * @return array<string, array<string, mixed>>
     */
    private function launchEntry(): array
    {
        $image = '![The KolleK home page, showing the dashboard of a collection](/images/marketing/blog/kollek-homepage.webp)';

        return [
            'en' => [
                'slug' => 'kollek-is-live',
                'title' => 'KolleK is live',
                'meta_description' => 'KolleK is out: one place to catalogue what you own, track every physical copy, and keep the data yours.',
                'state' => BlogTranslationState::Source,
                'body' => "Cataloguing what you own should not mean a spreadsheet that only you can read. KolleK is out today, and it is open source under the MIT licence.\n\n{$image}\n\n## What it does\n\nYou describe a thing once, then track every physical copy of it on its own: what you paid, what condition it is in, where it sits, and who has borrowed it.\n\n## Where it runs\n\nRun it on our instance or on your own hardware. The data is yours either way, and the export gives it back to you whole.",
            ],
            'fr_FR' => [
                'slug' => 'kollek-est-en-ligne',
                'title' => 'KolleK est en ligne',
                'meta_description' => 'KolleK est disponible : un seul endroit pour cataloguer ce que vous possédez, suivre chaque exemplaire et garder vos données.',
                'state' => BlogTranslationState::Live,
                'body' => "Cataloguer ce que l'on possède ne devrait pas se résumer à un tableur que vous seul savez lire. KolleK sort aujourd'hui, en open source sous licence MIT.\n\n{$image}\n\n## Ce qu'il fait\n\nVous décrivez un objet une fois, puis vous suivez chaque exemplaire séparément : le prix payé, l'état, l'emplacement et la personne à qui vous l'avez prêté.\n\n## Où il tourne\n\nSur notre instance ou sur votre propre machine. Les données restent les vôtres, et l'export vous les rend en entier.",
            ],
            'es_ES' => [
                'slug' => 'kollek-ya-esta-disponible',
                'title' => 'KolleK ya está disponible',
                'meta_description' => 'KolleK ya está aquí: un único lugar para catalogar lo que tienes, seguir cada copia física y conservar tus datos.',
                'state' => BlogTranslationState::Live,
                'body' => "Catalogar lo que posees no debería significar una hoja de cálculo que solo tú sabes leer. KolleK sale hoy, en código abierto con licencia MIT.\n\n{$image}\n\n## Qué hace\n\nDescribes algo una vez y luego sigues cada copia física por separado: lo que pagaste, en qué estado está, dónde se guarda y quién la tiene prestada.\n\n## Dónde funciona\n\nEn nuestra instancia o en tu propio servidor. Los datos son tuyos igualmente, y la exportación te los devuelve enteros.",
            ],
            'de_DE' => [
                'slug' => 'kollek-ist-jetzt-live',
                'title' => 'KolleK ist jetzt live',
                'meta_description' => 'KolleK ist da: ein Ort, um zu katalogisieren, was Ihnen gehört, jedes Exemplar zu verfolgen und Ihre Daten zu behalten.',
                'state' => BlogTranslationState::Live,
                'body' => "Zu katalogisieren, was einem gehört, sollte nicht auf eine Tabelle hinauslaufen, die nur Sie lesen können. KolleK erscheint heute, quelloffen unter der MIT-Lizenz.\n\n{$image}\n\n## Was es kann\n\nSie beschreiben eine Sache einmal und verfolgen dann jedes Exemplar einzeln: was Sie bezahlt haben, in welchem Zustand es ist, wo es liegt und wer es ausgeliehen hat.\n\n## Wo es läuft\n\nAuf unserer Instanz oder auf Ihrer eigenen Hardware. Die Daten gehören so oder so Ihnen, und der Export gibt sie vollständig zurück.",
            ],
            'pt_BR' => [
                'slug' => 'kollek-esta-no-ar',
                'title' => 'O KolleK está no ar',
                'meta_description' => 'O KolleK chegou: um único lugar para catalogar o que você tem, acompanhar cada cópia física e manter seus dados.',
                'state' => BlogTranslationState::Live,
                'body' => "Catalogar o que você tem não deveria significar uma planilha que só você sabe ler. O KolleK sai hoje, em código aberto sob a licença MIT.\n\n{$image}\n\n## O que ele faz\n\nVocê descreve uma coisa uma vez e depois acompanha cada cópia física separadamente: quanto pagou, em que estado está, onde fica e quem pegou emprestado.\n\n## Onde ele roda\n\nNa nossa instância ou no seu próprio servidor. Os dados são seus de qualquer forma, e a exportação devolve tudo.",
            ],
            'zh_CN' => [
                'slug' => 'kollek-zheng-shi-shang-xian',
                'title' => 'KolleK 正式上线',
                'meta_description' => 'KolleK 已经发布：在一个地方编录你拥有的东西，追踪每一件实物，并且数据始终属于你。',
                'state' => BlogTranslationState::Live,
                'body' => "编录自己拥有的东西，不该只剩下一张只有你自己看得懂的表格。KolleK 今天发布，采用 MIT 许可证开源。\n\n{$image}\n\n## 它能做什么\n\n你只需描述一件东西一次，然后分别追踪它的每一件实物：花了多少钱、品相如何、放在哪里、借给了谁。\n\n## 它跑在哪里\n\n可以用我们的实例，也可以自己部署。无论哪种方式数据都属于你，导出功能会把它们完整还给你。",
            ],
            'ja_JP' => [
                'slug' => 'kollek-kokai',
                'title' => 'KolleK を公開しました',
                'meta_description' => 'KolleK を公開しました。持ち物を一か所で目録にまとめ、現物を一点ずつ追跡し、データは手元に残ります。',
                'state' => BlogTranslationState::Live,
                'body' => "持ち物を目録にまとめる作業が、自分にしか読めない表計算で終わってしまうのは惜しいことです。KolleK を本日公開しました。MIT ライセンスのオープンソースです。\n\n{$image}\n\n## できること\n\nものを一度記述すれば、あとは現物を一点ずつ追跡できます。購入額、状態、保管場所、誰に貸しているか。\n\n## 動かす場所\n\n当方のインスタンスでも、自分の機材でも動きます。どちらでもデータは自分のものですし、エクスポートすればそのまま手元に戻せます。",
            ],
        ];
    }

    /**
     * Translated into two languages and still being worked through, so the
     * catalogue has an entry that falls back to English for most readers.
     *
     * @return array<string, array<string, mixed>>
     */
    private function copiesEntry(): array
    {
        return [
            'en' => [
                'slug' => 'a-thing-and-its-copies',
                'title' => 'A thing, and its copies',
                'meta_description' => 'Why an item and a copy are two different records, and what that buys you once a collection grows past a shelf.',
                'state' => BlogTranslationState::Source,
                'body' => "Most collection tools give you one row per object. That works until you own the same book twice.\n\n## One description, many objects\n\nAn item is the thing itself: the title, the author, the year. A copy is the object on your shelf, with its own condition, its own price and its own history.\n\n## What it buys you\n\nLending, valuation and insurance all happen to a copy rather than to an idea. Keeping them apart is what lets you say which of your two first editions is the one currently at a friend's house.",
            ],
            'fr_FR' => [
                'slug' => 'un-objet-et-ses-exemplaires',
                'title' => 'Un objet, et ses exemplaires',
                'meta_description' => 'Pourquoi un objet et un exemplaire sont deux fiches distinctes, et ce que cela change quand la collection dépasse une étagère.',
                'state' => BlogTranslationState::Live,
                'body' => "La plupart des outils de collection proposent une ligne par objet. Cela tient jusqu'au jour où vous possédez le même livre en double.\n\n## Une description, plusieurs objets\n\nL'objet, c'est la chose elle-même : le titre, l'auteur, l'année. L'exemplaire, c'est ce qui est posé sur votre étagère, avec son état, son prix et son histoire.\n\n## Ce que cela apporte\n\nLe prêt, l'estimation et l'assurance concernent un exemplaire, pas une idée. Les séparer permet de savoir laquelle de vos deux éditions originales se trouve en ce moment chez un ami.",
            ],
            'es_ES' => [
                'slug' => 'un-objeto-y-sus-copias',
                'title' => 'Un objeto y sus copias',
                'meta_description' => 'Por qué un objeto y una copia son dos fichas distintas, y qué cambia cuando la colección crece más allá de un estante.',
                'state' => BlogTranslationState::InReview,
                'body' => "La mayoría de las herramientas de colección dan una fila por objeto. Funciona hasta que tienes el mismo libro dos veces.\n\n## Una descripción, varios objetos\n\nEl objeto es la cosa en sí: el título, el autor, el año. La copia es lo que está en tu estante, con su estado, su precio y su historia.\n\n## Qué aporta\n\nEl préstamo, la tasación y el seguro afectan a una copia, no a una idea. Separarlos es lo que te permite saber cuál de tus dos primeras ediciones está ahora en casa de un amigo.",
            ],
        ];
    }

    /**
     * English and German only, with the German gone outdated behind an edited
     * source. That is the state the translation screens exist to surface.
     *
     * @return array<string, array<string, mixed>>
     */
    private function selfHostingEntry(): array
    {
        return [
            'en' => [
                'slug' => 'self-hosting-in-one-command',
                'title' => 'Self-hosting in one command',
                'meta_description' => 'The Compose stack behind a self-hosted instance, what each service does, and what to set before the first boot.',
                'state' => BlogTranslationState::Source,
                'body' => "A self-hosted instance is four services: the web container, a queue worker, a scheduler and a database.\n\n## Before the first boot\n\nCopy the example environment file, generate an application key and set a real database password. If a reverse proxy terminates TLS in front of you, name it in `TRUSTED_PROXIES` as well.\n\n## Upgrading\n\nPull a newer image and start it. Only pending migrations run, and the data lives in named volumes, so replacing the image never touches it.",
            ],
            'de_DE' => [
                'slug' => 'selbst-hosten-mit-einem-befehl',
                'title' => 'Selbst hosten mit einem Befehl',
                'meta_description' => 'Der Compose-Stack hinter einer selbst gehosteten Instanz, was jeder Dienst tut und was vor dem ersten Start gesetzt werden muss.',
                'state' => BlogTranslationState::Outdated,
                'body' => "Eine selbst gehostete Instanz besteht aus vier Diensten: dem Web-Container, einem Queue-Worker, einem Scheduler und einer Datenbank.\n\n## Vor dem ersten Start\n\nKopieren Sie die Beispiel-Umgebungsdatei, erzeugen Sie einen Anwendungsschlüssel und setzen Sie ein echtes Datenbankpasswort.\n\n## Aktualisieren\n\nEin neueres Image holen und starten. Es laufen nur ausstehende Migrationen, und die Daten liegen in benannten Volumes.",
            ],
        ];
    }

    /**
     * English only, which is the commonest state of a recent entry and the one
     * the "not translated yet" column is drawn for.
     *
     * @return array<string, array<string, mixed>>
     */
    private function encryptionEntry(): array
    {
        return [
            'en' => [
                'slug' => 'what-we-encrypt-and-why',
                'title' => 'What we encrypt, and why',
                'meta_description' => 'Which columns are encrypted at rest, how search still works over them, and where the tradeoff actually sits.',
                'state' => BlogTranslationState::Source,
                'body' => "Encrypting a column is easy. Searching it afterwards is the hard part.\n\n## The problem\n\nAn encrypted column cannot be matched with a `LIKE`, because the database only ever sees ciphertext. Encrypt a title and you have lost the ability to find it.\n\n## Blind indexes\n\nSo a second table holds a keyed hash of each searchable value. A search hashes what you typed with the same key and looks for that. The database never sees the text, and search keeps working.",
            ],
        ];
    }

    /**
     * A draft, in English, so the catalogue proves it hides one while the
     * instance admin still lists it.
     *
     * @return array<string, array<string, mixed>>
     */
    private function insuranceEntry(): array
    {
        return [
            'en' => [
                'slug' => 'insuring-a-collection',
                'title' => 'Insuring a collection',
                'meta_description' => 'What an insurer asks for, which of it the app already holds, and how to get the rest out in a form they accept.',
                'state' => BlogTranslationState::Source,
                'body' => "This one is still being written.\n\n## What an insurer wants\n\nA list, a value per line, a date for each valuation and something showing the condition. All of it per physical copy rather than per title.\n\n## Getting it out\n\nThe export writes a workbook with a sheet per record type, which is close to the shape a broker asks for.",
            ],
        ];
    }
}
