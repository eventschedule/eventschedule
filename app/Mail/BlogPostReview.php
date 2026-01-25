<?php

namespace App\Mail;

use App\Models\BlogPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BlogPostReview extends Mailable
{
    use Queueable, SerializesModels;

    protected BlogPost $blogPost;

    /**
     * Create a new message instance.
     */
    public function __construct(BlogPost $blogPost)
    {
        $this->blogPost = $blogPost;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Auto-Generated] New Blog Post: '.$this->blogPost->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate signed URL that expires in 7 days
        $deleteUrl = URL::temporarySignedRoute(
            'blog.destroy.signed',
            now()->addDays(7),
            ['blogPost' => $this->blogPost->id]
        );

        $viewUrl = blog_url('/'.$this->blogPost->slug);
        $editUrl = route('blog.edit', ['blogPost' => $this->blogPost->id]);

        // Truncate content for email
        $truncatedContent = mb_substr(strip_tags($this->blogPost->content), 0, 1000);
        if (mb_strlen(strip_tags($this->blogPost->content)) > 1000) {
            $truncatedContent .= '...';
        }

        return new Content(
            view: 'emails.blog_post_review',
            with: [
                'blogPost' => $this->blogPost,
                'truncatedContent' => $truncatedContent,
                'deleteUrl' => $deleteUrl,
                'viewUrl' => $viewUrl,
                'editUrl' => $editUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
