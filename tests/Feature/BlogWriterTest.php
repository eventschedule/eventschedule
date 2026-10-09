<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\AdminAlertService;
use App\Services\Blog\BlogFacts;
use App\Services\Blog\BlogGate;
use App\Services\Blog\BlogLinks;
use App\Services\Blog\BlogWriter;
use App\Utils\GeminiUtils;
use App\Utils\PlatformPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The blog's writer: a brief, a draft and an edit, then the check in code.
 *
 * Until 2026-10 one prompt wrote a post in one call and published it. It knew one fact about the
 * product, asked for "exactly 2 internal links" and was checked for length and for a title too
 * like another. Of the twenty newest posts, twenty had exactly two links (the homepage and at
 * most one audience page), fourteen called the product "a platform like Event Schedule", four
 * carried the em dashes the prompt forbade, and several described features that do not exist.
 * A post the check rejected was thrown away.
 *
 * The model is stood in for by GeminiUtils::fakeResponses(), keyed on the stage each call names.
 */
class BlogWriterTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, list<array{prompt: string, options: array<string, mixed>}>> */
    private array $asked = [];

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);
        BlogWriter::release();

        parent::tearDown();
    }

    /** @param  array<string, mixed>  $answers  stage => row, or a closure(prompt, how many times asked) */
    private function model(array $answers): void
    {
        $this->asked = [];

        GeminiUtils::fakeResponses(function ($prompt, $image, $purpose, $options) use ($answers) {
            $stage = $options['stage'] ?? 'other';
            $this->asked[$stage][] = ['prompt' => $prompt, 'options' => $options];
            $answer = $answers[$stage] ?? null;

            if ($answer instanceof \Closure) {
                $answer = $answer($prompt, count($this->asked[$stage]));
            }

            return $answer === null ? null : [$answer];
        });
    }

    /** @return array<string, mixed> */
    private function brief(array $over = []): array
    {
        return array_merge([
            'primary_query' => 'how to price door tickets',
            'reader' => 'Someone who runs a monthly night in a small room and sells most tickets on the door.',
            'format' => 'how-to',
            'angle' => 'What to charge on the door against in advance, with the numbers.',
            'questions' => ['How much more should the door cost?', 'When do advance sales close?', 'How is cash recorded?'],
            'capabilities' => ['paid-tickets', 'sales-window', 'scan'],
            'notes' => '',
        ], $over);
    }

    /**
     * A post the check passes: an opening paragraph, four sections, over 800 words, four links
     * to pages on the list, a title and a description of the right length.
     *
     * @return array<string, mixed>
     */
    private function goodPost(array $over = [], string $extra = ''): array
    {
        $base = BlogLinks::base();
        $filler = str_repeat('You set the price before the doors open and you tell people early. A small room fills from the people who already know it, so the price on the night can sit a little above the price the week before. ', 6);

        $content = '<p>Charge a little more on the door than in advance, and close advance sales an hour before you open. '
            .'That is the whole of it for a small room, and the sections below give the numbers.</p>'
            .'<h2>What to charge on the door</h2><p>'.$filler.'You can <a href="'.$base.'/features/ticketing">sell tickets with a price</a> on the Pro plan.</p>'
            .'<h2>When advance sales close</h2><p>'.$filler.'Each ticket type has a date sales close, as the <a href="'.$base.'/docs/tickets">guide to tickets</a> shows.</p>'
            .'<ol><li>Open the event.</li><li>Set the date sales close.</li></ol>'
            .'<h2>Cash on the night</h2><p>'.$filler.'The <a href="'.$base.'/pricing">plans and prices</a> are on one page.</p>'
            .'<h2>Questions people ask</h2><h3>Can the door price change?</h3><p>'.$filler.'You can <a href="'.$base.'/features/check-in">scan tickets at the door</a> on every plan.</p>'
            .$extra;

        return array_merge([
            'title' => 'How to Price Door Tickets for a Small Night',
            'description' => 'What to charge on the door against in advance for a small night, when to close advance sales, and how to record cash taken on the night.',
            'excerpt' => 'Door against advance prices for a small room, with the numbers.',
            'category' => 'Selling tickets',
            'content' => $content,
            'faq' => [['question' => 'Can the door price change?', 'answer' => 'Yes, between events.']],
            'product_claims' => [['sentence' => 'You can sell tickets with a price on the Pro plan.', 'fact_id' => 'paid-tickets']],
            'verdict' => 'publish',
            'fixed' => [],
            'remaining' => [],
        ], $over);
    }

    public function test_a_clean_post_is_published_with_its_section_and_its_questions(): void
    {
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $this->goodPost()]);

        $writer = app(BlogWriter::class);
        $written = $writer->write(['topic' => 'Pricing door tickets for a small night']);

        $this->assertSame([], $written['failures'], 'fixture: the post passes the check');
        $this->assertSame(['brief', 'draft', 'edit'], array_column($written['calls'], 'stage'), 'three calls, in this order, and no fourth');

        $post = $writer->store($written, BlogWriter::SOURCE_DAILY);

        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNull($post->held_reason);
        $this->assertSame('selling-tickets', $post->category, 'the section the model named, as its key');
        $this->assertSame('how to price door tickets', $post->primary_query);
        $this->assertSame('Can the door price change?', $post->faq[0]['question']);
        $this->assertSame(BlogWriter::SOURCE_DAILY, $post->source);
        $this->assertSame([], $post->tags, 'sections replaced free tags: 641 of them on 222 posts');
        $this->assertNotNull($post->featured_image);
    }

    public function test_a_claim_that_cites_no_fact_holds_the_post_as_a_draft(): void
    {
        $invented = $this->goodPost(['product_claims' => [['sentence' => 'Event Schedule offers robust volunteer scheduling.', 'fact_id' => 'volunteer-scheduling']]]);
        $this->model(['brief' => $this->brief(), 'draft' => $invented, 'edit' => $invented, 'edit-again' => $invented]);

        $writer = app(BlogWriter::class);
        $written = $writer->write(['topic' => 'Pricing door tickets for a small night']);
        $post = $writer->store($written, BlogWriter::SOURCE_DAILY);

        $this->assertFalse($post->is_published, 'a claim the facts do not hold went live');
        $this->assertNull($post->published_at);
        $this->assertStringContainsString('cites no fact', $post->held_reason);
        $this->assertSame(1, BlogPost::heldForReview()->count(), 'a rejected post used to be thrown away');
        $this->assertSame(0, BlogPost::published()->count());

        AdminAlertService::flush();
        $this->assertContains('blog_posts_held', AdminAlertService::items()->pluck('type')->all(), 'nobody is told a post is waiting');
    }

    public function test_a_link_that_is_not_on_the_list_is_unwrapped_and_its_words_kept(): void
    {
        // Told to copy addresses character for character, the model wrote this host twice.
        $stray = '<p>Compare with the <a href="https://eventschedule.eventschedule.com/ticket-fee-calculator">fee calculator</a>.</p>';
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost([], $stray), 'edit' => $this->goodPost([], $stray)]);

        $written = app(BlogWriter::class)->write(['topic' => 'Pricing door tickets for a small night']);

        $this->assertSame([], $written['failures']);
        $this->assertStringNotContainsString('eventschedule.eventschedule.com', $written['post']['content']);
        $this->assertStringContainsString('<p>Compare with the fee calculator.</p>', $written['post']['content']);
        $this->assertStringNotContainsString('eventschedule.eventschedule.com', $this->asked['edit'][0]['prompt'], 'the editor was handed the stray link');
    }

    public function test_what_the_check_finds_goes_back_to_the_editor_once(): void
    {
        $dashed = $this->goodPost([], '<p>The door is where it shows — every time.</p>');
        $this->model(['brief' => $this->brief(), 'draft' => $dashed, 'edit' => $dashed, 'edit-again' => $this->goodPost()]);

        $written = app(BlogWriter::class)->write(['topic' => 'Pricing door tickets for a small night']);

        $this->assertSame([], $written['failures'], 'the second edit fixed it, and the post is still held');
        $this->assertSame(['brief', 'draft', 'edit', 'edit-again'], array_column($written['calls'], 'stage'));
        $this->assertStringContainsString('an em dash or an en dash', $this->asked['edit-again'][0]['prompt'], 'the editor was not told what failed');
        $this->assertCount(1, $this->asked['edit-again'], 'the check is sent back once, not until it passes');
    }

    public function test_the_rules_the_old_prompt_only_asked_for_are_each_checked(): void
    {
        $allowed = array_keys(BlogLinks::targets());
        $base = BlogLinks::base();

        $cases = [
            'an em dash' => $this->goodPost([], '<p>Open early — it helps.</p>'),
            'Markdown inside the HTML' => $this->goodPost([], '<p>Use **bold** words.</p>'),
            'the title is' => $this->goodPost(['title' => 'How to Price Door Tickets for a Small Monthly Night Without Losing the Regulars']),
            'the title opens on a stock word' => $this->goodPost(['title' => 'Mastering Door Prices for a Small Night']),
            'the description is' => $this->goodPost(['description' => 'Too short.']),
            'names the product as a category' => $this->goodPost([], '<p>A platform like Event Schedule often helps.</p>'),
            'a percentage nobody supplied' => $this->goodPost([], '<p>Fees are typically 5% of every ticket sold.</p>'),
            'links to the home page' => $this->goodPost([], '<p>See <a href="'.$base.'">the site</a> and <a href="'.$base.'/">the site again</a>.</p>'),
            'stock words' => $this->goodPost([], '<p>'.str_repeat('A seamless, robust way to streamline it. ', 3).'</p>'),
            'an h1 in the body' => $this->goodPost([], '<h1>Again</h1>'),
        ];

        foreach ($cases as $expected => $post) {
            $failures = implode(' | ', BlogGate::failures($post, ['allowed' => $allowed]));

            $this->assertStringContainsString($expected, $failures, $expected.' passed the check');
        }

        // And none of them is what holds a clean post.
        $this->assertSame([], BlogGate::failures($this->goodPost(), ['allowed' => $allowed]));

        // A percentage is fine when the brief supplied it, or when the sentence is plainly an example.
        $this->assertSame([], BlogGate::failures($this->goodPost([], '<p>Stripe charges 2.9% in the United States.</p>'), ['allowed' => $allowed, 'figures' => 'the rate is 2.9% + $0.30']));
        $this->assertSame([], BlogGate::failures($this->goodPost([], '<p>Say you give 10% off to the first twenty.</p>'), ['allowed' => $allowed]));
        // And a width in a snippet the post quotes is markup, not a statistic.
        $this->assertSame([], BlogGate::failures($this->goodPost([], '<blockquote><p>&lt;iframe src=&quot;x&quot; width=&quot;100%&quot;&gt;&lt;/iframe&gt;</p></blockquote>'), ['allowed' => $allowed]));

        // Two links, the old prompt's whole allowance, is no longer enough.
        $two = $this->goodPost(['content' => '<p>Open.</p><h2>A</h2><p>'.str_repeat('Plain words about the door and the price on the night. ', 90).'<a href="'.$base.'/pricing">prices</a> <a href="'.$base.'">home</a></p><h2>B</h2><p>x</p><h2>C</h2><p>y</p>']);
        $this->assertStringContainsString('2 links', implode(' | ', BlogGate::failures($two, ['allowed' => $allowed])));
    }

    public function test_a_post_the_editor_doubts_is_held(): void
    {
        $doubted = $this->goodPost(['remaining' => ['The refund rule in section two should be checked by a person.']]);
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $doubted]);

        $written = app(BlogWriter::class)->write(['topic' => 'Pricing door tickets for a small night']);

        $this->assertCount(1, $written['failures']);
        $this->assertStringContainsString('the editor asks for a look', $written['failures'][0]);
    }

    public function test_the_planner_is_told_when_it_chooses_a_post_the_blog_already_has(): void
    {
        BlogPost::create(['title' => '5 Ways to Recruit & Retain Event Volunteers', 'content' => '<p>x</p>', 'is_published' => true, 'published_at' => now()->subMonth()]);

        $repeat = $this->brief(['topic' => 'How to recruit and retain event volunteers', 'primary_query' => 'recruit and retain event volunteers', 'closest_existing' => [], 'what_this_adds' => 'More.']);
        $this->model(['plan' => $repeat]);

        $written = app(BlogWriter::class)->write();

        $this->assertNull($written['post']);
        $this->assertSame('the planner returned no topic', $written['error']);
        $this->assertCount(2, $this->asked['plan'], 'the planner gets one more try, then the day is skipped');
        $this->assertStringContainsString('is the same post as "5 Ways to Recruit & Retain Event Volunteers"', $this->asked['plan'][1]['prompt']);
        $this->assertArrayNotHasKey('draft', $this->asked, 'a seventh post about volunteers was drafted');

        // What the planner is handed: every title, the subjects that are full, the searches the
        // product's own pages answer, and no example title to copy.
        $prompt = $this->asked['plan'][0]['prompt'];
        $this->assertStringContainsString('EVERY PUBLISHED TITLE (1)', $prompt);
        $this->assertStringContainsString('Eventbrite alternative', $prompt, 'the planner cannot tell which searches product pages own');
        $this->assertStringNotContainsString('5 Ways to Boost', $this->asked['plan'][0]['options']['system_instruction']);
    }

    public function test_the_six_posts_about_volunteers_are_one_subject(): void
    {
        $live = [
            'Empower Volunteers: Optimize Recruitment & Retention for Events',
            'Boost Event Success: Recruit, Train & Retain Volunteers',
            '5 Ways to Recruit & Retain Event Volunteers',
        ];

        // similar_text() on titles, the old check, calls none of these a repeat of the others.
        $this->assertSame($live[2], BlogGate::repeats('How to recruit and retain event volunteers', 'recruit retain event volunteers', $live) ? $live[2] : null);
        $this->assertNull(BlogGate::repeats('How to price door tickets for a small night', 'door ticket prices', $live));

        // A subject is full once enough titles are on it (config/blog.php).
        foreach (array_slice($live, 0, 2) as $title) {
            BlogPost::create(['title' => $title, 'content' => '<p>x</p>', 'is_published' => true, 'published_at' => now()->subMonth()]);
        }
        $this->model(['plan' => null]);
        app(BlogWriter::class)->plan();

        $this->assertStringContainsString('- volunteers (2 posts)', $this->asked['plan'][0]['prompt']);
    }

    public function test_the_writer_is_handed_the_facts_the_prices_the_guide_and_the_links(): void
    {
        $this->model(['brief' => $this->brief(['capabilities' => ['[passes]', 'Paid-Tickets', 'not-a-fact']]), 'draft' => $this->goodPost(), 'edit' => $this->goodPost()]);

        app(BlogWriter::class)->write(['topic' => 'Selling a class pass']);
        $draft = $this->asked['draft'][0];

        // What the product does, by plan, at this install's own prices.
        $this->assertStringContainsString('[paid-tickets] Selling tickets that have a price', $draft['prompt']);
        $this->assertStringContainsString('On the Pro plan and above (not on Free)', $draft['prompt']);
        $this->assertStringContainsString(plan_price(PlatformPricing::proMonthly()).' a month', $draft['prompt']);
        $this->assertStringNotContainsString(':pro_monthly', $draft['prompt']);

        // The user guide's own words for the facts the brief named, labels and all. Without them
        // the model wrote "Navigate to the Passes section, click Create New Pass".
        $this->assertStringContainsString('from the guide page '.BlogLinks::base().'/docs/subscriptions', $draft['prompt']);
        $this->assertStringContainsString('This is a pass or subscription (multi-use)', $draft['prompt']);
        $this->assertStringContainsString('Facts the planner thinks apply: passes, paid-tickets', $draft['prompt'], 'ids are cleaned and the unknown one dropped');

        // The pages it may link, each with what it is about, and the date.
        $this->assertStringContainsString(BlogLinks::base().'/features/passes  |  class packs and memberships', $draft['prompt']);
        $this->assertStringContainsString('Today is '.now()->format('j F Y'), $draft['prompt']);

        // A system instruction, a schema and a temperature: the old call had none of the three.
        $this->assertStringContainsString('never a category', $draft['options']['system_instruction']);
        $this->assertSame('OBJECT', $draft['options']['generation_config']['response_schema']['type']);
        $this->assertSame(0.7, $draft['options']['generation_config']['temperature']);
        $this->assertStringContainsString('when in doubt, cut', $this->asked['edit'][0]['options']['system_instruction']);
    }

    public function test_an_audience_post_keeps_its_address_its_section_and_links_its_own_page(): void
    {
        $audience = ['slug' => 'for-heritage-sites', 'name' => 'Heritage Sites & Historic Houses', 'page' => 'for-museums', 'title' => 'Museums', 'features' => ['Ticket sales', 'Recurring events']];
        $parent = BlogLinks::base().'/for-museums';

        // Without its audience's page among the links, it is held.
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $this->goodPost(), 'edit-again' => $this->goodPost()]);
        $written = app(BlogWriter::class)->write(['topic' => 'Tours and passes on one schedule', 'audience' => $audience]);
        $this->assertStringContainsString('does not link '.$parent, implode(' | ', $written['failures']));
        $this->assertStringContainsString('One of them must be '.$parent.'.', $this->asked['draft'][0]['prompt']);
        $this->assertStringContainsString('(the facts paid-tickets, recurring)', $this->asked['brief'][0]['prompt'], 'the card labels reach the model as bare words');

        // With it, it is published under the configured address, in the audience section.
        $linked = $this->goodPost(['category' => 'Venues and bookings', 'content' => str_replace(BlogLinks::base().'/pricing', $parent, $this->goodPost()['content'])]);
        $this->model(['brief' => $this->brief(), 'draft' => $linked, 'edit' => $linked]);
        $writer = app(BlogWriter::class);
        $post = $writer->store($writer->write(['topic' => 'Tours and passes on one schedule', 'audience' => $audience]), BlogWriter::SOURCE_AUDIENCE, 'for-heritage-sites');

        $this->assertTrue($post->is_published);
        $this->assertSame('for-heritage-sites', $post->slug);
        $this->assertSame('by-event', $post->category, 'the model chose a section for an audience post');
    }

    public function test_the_writer_never_publishes_an_admins_post(): void
    {
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $this->goodPost()]);

        $writer = app(BlogWriter::class);
        $post = $writer->store($writer->write(['topic' => 'Pricing door tickets for a small night']), BlogWriter::SOURCE_ADMIN);

        $this->assertFalse($post->is_published, 'publishing an admin\'s post is the admin\'s decision');
        $this->assertNull($post->held_reason, 'a post that passed is not a held post');
    }

    public function test_the_daily_command_publishes_what_passes_and_says_when_it_holds(): void
    {
        // The command skips about three days in ten at random: seed a day it writes.
        for ($seed = 1; ; $seed++) {
            mt_srand($seed);
            if (rand(1, 100) <= 70) {
                break;
            }
        }

        $plan = $this->brief(['topic' => 'Pricing door tickets for a small night', 'closest_existing' => [], 'what_this_adds' => 'Numbers.']);
        $this->model(['plan' => $plan, 'draft' => $this->goodPost(), 'edit' => $this->goodPost()]);

        mt_srand($seed);
        $this->artisan('app:generate-daily-blog-post')->expectsOutputToContain('Created blog post: How to Price Door Tickets for a Small Night')->assertExitCode(0);

        $this->assertSame(1, BlogPost::published()->count());
        $this->assertFalse(BlogWriter::busy(), 'the writer was left claimed');

        // The next run sees that post and writes nothing, as before.
        $this->artisan('app:generate-daily-blog-post')->expectsOutputToContain('already published in the last day')->assertExitCode(0);
        $this->assertSame(1, BlogPost::count());
    }

    public function test_two_generators_cannot_write_at_once(): void
    {
        for ($seed = 1; ; $seed++) {
            mt_srand($seed);
            if (rand(1, 100) <= 70) {
                break;
            }
        }

        $this->model([]);
        $this->assertTrue(BlogWriter::claim());

        mt_srand($seed);
        $this->artisan('app:generate-daily-blog-post')->expectsOutputToContain('Another blog post is being written')->assertExitCode(0);

        $this->assertSame([], $this->asked, 'the model was asked while another post was being written');
    }

    public function test_the_admins_generate_button_runs_one_step_a_request(): void
    {
        $admin = User::factory()->create();
        DB::table('users')->where('id', $admin->id)->update(['is_admin' => 1]);
        $admin->refresh();

        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $this->goodPost(['title' => 'Mastering Door Prices for a Small Night'])]);
        $as = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
        $url = route('blog.generate-content');

        $this->postJson($url, ['topic' => 'Door prices'])->assertUnauthorized();

        $brief = $as()->postJson($url, ['topic' => 'Door prices', 'step' => 'brief'])->assertOk()->json('brief');
        $this->assertSame('how to price door tickets', $brief['primary_query']);

        $draft = $as()->postJson($url, ['topic' => 'Door prices', 'step' => 'draft', 'brief' => $brief])->assertOk()->json('draft');
        $this->assertStringContainsString('<h2>What to charge on the door</h2>', $draft['content']);

        $done = $as()->postJson($url, ['topic' => 'Door prices', 'step' => 'edit', 'brief' => $brief, 'draft' => $draft])->assertOk()->json();

        $this->assertSame('selling-tickets', $done['category']);
        $this->assertSame('Can the door price change?', $done['faq'][0]['question']);
        $this->assertStringContainsString('the title opens on a stock word', implode(' | ', $done['failures']), 'the admin is not shown what the check made of it');
        $this->assertSame(0, BlogPost::count(), 'the button saved a post: saving is the form\'s job');

        // Each step is its own request, so a step asked for without the one before it is refused.
        $as()->postJson($url, ['topic' => 'Door prices', 'step' => 'draft'])->assertStatus(500);
    }

    public function test_the_facts_reach_a_model_that_takes_no_system_message(): void
    {
        // The OpenAI path sends one message, so the system instruction rides at its head there;
        // on Gemini it is its own field. Either way a fake sees it in the options.
        $this->model(['brief' => $this->brief(), 'draft' => $this->goodPost(), 'edit' => $this->goodPost()]);

        app(BlogWriter::class)->write(['topic' => 'Pricing door tickets for a small night']);

        foreach (['brief', 'draft', 'edit'] as $stage) {
            $this->assertNotSame('', trim($this->asked[$stage][0]['options']['system_instruction']), $stage.' was asked without its instruction');
            $this->assertStringContainsString('[fees]', $stage === 'brief' ? $this->asked[$stage][0]['prompt'] : $this->asked[$stage][0]['prompt'], $stage.' was asked without the facts');
        }

        $this->assertNotContains('volunteer scheduling', BlogFacts::ids());
    }
}
