<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\User;

// database/seeders/BlogSeeder.php

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ────────────────────────────────────────
        BlogCategory::upsert([
            ['name' => 'Proposal Tips',  'slug' => 'proposal-tips',  'color' => '#1A56F0', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Templates',      'slug' => 'templates',      'color' => '#0DBD7F', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Freelance',      'slug' => 'freelance',      'color' => '#E8A838', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agency Growth',  'slug' => 'agency-growth',  'color' => '#F04060', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Case Studies',   'slug' => 'case-studies',   'color' => '#7C3AED', 'created_at' => now(), 'updated_at' => now()],
        ], ['slug'], ['name', 'color', 'updated_at']);

        // ── Tags ──────────────────────────────────────────────
        BlogTag::upsert([
            ['name' => 'Pricing',       'slug' => 'pricing',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Client Tips',   'slug' => 'client-tips',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Closing Deals', 'slug' => 'closing-deals', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Productivity',  'slug' => 'productivity',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Design',        'slug' => 'design',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'E-Signature',   'slug' => 'e-signature',   'created_at' => now(), 'updated_at' => now()],
        ], ['slug'], ['name', 'updated_at']);

        $author = User::first();
        $cats   = BlogCategory::pluck('id', 'slug');
        $tags   = BlogTag::pluck('id', 'slug');

        $posts = [
            [
                'title'       => 'How to Write a Proposal That Closes in 24 Hours',
                'slug'        => 'how-to-write-proposal-closes-24-hours',
                'excerpt'     => 'Most proposals sit in inboxes for days. Here\'s the exact structure that gets a yes the same day you send it.',
                'content'     => '<p>Most proposals sit in inboxes for days. Here\'s why — and how to fix it.</p><h2>Lead with the outcome</h2><p>Clients don\'t buy services. They buy results. Start your proposal with exactly what they get, not what you do.</p><h2>Keep pricing simple</h2><p>One number. No hourly breakdowns. Confusion kills deals.</p><h2>Add a clear deadline</h2><p>Proposals without expiry dates never get accepted. Add "Valid for 7 days" and watch your close rate jump.</p>',
                'category_id' => $cats['proposal-tips'],
                'is_featured' => true,
                'status'      => 'published',
                'published_at' => now()->subDays(2),
                'tag_slugs'   => ['closing-deals', 'pricing'],
            ],
            [
                'title'       => '5 Proposal Templates Every Freelancer Needs in 2025',
                'slug'        => '5-proposal-templates-freelancers-2025',
                'excerpt'     => 'Stop starting from scratch. These five battle-tested templates cover 90% of freelance projects.',
                'content'     => '<p>The fastest way to send more proposals is to stop writing them from scratch.</p><h2>1. Web Design Proposal</h2><p>Cover discovery, design, development, and launch in four clean sections.</p><h2>2. Branding Package</h2><p>Logo, guidelines, and assets — priced as a single deliverable.</p><h2>3. Monthly Retainer</h2><p>Ongoing work structured as a predictable monthly investment.</p>',
                'category_id' => $cats['templates'],
                'is_featured' => false,
                'status'      => 'published',
                'published_at' => now()->subDays(5),
                'tag_slugs'   => ['pricing', 'productivity'],
            ],
            [
                'title'       => 'Why PDF Proposals Are Killing Your Close Rate',
                'slug'        => 'pdf-proposals-killing-close-rate',
                'excerpt'     => 'PDFs are silent. You send one, it disappears, and you never know what happened. There\'s a better way.',
                'content'     => '<p>You spend two hours writing a proposal. You export it as a PDF. You email it. Then — nothing.</p><p>Did they open it? Did they read it? Did they show it to someone else?</p><h2>The problem with PDFs</h2><p>PDFs are a black hole. Zero visibility after you hit send.</p><h2>The alternative</h2><p>Web-based proposals give you open notifications, scroll tracking, and a one-click accept button.</p>',
                'category_id' => $cats['proposal-tips'],
                'is_featured' => false,
                'status'      => 'published',
                'published_at' => now()->subDays(8),
                'tag_slugs'   => ['closing-deals', 'client-tips'],
            ],
            [
                'title'       => 'How This Agency 3x\'d Their Close Rate in 60 Days',
                'slug'        => 'agency-3x-close-rate-60-days',
                'excerpt'     => 'A 4-person design agency went from 22% to 67% close rate. Here\'s exactly what they changed.',
                'content'     => '<p>PixelForge is a 4-person design agency based in London. In January their close rate was 22%. By March it was 67%.</p><h2>What changed</h2><p>They switched from emailing PDFs to sending ProposalCraft links. They started calling clients within 5 minutes of a proposal open notification.</p><h2>The result</h2><p>More deals, shorter sales cycles, and clients who felt impressed before the project even started.</p>',
                'category_id' => $cats['case-studies'],
                'is_featured' => false,
                'status'      => 'published',
                'published_at' => now()->subDays(12),
                'tag_slugs'   => ['closing-deals', 'client-tips'],
            ],
            [
                'title'       => 'How to Price Your Freelance Services (Without Underselling)',
                'slug'        => 'how-to-price-freelance-services',
                'excerpt'     => 'Pricing is the hardest part of freelancing. Here\'s a framework that gets you paid what you\'re worth.',
                'content'     => '<p>Most freelancers underprice by 40–60%. Not because their work isn\'t worth more — because they\'re afraid.</p><h2>Value-based pricing</h2><p>Price based on the outcome you deliver, not the hours you work.</p><h2>The 3-tier proposal</h2><p>Always offer three options. Clients choose the middle one 70% of the time.</p>',
                'category_id' => $cats['freelance'],
                'is_featured' => false,
                'status'      => 'published',
                'published_at' => now()->subDays(15),
                'tag_slugs'   => ['pricing', 'client-tips'],
            ],
            [
                'title'       => 'The 10-Minute Proposal: Send Faster, Close More',
                'slug'        => '10-minute-proposal-send-faster-close-more',
                'excerpt'     => 'Speed wins deals. If you can\'t send a proposal within an hour of a call, you\'re losing to someone who can.',
                'content'     => '<p>The window between a sales call and a buying decision is short. Every hour you wait, the client cools off.</p><h2>Use templates</h2><p>90% of your proposals are the same. Build one great template and fill in the blanks.</p><h2>Pre-write your sections</h2><p>About us, process, terms — these never change. Pre-write them once.</p>',
                'category_id' => $cats['proposal-tips'],
                'is_featured' => false,
                'status'      => 'published',
                'published_at' => now()->subDays(20),
                'tag_slugs'   => ['productivity', 'closing-deals'],
            ],
        ];

        foreach ($posts as $data) {
            $tagSlugs = $data['tag_slugs'];
            unset($data['tag_slugs']);

            $data['content_html'] = $data['content'];
            $data['author_id']    = $author->id;
            $data['created_at']   = now();
            $data['updated_at']   = now();

            $post = BlogPost::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $post->tags()->syncWithoutDetaching(
                collect($tagSlugs)->map(fn($s) => $tags[$s])->toArray()
            );
        }
    }
}
