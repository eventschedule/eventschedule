<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketingController extends Controller
{
    /**
     * Home page
     */
    public function index()
    {
        return view('marketing.index', [
            'personas' => $this->getPersonas(),
            'steps' => $this->getSteps(),
        ]);
    }

    /**
     * Features page
     */
    public function features()
    {
        return view('marketing.features', [
            'features' => $this->getFeatures(),
        ]);
    }

    /**
     * Pricing page
     */
    public function pricing()
    {
        return view('marketing.pricing', [
            'plans' => $this->getPlans(),
        ]);
    }

    /**
     * About page
     */
    public function about()
    {
        return view('marketing.about');
    }

    /**
     * Ticketing page
     */
    public function ticketing()
    {
        return view('marketing.ticketing', [
            'ticketFeatures' => $this->getTicketFeatures(),
        ]);
    }

    /**
     * Privacy Policy page
     */
    public function privacy()
    {
        return view('marketing.privacy');
    }

    /**
     * Terms of Service page
     */
    public function terms()
    {
        return view('marketing.terms');
    }

    /**
     * Get user personas data
     */
    protected function getPersonas(): array
    {
        return [
            [
                'title' => 'Talent',
                'icon' => 'microphone',
                'description' => 'Musicians, DJs, performers, and artists who want to share their upcoming shows and build their audience.',
            ],
            [
                'title' => 'Venue',
                'icon' => 'building',
                'description' => 'Bars, clubs, theaters, and event spaces that host regular events and need to keep their calendar updated.',
            ],
            [
                'title' => 'Curator',
                'icon' => 'calendar',
                'description' => 'Event promoters, bloggers, and community organizers who aggregate and share events from multiple sources.',
            ],
            [
                'title' => 'Vendor',
                'icon' => 'store',
                'description' => 'Food trucks, market vendors, and mobile businesses that appear at different locations on different days.',
            ],
        ];
    }

    /**
     * Get 3-step process data
     */
    protected function getSteps(): array
    {
        return [
            [
                'number' => '1',
                'title' => 'Create Your Schedule',
                'description' => 'Sign up and add your events in seconds. Import from other calendars or add them manually.',
            ],
            [
                'number' => '2',
                'title' => 'Share Your Link',
                'description' => 'Get a custom URL for your schedule. Share it on social media, your website, or anywhere else.',
            ],
            [
                'number' => '3',
                'title' => 'Grow Your Audience',
                'description' => 'Fans can follow your schedule and get notified about new events. Build your community effortlessly.',
            ],
        ];
    }

    /**
     * Get features data
     */
    protected function getFeatures(): array
    {
        return [
            [
                'title' => 'Event Scheduling',
                'icon' => 'calendar',
                'description' => 'Create and manage events with support for recurring schedules, multiple dates, and time zones.',
                'details' => [
                    'One-time and recurring events',
                    'Multiple date support',
                    'Automatic timezone handling',
                    'Import from Google Calendar',
                ],
            ],
            [
                'title' => 'Ticketing & Payments',
                'icon' => 'ticket',
                'description' => 'Sell tickets directly through your schedule with integrated payment processing.',
                'details' => [
                    'Multiple ticket types',
                    'QR code tickets',
                    'Stripe integration',
                    'Real-time sales tracking',
                ],
            ],
            [
                'title' => 'Mobile Optimized',
                'icon' => 'phone',
                'description' => 'Your schedule looks great on any device. Fans can browse and buy tickets on the go.',
                'details' => [
                    'Responsive design',
                    'Fast loading',
                    'Touch-friendly interface',
                    'Works offline',
                ],
            ],
            [
                'title' => 'Team Collaboration',
                'icon' => 'users',
                'description' => 'Invite team members to help manage your schedule. Control who can add and edit events.',
                'details' => [
                    'Multiple team members',
                    'Role-based permissions',
                    'Activity tracking',
                    'Email notifications',
                ],
            ],
            [
                'title' => 'Custom Branding',
                'icon' => 'palette',
                'description' => 'Make your schedule match your brand with custom colors, logos, and domains.',
                'details' => [
                    'Custom colors and fonts',
                    'Logo upload',
                    'Custom domain support',
                    'Remove branding (Pro)',
                ],
            ],
            [
                'title' => 'Analytics & Insights',
                'icon' => 'chart',
                'description' => 'Track views, followers, and ticket sales. Understand your audience better.',
                'details' => [
                    'View tracking',
                    'Follower growth',
                    'Sales reports',
                    'Export data',
                ],
            ],
        ];
    }

    /**
     * Get pricing plans data
     */
    protected function getPlans(): array
    {
        return [
            [
                'name' => 'Free',
                'price' => '$0',
                'period' => 'forever',
                'description' => 'Everything you need to get started',
                'features' => [
                    'Unlimited events',
                    'Custom subdomain',
                    'Mobile-friendly schedule',
                    'Basic analytics',
                    'Email support',
                    'Follower notifications',
                ],
                'cta' => 'Get Started',
                'highlighted' => false,
            ],
            [
                'name' => 'Pro',
                'price' => '$5',
                'period' => '/month',
                'description' => 'First year free, then $5/month',
                'features' => [
                    'Everything in Free, plus:',
                    'Custom domain',
                    'Remove Event Schedule branding',
                    'Priority support',
                    'Advanced analytics',
                    'Custom CSS styling',
                    'API access',
                ],
                'cta' => 'Start Free Trial',
                'highlighted' => true,
            ],
        ];
    }

    /**
     * Get ticketing features data
     */
    protected function getTicketFeatures(): array
    {
        return [
            [
                'title' => 'QR Code Tickets',
                'icon' => 'qrcode',
                'description' => 'Every ticket includes a unique QR code for quick and easy check-in at your event.',
            ],
            [
                'title' => 'Multiple Ticket Types',
                'icon' => 'layers',
                'description' => 'Create different ticket tiers: general admission, VIP, early bird, and more.',
            ],
            [
                'title' => 'Secure Payments',
                'icon' => 'shield',
                'description' => 'Accept credit cards securely with Stripe. Funds go directly to your account.',
            ],
            [
                'title' => 'Real-time Sales',
                'icon' => 'chart',
                'description' => 'Track ticket sales as they happen. See who bought tickets and manage attendees.',
            ],
            [
                'title' => 'Mobile Scanner',
                'icon' => 'scan',
                'description' => 'Use your phone to scan tickets at the door. No special hardware required.',
            ],
            [
                'title' => 'Automatic Emails',
                'icon' => 'mail',
                'description' => 'Buyers receive confirmation emails with their tickets automatically.',
            ],
        ];
    }
}
