<?php

return [
    'event_details' => [
        'base' => "You are writing content for an event on an event schedule platform. Generate the requested fields for an event with these details:\n\n- Event name: :event_name\n- Schedule name: :schedule_name\n- Schedule type: :schedule_type (talent = performer/artist, venue = location/place, curator = event organizer)\n- Existing short description: :short_description",
        'existing_description_line' => '- Existing description: :description',
        'return_instruction' => "\nReturn a JSON object with only the requested fields:\n",
        'elements' => [
            'category_id' => "- \"category_id\": Choose the single best-fitting category ID from this list:\n1=Art & Culture, 2=Business Networking, 3=Community, 4=Concerts, 5=Education,\n6=Food & Drink, 7=Health & Fitness, 8=Parties & Festivals, 9=Personal Growth,\n10=Sports, 11=Spirituality, 12=Tech\nReturn just the integer ID.\n",
            'short_description' => "- \"short_description\": Write a concise, engaging summary in under 150 characters.\n",
            'description_new' => "- \"description\": Write an engaging description in markdown. Use rich formatting: include a few subheadings (##), bullet or numbered lists where appropriate, and bold text for emphasis. Include a few relevant emojis to make it visually appealing. Describe what attendees can expect. Do not use em-dashes. Do not include the event name as a top-level heading. Keep it concise, around 150 to 300 words.\n",
            'description_existing' => "- \"description\": Improve and enhance the existing description rather than writing from scratch. Use rich markdown formatting: include a few subheadings (##), bullet or numbered lists where appropriate, and bold text for emphasis. Include a few relevant emojis to make it visually appealing. Do not use em-dashes. Do not include the event name as a top-level heading. Keep it concise, around 150 to 300 words.\n",
        ],
        'short_description_first' => "\nSince no short description exists yet, generate the short_description first and use it as context when writing the description.",
    ],

    'schedule_details' => [
        'base' => "You are writing content for an event schedule platform. Generate the requested fields for a schedule with these details:\n\n- Schedule name: :name\n- Schedule type: :schedule_type (talent = performer/artist, venue = location/place, curator = event organizer)\n- Existing short description: :short_description",
        'existing_description_line' => '- Existing description: :description',
        'return_instruction' => "\nReturn a JSON object with only the requested fields:\n",
        'elements' => [
            'short_description' => "- \"short_description\": Write a concise, engaging summary in under 150 characters. Capture what makes this schedule unique.\n",
            'description_new' => "- \"description\": Write an engaging description in markdown. Use rich formatting: include a few subheadings (##), bullet or numbered lists where appropriate, and bold text for emphasis. Include a few relevant emojis to make it visually appealing. Describe what visitors can expect. Do not use em-dashes. Do not include the schedule name as a top-level heading. Keep it concise, around 150 to 300 words.\n",
            'description_existing' => "- \"description\": Improve and enhance the existing description rather than writing from scratch. Use rich markdown formatting: include a few subheadings (##), bullet or numbered lists where appropriate, and bold text for emphasis. Include a few relevant emojis to make it visually appealing. Do not use em-dashes. Do not include the schedule name as a top-level heading. Keep it concise, around 150 to 300 words.\n",
        ],
        'short_description_first' => "\nSince no short description exists yet, generate the short_description first and use it as context when writing the description.",
    ],

    'schedule_style' => [
        'base' => "You are a branding expert. Generate style properties for a schedule called ':name'.\nSchedule type: :schedule_type",
        'description_line' => "\nDescription: :description",
        'categories_line' => "\nEvent categories: :categories",
        'existing_accent_color' => "\nThe schedule already uses accent color :accent_color - ensure your choices complement it.",
        'existing_font' => "\nThe schedule already uses the font ':font_family' - ensure your choices complement it.",
        'return_instruction' => "\n\nReturn a JSON object with ONLY the requested fields:\n",
        'elements' => [
            'accent_color' => "- \"accent_color\": a hex color code (e.g. \"#4E81FA\") that fits the schedule's theme and type. Choose a vibrant, professional color.\n",
            'font_family' => "- \"font_family\": choose ONE font from this exact list: :font_list. Pick a font that matches the schedule's personality and type.\n",
        ],
        'style_preferences' => "\nUser's style preferences: :instructions",
    ],

    'event_flyer' => [
        'intro' => "Create a professional event flyer/poster. Every piece of text must exactly match the details provided below.\n\nEVENT DETAILS:\nEvent name: :event_name\n",
        'layout' => "\nLAYOUT (top to bottom):\n- Top third: event name in large, bold type (at least 3x larger than body text).\n- Middle: date, time, and venue in medium type.\n- Bottom third: remaining details (description, performers, ticket price) in smaller type.\n",
        'design' => "\nDESIGN DIRECTIVES:\n- Background: rich color or gradient inspired by the event category:category_hint. Not plain white.\n- Use category-appropriate decorative elements (abstract shapes, icons) to set the mood.\n- All text must be clearly legible with strong contrast against the background.\n- Professional typography with generous spacing.\n- Do not include AI-generated photos of people.\n- Suitable for digital sharing on social media.\n",
        'style_instructions' => "\nCustom style instructions: :instructions\n",
    ],

    'profile_image' => [
        'intro' => "Create a flat vector illustration with clean geometric shapes and smooth gradients for a :type schedule called ':name'.",
        'description' => ' Description: :description.',
        'body' => "\n\nVisual mood: :mood.\nComposition: one strong central motif that fills most of the canvas. High contrast between the main element and the background so the image reads clearly at small sizes (as small as 32px).\nSuggested motifs: :motifs.",
        'accents' => "\nCategory-inspired accents: :accents.",
        'color' => "\nColor palette: use :accent_color as the dominant color with 2 to 3 tonal variations and one contrasting accent for depth.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- Full bleed: the design extends to every edge of the canvas with no padding, margins, or empty space.\n- No border, outline, frame, or rounded corners on the image.\n- Do not center a small element in the middle of empty space.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
    ],

    'header_image' => [
        'intro' => "Create a smooth abstract illustration with flowing gradients and layered geometric shapes for a wide banner image. This is for a :type schedule called ':name'.",
        'description' => ' Description: :description.',
        'body' => "\n\nVisual mood: :mood.\nComposition: flows horizontally from left to right. Visual weight toward the left and right edges, with the center area simpler and lighter so overlaid text remains legible.\nSuggested motifs: :motifs.",
        'accents' => "\nCategory-inspired accents: :accents.",
        'color' => "\nColor palette: use :accent_color as the base, shifting subtly across the width through 2 to 3 related tones.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- Full bleed with no padding or borders.\n- Professional and modern.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
    ],

    'background_image' => [
        'intro' => "Create a soft watercolor wash with faint geometric line work as a background image for a :type schedule called ':name'.",
        'body' => "\n\nPurpose: this image sits behind text and event listings, so it must be very subtle and non-distracting.\nComposition: even and uniform across the entire canvas. No strong focal point. No area should be dramatically darker or lighter than another.\nColor palette: very pale, washed-out tints of :accent_color at about 15 to 25 percent opacity. Soft, muted tones that will not compete with foreground content.",
        'motifs' => "\nOptional hint of motifs (like a watermark, barely visible): :motifs.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- Must be subtle enough that black or dark text is easily readable over every part of the image.\n- No bold shapes, no high-contrast elements, no vivid colors.\n- Full bleed with no padding or borders.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
    ],

    'event_parse' => [
        'base' => "Parse the event details from this :source message to the following fields, take your time and do the best job possible:\n",
        'footer' => "\nThe date today is :today.\nThe event date is either :this_month or :next_month.\nIf there is no time, use 8pm as the default time.\nIf there are multiple performers create multiple events.",
    ],

    'event_parts' => [
        'base' => "Extract the agenda items, schedule parts, or setlist songs from this :source.\n\nReturn a JSON array of objects, each with these keys:\n- name: the title or name of the part/song/session (required)\n- description: optional details or speaker name\n- start_time: in HH:MM 24-hour format, or null if no times are shown\n- end_time: in HH:MM 24-hour format, or null if no times are shown\n\nIf the content is a setlist or numbered list without times, set start_time and end_time to null for all items.\nPreserve the original order. Return only the JSON array.\n",
        'additional_instructions' => "\nAdditional instructions: :instructions\n",
        'text_section' => "\nText:\n:text",
    ],

    'translate' => [
        'base' => 'Translate this text from :from to :to.:glossary Return only the translation as a JSON string:',
        'glossary_header' => " Use these exact translations for the following terms:\n",
        'glossary_line' => '- ":original" => ":translation"',
    ],

    'translate_group_names' => [
        'base' => "Translate these group names from :from to English. Return a JSON object where each key is the original name and the value is the English translation:\n:names",
    ],

    'translate_custom_field_names' => [
        'base' => "Translate these form field names from :from to English. Return a JSON object where each key is the original name and the value is the English translation:\n:names",
    ],

    'translate_custom_field_options' => [
        'base' => "Translate these dropdown option values from :from to English. Return a JSON object where each key is the original value and the value is the English translation:\n:values",
    ],

    'blog_post' => [
        'base' => "Generate a blog post about ':topic' with the following specifications:

        Tone: professional
        Length: :length (short: 300-500 words, medium: 800-1200 words, long: 1500-2000 words)

        Please return a JSON object with the following structure:
        {
            \"title\": \"SEO-optimized title (50-60 characters)\",
            \"content\": \"HTML formatted content with proper headings (h1, h2, h3), paragraphs, and lists. Include practical tips, examples, and actionable advice.\",
            \"excerpt\": \"Brief summary (150-160 characters)\",
            \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"],
            \"meta_title\": \"SEO meta title (50-60 characters)\",
            \"meta_description\": \"SEO meta description (150-160 characters)\",
            \"image_category\": \"business|wellness|sports|music|networking|family|productivity|nature|arts|general\"
        }

        Requirements:
        - Try not to seem like a robot
        - Never use em dashes
        - Make the content engaging and informative
        - Include practical tips and actionable advice
        - Use proper HTML formatting with h1, h2, h3 tags
        - Include bullet points and numbered lists where appropriate
        - Make it relevant to event scheduling and ticketing
        :features_requirement
        - Ensure the content is original and valuable
        - Use natural, search-friendly language in the title and headings (the kind of phrasing people type into search engines)
        - Make it SEO-friendly with relevant keywords
        - Always maintain a professional tone
        :links_requirement
        - For image_category, choose the most appropriate category based on the topic:
          * business: for business, professional, corporate topics
          * wellness: for health, wellness, meditation, yoga topics
          * sports: for sports, fitness, athletics topics
          * music: for music, arts, entertainment topics
          * networking: for networking, social, community topics
          * family: for family, children, parenting topics
          * productivity: for productivity, time management, organization topics
          * nature: for outdoor, nature, environmental topics
          * arts: for creative, artistic, cultural topics
          * general: for general topics that don't fit other categories",
        'links_with_parent' => "- IMPORTANT: You MUST include exactly 2 internal links in the content:
          1. One link to 'https://www.eventschedule.com/:parent_url' with text like 'Event Schedule for :parent_title' or similar contextual text
          2. One link to 'https://www.eventschedule.com' with 'Event Schedule' as the link text
        - Place these links naturally within the content where they add value
        - Use proper HTML anchor tags: <a href=\"URL\">text</a>",
        'links_without_parent' => "- Add 2 links in the text where relevant to 'https://www.eventschedule.com' with 'Event Schedule' as the text
        - Place these links naturally within the content where they add value",
        'features_line' => "- The blog post should naturally mention these Event Schedule features: :features. Weave them into the content as practical recommendations, not as a feature list.\n        ",
    ],

    'blog_topic' => [
        'base' => 'You are a content strategist for Event Schedule, an event management platform.

Recent blog post titles:
- :titles

Suggest ONE new blog post topic that:
1. :style
2. Is different from the recent posts listed above
3. Would be interesting to event organizers and community managers

Return a JSON object with just one field:
{"topic": "your topic phrase here (5-15 words)"}',
    ],
];
