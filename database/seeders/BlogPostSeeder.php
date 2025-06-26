<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'title' => 'How to Build and Share Your Own Event Calendar',
                'content' => '<h1>Event Calendars: Keep Everyone in the Loop</h1><p>Managing events can get messy, but with Event Schedule, you can create and share beautiful calendars in just a few clicks. Whether you\'re organizing a local gig or a community meetup, your audience will always know what\'s coming up. Share your calendar link or embed it on your website—no tech skills required.</p><ul><li>Easy drag-and-drop interface</li><li>Shareable public links</li><li>Embed on any site</li></ul><p>Try it out and see how much smoother your event planning can be.</p>',
                'excerpt' => 'Create and share event calendars with ease. Keep your audience informed and never miss a beat.',
                'tags' => ['calendar', 'events', 'sharing'],
                'meta_title' => 'Create and Share Event Calendars Effortlessly',
                'meta_description' => 'Discover how to build and share event calendars with Event Schedule. Keep your audience in the loop and boost attendance.',
                'featured_image' => 'All_Hands_on_Deck.png',
            ],
            [
                'title' => 'Sell Tickets Online: A Seamless Experience for Everyone',
                'content' => '<h1>Sell Tickets Online</h1><p>Forget about clunky forms and manual tracking. With Event Schedule, selling tickets is a breeze. Set up your event, add ticket types, and let attendees check out in seconds. You\'ll get real-time sales updates and your guests get instant confirmation. No more guesswork, just smooth sales.</p><ul><li>Multiple ticket types</li><li>Instant confirmation</li><li>Easy refunds</li></ul>',
                'excerpt' => 'Offer tickets online with a seamless checkout process. Make it easy for your guests to attend.',
                'tags' => ['tickets', 'sales', 'checkout'],
                'meta_title' => 'Sell Tickets Online with Ease',
                'meta_description' => 'Learn how to sell tickets online using Event Schedule. Fast, secure, and hassle-free for both organizers and attendees.',
                'featured_image' => 'Tradeshow_Expo.png',
            ],
            [
                'title' => 'Accept Online Payments with Invoice Ninja or Payment Links',
                'content' => '<h1>Online Payments Made Simple</h1><p>Getting paid shouldn\'t be complicated. Event Schedule integrates with Invoice Ninja and supports payment links, so you can accept payments securely and quickly. Whether you\'re running a free event or charging for entry, you\'re covered.</p><ul><li>Invoice Ninja integration</li><li>Custom payment links</li><li>Secure transactions</li></ul>',
                'excerpt' => 'Accept secure online payments via Invoice Ninja or payment links. Fast, safe, and reliable.',
                'tags' => ['payments', 'invoice ninja', 'security'],
                'meta_title' => 'Online Payments with Invoice Ninja Integration',
                'meta_description' => 'Accept secure online payments for your events with Invoice Ninja or payment links. Simple and safe.',
                'featured_image' => 'Lets_do_Business.png',
            ],
            [
                'title' => 'AI Event Parsing: Let the Robots Do the Work',
                'content' => '<h1>AI Event Parsing</h1><p>Ever wish you could just paste your event details and have everything filled out for you? Now you can. Our AI-powered event parser reads your event info and sets up your event in seconds. It even works with images and supports multiple languages. Try pasting a flyer or a text blurb and watch the magic happen.</p>',
                'excerpt' => 'Automatically extract event details using AI. Save time and reduce manual entry.',
                'tags' => ['AI', 'automation', 'event parsing'],
                'meta_title' => 'AI Event Parsing for Fast Event Creation',
                'meta_description' => 'Use AI to quickly create new events by extracting details from text or images. Save time and reduce errors.',
                'featured_image' => 'Synergy.png',
            ],
            [
                'title' => 'Recurring Events: Set It and Forget It',
                'content' => '<h1>Recurring Events</h1><p>Some events happen every week, and entering them over and over is a pain. With recurring events, you can set your schedule once and let Event Schedule handle the rest. Just pick your days and times, and your calendar stays up to date automatically.</p>',
                'excerpt' => 'Schedule recurring events with ease. Perfect for weekly classes, meetings, or open mics.',
                'tags' => ['recurring', 'automation', 'calendar'],
                'meta_title' => 'Recurring Events Made Easy',
                'meta_description' => 'Schedule recurring events in a snap. Save time and keep your calendar fresh.',
                'featured_image' => 'Fitness_Morning.png',
            ],
            [
                'title' => 'QR Code Ticketing: Fast, Secure Check-Ins',
                'content' => '<h1>QR Code Ticketing</h1><p>Checking in guests shouldn\'t slow you down. Every ticket comes with a unique QR code that you can scan at the door. It\'s fast, secure, and works right from your phone or tablet. No more paper lists or confusion at the entrance.</p>',
                'excerpt' => 'Generate and scan QR codes for easy and secure event check-ins. No more paper tickets.',
                'tags' => ['QR code', 'ticketing', 'check-in'],
                'meta_title' => 'QR Code Ticketing for Events',
                'meta_description' => 'Streamline event check-ins with QR code ticketing. Fast, secure, and paperless.',
                'featured_image' => 'Networking_and_Bagels.png',
            ],
            [
                'title' => 'Sell Tickets to Online Events',
                'content' => '<h1>Support for Online Events</h1><p>Hosting a virtual event? Event Schedule lets you sell tickets to online events just as easily as in-person ones. Share your event link, collect payments, and manage attendees from anywhere.</p>',
                'excerpt' => 'Sell tickets to online events and reach a global audience. Simple and effective.',
                'tags' => ['online', 'virtual', 'tickets'],
                'meta_title' => 'Sell Tickets to Online Events',
                'meta_description' => 'Reach more people by selling tickets to online events. Manage everything in one place.',
                'featured_image' => 'Music_Potential.png',
            ],
            [
                'title' => 'Team Scheduling: Work Together, Win Together',
                'content' => '<h1>Team Scheduling</h1><p>Event planning is a team sport. Invite your team, assign roles, and coordinate schedules with ease. Everyone stays in sync, and you can manage availability in real time. No more endless email threads or double bookings.</p>',
                'excerpt' => 'Collaborate with your team to manage availability and event schedules. Stay organized.',
                'tags' => ['team', 'collaboration', 'scheduling'],
                'meta_title' => 'Team Scheduling for Events',
                'meta_description' => 'Coordinate with your team and manage event schedules together. Stay organized and efficient.',
                'featured_image' => 'People_of_the_World.png',
            ],
            [
                'title' => 'Multi-Language Support: Speak to Everyone',
                'content' => '<h1>Multi-Language Support</h1><p>Events bring people together from all over the world. With built-in multi-language support, your schedule can be viewed in several languages. Make everyone feel at home, no matter where they\'re from.</p>',
                'excerpt' => 'Provide a localized experience with support for multiple languages. Welcome everyone.',
                'tags' => ['languages', 'localization', 'international'],
                'meta_title' => 'Multi-Language Support for Events',
                'meta_description' => 'Offer your event schedule in multiple languages. Make your events accessible to all.',
                'featured_image' => 'Network_Summit.png',
            ],
            [
                'title' => 'AI Translation: Your Schedule, Any Language',
                'content' => '<h1>AI Translation</h1><p>Don\'t let language barriers slow you down. Our AI translation feature can translate your entire schedule into multiple languages in seconds. It\'s automatic, accurate, and helps you reach a wider audience.</p>',
                'excerpt' => 'Automatically translate your schedule into multiple languages using AI. Fast and accurate.',
                'tags' => ['AI', 'translation', 'languages'],
                'meta_title' => 'AI Translation for Event Schedules',
                'meta_description' => 'Translate your event schedule into any language with AI. Reach a global audience.',
                'featured_image' => 'Literature.png',
            ],
            [
                'title' => 'Multiple Ticket Types: Standard, VIP, and More',
                'content' => '<h1>Multiple Ticket Types</h1><p>Not all tickets are created equal. Offer different ticket tiers like Standard, VIP, or custom options. Give your guests choices and boost your revenue.</p>',
                'excerpt' => 'Offer different ticket tiers to meet various audience needs. Standard, VIP, and more.',
                'tags' => ['tickets', 'tiers', 'VIP'],
                'meta_title' => 'Multiple Ticket Types for Events',
                'meta_description' => 'Create multiple ticket types for your events. Standard, VIP, and custom options available.',
                'featured_image' => 'Arena.png',
            ],
            [
                'title' => 'Ticket Quantity Limits: Manage Capacity with Confidence',
                'content' => '<h1>Ticket Quantity Limits</h1><p>Worried about overselling? Set a maximum number of tickets for each event. Event Schedule tracks sales and stops selling when you hit your limit. No more awkward conversations at the door.</p>',
                'excerpt' => 'Set a maximum number of tickets for each event. Manage capacity and avoid overselling.',
                'tags' => ['tickets', 'capacity', 'limits'],
                'meta_title' => 'Ticket Quantity Limits',
                'meta_description' => 'Manage event capacity by setting ticket quantity limits. Prevent overselling and keep things smooth.',
                'featured_image' => 'Sports_Centre.png',
            ],
            [
                'title' => 'Ticket Reservation System: Hold Seats Before Purchase',
                'content' => '<h1>Ticket Reservation System</h1><p>Give your attendees the option to reserve tickets before they buy. Set a release time, and let them complete their purchase when they\'re ready. It\'s a great way to boost sales and reduce no-shows.</p>',
                'excerpt' => 'Allow attendees to reserve tickets with a configurable release time. Flexible and fair.',
                'tags' => ['reservation', 'tickets', 'sales'],
                'meta_title' => 'Ticket Reservation System',
                'meta_description' => 'Let attendees reserve tickets before purchase. Increase sales and reduce no-shows.',
                'featured_image' => 'Chill_Evening.png',
            ],
            [
                'title' => 'Calendar Integration: Add Events to Google, Apple, or Microsoft',
                'content' => '<h1>Calendar Integration</h1><p>Make it easy for your guests to remember your events. With one click, they can add events to Google, Apple, or Microsoft calendars. No more missed dates or forgotten shows.</p>',
                'excerpt' => 'Enable attendees to add events directly to their calendars. Google, Apple, Microsoft supported.',
                'tags' => ['calendar', 'integration', 'reminders'],
                'meta_title' => 'Calendar Integration for Events',
                'meta_description' => 'Let attendees add your events to their calendars. Google, Apple, and Microsoft supported.',
                'featured_image' => 'Warming_Up.png',
            ],
            [
                'title' => 'Sub-schedules: Organize Events Your Way',
                'content' => '<h1>Sub-schedules</h1><p>Big event? Lots of tracks? Use sub-schedules to organize your events into categories or tracks. Attendees can filter and find what interests them most. It\'s all about flexibility.</p>',
                'excerpt' => 'Organize events into sub-schedules for better management. Perfect for festivals and conferences.',
                'tags' => ['sub-schedules', 'organization', 'tracks'],
                'meta_title' => 'Sub-schedules for Event Organization',
                'meta_description' => 'Use sub-schedules to organize events by category or track. Flexible and attendee-friendly.',
                'featured_image' => 'Flowerful_Life.png',
            ],
            [
                'title' => 'Search Feature: Find What You Need, Fast',
                'content' => '<h1>Search Feature</h1><p>Looking for a specific event or performer? The search feature helps users find exactly what they need in seconds. No more endless scrolling—just type and go.</p>',
                'excerpt' => 'Powerful search helps users find events or content quickly. Save time and discover more.',
                'tags' => ['search', 'find', 'events'],
                'meta_title' => 'Search Feature for Events',
                'meta_description' => 'Find events or content quickly with powerful search. Save time and discover more.',
                'featured_image' => 'Chess_Vibrancy.png',
            ],
            [
                'title' => 'REST API: Take Control of Your Data',
                'content' => '<h1>REST API</h1><p>Developers, this one\'s for you. Access and manage your events programmatically with our REST API. Integrate with other tools, automate workflows, and build custom solutions on top of Event Schedule.</p>',
                'excerpt' => 'Access and manage your events programmatically through a REST API. Build your own tools.',
                'tags' => ['API', 'integration', 'developers'],
                'meta_title' => 'REST API for Event Management',
                'meta_description' => 'Use the REST API to access and manage your events. Integrate, automate, and extend.',
                'featured_image' => 'Synergy.png',
            ],
            [
                'title' => 'Automatic App Updates: Stay Current, Effortlessly',
                'content' => '<h1>Automatic App Updates</h1><p>Never worry about missing out on new features or security patches. Event Schedule updates itself with a single click. You focus on your events—we\'ll handle the rest.</p>',
                'excerpt' => 'Keep the platform up to date with one-click automatic updates. Stay secure and get new features.',
                'tags' => ['updates', 'security', 'features'],
                'meta_title' => 'Automatic App Updates',
                'meta_description' => 'Stay up to date with automatic app updates. Get new features and security patches effortlessly.',
                'featured_image' => 'Summer_Events.png',
            ],
        ];

        $images = array_keys(BlogPost::getAvailableHeaderImages());
        $authors = ['Event Schedule Team', 'Hillel', 'Alex', 'Sam', 'Jordan', 'Taylor'];
        $now = Carbon::now();

        foreach ($features as $i => $feature) {
            $publishedAt = $now->copy()->subDays(rand(1, 60))->setTime(rand(8, 20), rand(0, 59));
            $viewCount = rand(10, 200) * ($i + 1);
            $author = $authors[array_rand($authors)];
            $image = $feature['featured_image'] ?? $images[array_rand($images)];

            BlogPost::create([
                'title' => $feature['title'],
                'slug' => Str::slug($feature['title']),
                'content' => $feature['content'],
                'excerpt' => $feature['excerpt'],
                'tags' => $feature['tags'],
                'published_at' => $publishedAt,
                'meta_title' => $feature['meta_title'],
                'meta_description' => $feature['meta_description'],
                'featured_image' => $image,
                'author_name' => $author,
                'is_published' => true,
                'view_count' => $viewCount,
            ]);
        }
    }
}
