<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $problems = [
            [
                'icon'        => '📄',
                'title'       => 'Messy, Static PDFs',
                'description' => 'Generic PDF proposals are hard to update, impossible to track, and leave clients underwhelmed — costing you deals before they even start.',
            ],
            [
                'icon'        => '⏱️',
                'title'       => 'Hours Wasted Every Proposal',
                'description' => 'Manually formatting Word docs, juggling pricing tables in spreadsheets, and copy-pasting content eats up your most valuable resource: time.',
            ],
            [
                'icon'        => '📉',
                'title'       => 'Low Client Engagement',
                'description' => 'Clients ghost your proposals. You have no idea if they opened it, spent 30 seconds or 30 minutes — leaving you to follow up blindly.',
            ],
        ];

        $features = [
            [
                'icon'        => '🖱️',
                'title'       => 'Drag-and-Drop Editor',
                'description' => 'Intuitive visual editor that lets you build beautiful proposals section by section. Rearrange blocks, swap content, and see changes instantly — no code required.',
            ],
            [
                'icon'        => '🎨',
                'title'       => 'Professional Templates',
                'description' => '50+ industry-specific templates designed by professionals. Agency, freelance, SaaS, consulting — pick a template and make it yours in seconds.',
            ],
            [
                'icon'        => '💰',
                'title'       => 'Smart Pricing Tables',
                'description' => 'Build dynamic pricing tables with optional line items, quantity inputs, and automatic totals. Let clients customize their own package right in the proposal.',
            ],
            [
                'icon'        => '📊',
                'title'       => 'Real-Time Client Tracking',
                'description' => 'Get instant notifications when a client opens your proposal. See exactly which sections they read, how long they spent, and what caught their attention.',
            ],
            [
                'icon'        => '📥',
                'title'       => 'Export to PDF',
                'description' => 'Generate pixel-perfect, print-ready PDF exports with one click. Maintain your brand formatting whether clients view online or print a copy for the boardroom.',
            ],
            [
                'icon'        => '✍️',
                'title'       => 'E-Signature Support',
                'description' => 'Close deals instantly with legally-binding e-signatures built into every proposal. No more back-and-forth emails or third-party signing tools needed.',
            ],
        ];

        $steps = [
            [
                'title'       => 'Choose a Template',
                'description' => 'Browse 50+ polished, industry-specific templates and select one that fits your brand and client.',
            ],
            [
                'title'       => 'Customize Your Proposal',
                'description' => 'Add your content, pricing, and branding with our intuitive drag-and-drop editor. It only takes minutes.',
            ],
            [
                'title'       => 'Send, Track & Sign',
                'description' => 'Share a beautiful web link, watch engagement in real time, and collect e-signatures — all in one place.',
            ],
        ];

        $plans = PricingPlan::active()->with('features')->get();

        $testimonials = [
            [
                'quote'        => 'ProposalCraft completely transformed how we pitch clients. Our close rate went from 35% to over 60% in just two months. The tracking feature alone is worth every cent.',
                'name'         => 'Sarah Mitchell',
                'role'         => 'Creative Director, Vantage Studio',
                'avatar_bg'    => 'var(--accent-dim)',
                'avatar_color' => null,
            ],
            [
                'quote'        => 'I used to spend 3 hours on every proposal. Now it takes 20 minutes and looks 10× better. Clients always mention how professional everything looks before we even get on a call.',
                'name'         => 'James Park',
                'role'         => 'Freelance Brand Strategist',
                'avatar_bg'    => 'var(--gold-dim)',
                'avatar_color' => 'var(--gold)',
            ],
            [
                'quote'        => 'The e-signature feature is a game changer. We close deals the same day we send proposals now. No more printing, scanning, emailing back and forth. Absolute must-have tool.',
                'name'         => 'Amir Rashidi',
                'role'         => 'CEO, Nexus Digital Agency',
                'avatar_bg'    => '#F0F7EE',
                'avatar_color' => '#2D9C5F',
            ],
            [
                'quote'        => 'We\'ve tried five different proposal tools. ProposalCraft is the only one that actually feels intuitive. The templates are gorgeous and the team loves using it every day.',
                'name'         => 'Tanya Cole',
                'role'         => 'Operations Manager, Pivot Consulting',
                'avatar_bg'    => '#F5F0FF',
                'avatar_color' => '#9B4DCA',
            ],
            [
                'quote'        => 'Real-time tracking changed how I follow up with clients. I know exactly when to call — right after they\'ve read the proposal. My conversion rate has never been higher.',
                'name'         => 'Marco Ferretti',
                'role'         => 'Independent Business Consultant',
                'avatar_bg'    => '#FFF0F0',
                'avatar_color' => '#E85454',
            ],
            [
                'quote'        => 'As a solo designer, looking professional is everything. ProposalCraft makes me look like a full agency. My proposals have won me contracts I never thought possible before.',
                'name'         => 'Lily Nguyen',
                'role'         => 'UX Designer & Consultant',
                'avatar_bg'    => 'var(--accent-dim)',
                'avatar_color' => 'var(--accent)',
            ],
        ];

        $faqs = [
            [
                'q' => 'Do I need a credit card to start the free trial?',
                'a' => 'No, absolutely not. You can start your 14-day free trial with just your email address. No credit card is required, and we won\'t ask for one until you decide to upgrade to a paid plan. Your trial includes full access to all Pro features.',
            ],
            [
                'q' => 'Can I use my own branding and logo?',
                'a' => 'Yes. From the Pro plan onward, you can add your own logo, brand colors, and fonts to every proposal. On the Business plan, you can also use a custom domain so clients see your brand, not ours, throughout the entire proposal experience.',
            ],
            [
                'q' => 'Are the e-signatures legally binding?',
                'a' => 'Yes. ProposalCraft e-signatures comply with major electronic signature laws including eIDAS (EU), ESIGN Act (US), and UETA. Each signature comes with a full audit trail including timestamp, IP address, and signer email for your records.',
            ],
            [
                'q' => 'Can multiple team members work on the same proposal?',
                'a' => 'Collaboration is available on the Business plan, which supports up to 10 team members. Team members can co-edit proposals in real-time, leave internal comments, and have role-based access to control who can send or sign proposals.',
            ],
            [
                'q' => 'What integrations do you support?',
                'a' => 'ProposalCraft integrates with popular CRMs and tools including HubSpot, Salesforce, Pipedrive, Slack, Zapier, and Stripe. Business plan customers also get access to our REST API for custom integrations. More integrations are added regularly.',
            ],
            [
                'q' => 'Can I cancel my subscription at any time?',
                'a' => 'Yes, absolutely. You can cancel your subscription at any time from your account settings, with no cancellation fees. You\'ll continue to have access to your plan until the end of your current billing period. We also offer a 30-day money-back guarantee on all paid plans.',
            ],
        ];

        return view('frontend.index', compact(
            'problems',
            'features',
            'steps',
            'plans',
            'testimonials',
            'faqs'
        ));
    }

    public function socialProof()
    {
        return view('frontend.pages.social-proof');
    }

    public function about()
    {
        return view('frontend.pages.about-us');
    }

    public function templatesIndex()
    {
        return view('frontend.pages.templates');
    }

    public function comparison()
    {
        return view('frontend.pages.comparison');
    }

    public function privacy()
    {
        return view('frontend.pages.privacy');
    }

    public function terms()
    {
        return view('frontend.pages.terms');
    }

    public function cookies()
    {
        return view('frontend.pages.cookies');
    }

    public function security()
    {
        return view('frontend.pages.security');
    }

}
