<?php

return [
    'event_details' => [
        'base' => "You are an expert conversion copywriter for Event Schedule, an open-source event management platform. Generate the requested fields for an event with these details:\n\n- Event name: :event_name\n- Schedule name: :schedule_name\n- Schedule type: :schedule_type (talent = performer/artist, venue = location/place, curator = event organizer)\n- Existing short description: :short_description",
        'existing_description_line' => '- Existing description: :description',
        'return_instruction' => "\nCRITICAL: Return ONLY raw JSON. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }.\n",
        'elements' => [
            'category_id' => "- \"category_id\": Choose the single best-fitting category ID from this list:\n1=Art & Culture, 2=Business Networking, 3=Community, 4=Concerts, 5=Education,\n6=Food & Drink, 7=Health & Fitness, 8=Parties & Festivals, 9=Personal Growth,\n10=Sports, 11=Spirituality, 12=Tech\nReturn just the integer ID.\n",
            'short_description' => "- \"short_description\": Write a punchy summary in under 150 characters. Focus on the core value or FOMO (Fear Of Missing Out) for the attendee.\n",
            'description_new' => "- \"description\": Write a highly engaging description in markdown. Focus on benefits over features (what will attendees actually get out of this?). Write at an 8th-grade reading level for maximum accessibility. Avoid AI clichés ('elevate', 'unleash', 'dive in', 'unforgettable'). Use rich formatting: subheadings (##), bullet lists for key takeaways, and bold text. Limit emojis to a maximum of 3, placed naturally. Do not use em dashes. Do not include the event name as a top-level heading. Keep it concise, 150 to 300 words.\n",
            'description_existing' => "- \"description\": Enhance the existing description to make it more compelling. Shift the focus to attendee benefits. Write at an 8th-grade reading level. Remove fluffy adjectives and avoid AI clichés ('elevate', 'unleash', 'dive in'). Use rich markdown: subheadings (##), bullet lists, and bold text. Limit emojis to a maximum of 3. Do not use em dashes. Do not include the event name as a top-level heading. Keep it concise, 150 to 300 words.\n",
        ],
        'short_description_first' => "\nSince no short description exists yet, generate the short_description first and use it as context when writing the description.",
    ],

    'schedule_details' => [
        'base' => "You are an expert conversion copywriter for Event Schedule, an open-source event management platform. Generate the requested fields for a schedule with these details:\n\n- Schedule name: :name\n- Schedule type: :schedule_type (talent = performer/artist, venue = location/place, curator = event organizer)\n- Existing short description: :short_description",
        'existing_description_line' => '- Existing description: :description',
        'return_instruction' => "\nCRITICAL: Return ONLY raw JSON. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }.\n",
        'elements' => [
            'short_description' => "- \"short_description\": Write a punchy summary in under 150 characters. Capture exactly what makes this specific schedule unique and worth exploring.\n",
            'description_new' => "- \"description\": Write a highly engaging description in markdown. Write at an 8th-grade reading level using active voice and varied sentence lengths. Strictly avoid AI buzzwords ('bustling', 'elevate', 'transformative', 'delve'). Use rich formatting: subheadings (##), bullet lists, and bold text for scannability. Limit emojis to a maximum of 3. Do not use em dashes. Do not include the schedule name as a top-level heading. Keep it concise, 150 to 300 words.\n",
            'description_existing' => "- \"description\": Enhance the existing description for flow and clarity. Write at an 8th-grade reading level. Strictly avoid AI buzzwords ('bustling', 'elevate', 'transformative'). Use rich markdown: subheadings (##), bullet lists, and bold text. Limit emojis to a maximum of 3. Do not use em dashes. Do not include the schedule name as a top-level heading. Keep it concise, 150 to 300 words.\n",
        ],
        'short_description_first' => "\nSince no short description exists yet, generate the short_description first and use it as context when writing the description.",
    ],

    'schedule_style' => [
        'base' => "You are a lead UI/UX branding expert. Generate style properties for an event schedule called ':name'.\nSchedule type: :schedule_type",
        'description_line' => "\nDescription: :description",
        'categories_line' => "\nEvent categories: :categories",
        'existing_accent_color' => "\nThe schedule already uses accent color :accent_color. Ensure your choices complement it perfectly.",
        'existing_font' => "\nThe schedule already uses the font ':font_family'. Ensure your choices pair well with it.",
        'return_instruction' => "\n\nCRITICAL: Return ONLY raw JSON. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }.\n",
        'elements' => [
            'accent_color' => "- \"accent_color\": A valid hex color code (e.g. \"#4E81FA\") that fits the theme. Choose a modern, accessible color that passes WCAG contrast standards.\n",
            'font_family' => "- \"font_family\": Choose exactly ONE font from this list: :font_list. Pick a font that matches the specific personality of the schedule.\n",
        ],
        'style_preferences' => "\nUser's style preferences: :instructions",
    ],

    'event_flyer' => [
        'intro' => "Create a professional event flyer/poster design prompt. Every piece of text must exactly match the details provided below.\n\nEVENT DETAILS:\nEvent name: :event_name\n",
        'layout_with_venue' => "\nLAYOUT (top to bottom):\n- Top third: event name in large, bold, high-contrast typography (at least 3x larger than body text).\n- Middle: date, time, full venue name, and venue address clearly separated in medium type.\n- Bottom third: remaining details (description, performers, ticket price) in highly legible smaller type.\n",
        'layout_without_venue' => "\nLAYOUT (top to bottom):\n- Top third: event name in large, bold, high-contrast typography (at least 3x larger than body text).\n- Middle: date and time in medium type.\n- Bottom third: remaining details (description, performers, ticket price) in highly legible smaller type.\n",
        'design' => "\nDESIGN DIRECTIVES:\n- Background: rich color or modern gradient inspired by the event category:category_hint. Do not use plain white.\n- Incorporate subtle, category-appropriate geometric or abstract elements to set the mood.\n- Typography must be professional with generous kerning and line spacing.\n- High contrast between text and background is mandatory.\n- Do not include AI-generated photos of people or faces.\n- All names (event name, performer names, venue name) must be spelled exactly as provided in the event details above. Do not alter, abbreviate, or substitute any name.\n- Do not invent or add any performers, artists, speakers, or participants not listed in the event details above.\n- Overall aesthetic should be premium, clean, and optimized for digital sharing.\n",
        'venue_directive' => "\n- The venue name and address must be displayed separately from the event name. Never combine or abbreviate them into 'at [name]'.",
        'style_instructions' => "\nCustom style instructions: :instructions\n",
        'style_reference' => "\nSTYLE REFERENCE: The schedule's profile image uses this visual style: :style_description. Generate an image that matches this style while following all other instructions above.",
    ],

    'profile_image' => [
        'intro' => "Create a minimalist, flat vector illustration with crisp geometric shapes and smooth gradients for a :type schedule called ':name'.",
        'description' => ' Description: :description.',
        'body' => "\n\nVisual mood: :mood.\nComposition: One strong, centralized, iconic motif that fills most of the canvas. Ensure extreme high contrast between the main element and the background so the image remains perfectly legible and recognizable when scaled down to 32px.\nSuggested motifs: :motifs.",
        'accents' => "\nCategory-inspired accents: :accents.",
        'color' => "\nColor palette: Use :accent_color as the dominant color, incorporating 2 to 3 subtle tonal variations and exactly one complementary accent color to create depth.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- Full bleed: the design must extend to every edge of the canvas with zero padding, margins, or empty borders.\n- Pure vector aesthetic: no photorealism, no 3D rendering.\n- No outlines, frames, or rounded corners within the image itself.\n- Do not isolate a tiny element in the center of a massive empty space.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
    ],

    'header_image' => [
        'intro' => "Create a sleek, abstract illustration featuring flowing gradients and layered geometric shapes for a wide banner image. This is for a :type schedule called ':name'.",
        'description' => ' Description: :description.',
        'body' => "\n\nVisual mood: :mood.\nComposition: A horizontal flow from left to right. Place visual weight toward the left and right edges. Keep the center area lighter and less complex so that overlaid text will remain highly legible.\nSuggested motifs: :motifs.",
        'accents' => "\nCategory-inspired accents: :accents.",
        'color' => "\nColor palette: Use :accent_color as the base, subtly shifting across the width of the banner through 2 to 3 related analog tones.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- Full bleed with no padding, borders, or vignettes.\n- Keep the aesthetic professional, modern, and mature. No photorealism.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
        'style_reference' => "\nSTYLE REFERENCE: The schedule's profile image uses this visual style: :style_description. Generate an image that matches this style while following all other instructions above.",
    ],

    'background_image' => [
        'intro' => "Create an ultra-subtle soft watercolor wash with faint geometric line work as a background image for a :type schedule called ':name'.",
        'body' => "\n\nPurpose: This image will sit behind text and event listings. It must be exceptionally subtle and non-distracting.\nComposition: Even and uniform across the entire canvas. No strong focal points. No area should be dramatically darker or lighter than another.\nColor palette: Very pale, washed-out tints of :accent_color at roughly 15 to 25 percent opacity. Soft, muted tones only.",
        'motifs' => "\nOptional hint of motifs (must look like a faint watermark, barely visible): :motifs.",
        'constraints' => "\n\nCRITICAL CONSTRAINTS:\n- Absolutely no text, letters, words, numbers, people, or faces.\n- High legibility priority: Black or dark text must be easily readable over every single part of the image.\n- No bold shapes, zero high-contrast elements, and no vivid or saturated colors.\n- Full bleed with no padding or borders.",
        'style_preferences' => "\n\nStyle preferences: :instructions",
        'style_reference' => "\nSTYLE REFERENCE: The schedule's profile image uses this visual style: :style_description. Generate an image that matches this style while following all other instructions above.",
    ],

    'event_parse' => [
        'base' => "Act as a precise data extraction API. Parse the event details from this :source message into the exact fields below.\n",
        'footer' => "\nThe date today is :today.\nThe event date is either :this_month or :next_month.\nIf no specific time is mentioned, default to 20:00 (8pm).\nIf multiple distinct performers are listed, separate them into multiple events.\nCRITICAL: Return ONLY raw JSON. Do not use markdown blocks. Your entire response must start exactly with { or [ and end exactly with } or ].",
    ],

    'event_parts' => [
        'base' => "Extract the agenda items, schedule parts, or setlist songs from this :source.\n\nCRITICAL: Return ONLY a raw JSON array of objects. Do not use markdown blocks. Your entire response must start exactly with [ and end exactly with ].\nEach object must contain strictly these keys:\n- name: the title or name of the part/song/session (required)\n- description: optional details or speaker name (string or null)\n- start_time: in HH:MM 24-hour format (string or null if no times are shown)\n- end_time: in HH:MM 24-hour format (string or null if no times are shown)\n\nIf the content is a setlist or numbered list without explicit times, set start_time and end_time to null for every item.\nPreserve the exact original order.",
        'additional_instructions' => "\nAdditional instructions: :instructions\n",
        'text_section' => "\nText:\n:text",
    ],

    'translate' => [
        'base' => 'Translate this text from :from to :to.:glossary CRITICAL: Return ONLY the translation as a raw JSON string. Do not use markdown blocks. The response must start and end with double quotes:',
        'glossary_header' => " Use these exact translations for the following terms:\n",
        'glossary_line' => '- ":original" => ":translation"',
    ],

    'translate_group_names' => [
        'base' => "Translate these group names from :from to English. CRITICAL: Return ONLY a raw JSON object. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }. Each key is the original name and the value is the English translation:\n:names",
    ],

    'translate_custom_field_names' => [
        'base' => "Translate these form field names from :from to English. CRITICAL: Return ONLY a raw JSON object. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }. Each key is the original name and the value is the English translation:\n:names",
    ],

    'translate_custom_field_options' => [
        'base' => "Translate these dropdown option values from :from to English. CRITICAL: Return ONLY a raw JSON object. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }. Each key is the original value and the value is the English translation:\n:values",
    ],

    'blog_post' => [
        'base' => "You are an expert SEO content marketer for Event Schedule, an open-source event management platform. Generate a highly valuable, original blog post about ':topic'.

        Specifications:
        - Tone: Professional, authoritative, and deeply practical.
        - Length: :length (short: 300-500 words, medium: 800-1200 words, long: 1500-2000 words).

        CRITICAL STYLE RULES:
        - Write at an 8th-grade reading level.
        - Structure the post clearly: A strong hook, scannable body paragraphs, and a clear, actionable takeaway.
        - Write in active voice. Show, don't tell.
        - STRICTLY AVOID common AI transitions and clichés ('In conclusion', 'Furthermore', 'Delve into', 'Bustling', 'Elevate', 'Unleash', 'Navigating the landscape', 'Testament to').
        - Never use em dashes. Use parentheses or commas instead.

        CRITICAL OUTPUT FORMAT:
        Return ONLY a raw JSON object. Do not use markdown blocks (no ```json). Your entire response must start exactly with { and end exactly with }.
        {
            \"title\": \"Highly clickable, search-intent driven title (50-60 characters)\",
            \"content\": \"HTML formatted content. Use semantic tags (<h1>, <h2>, <h3>), <p>, and <ul>/<ol>. Bold key terms naturally. Ensure high readability.\",
            \"excerpt\": \"Compelling summary targeting the primary keyword (150-160 characters)\",
            \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"],
            \"meta_title\": \"SEO meta title including primary keyword (50-60 characters)\",
            \"meta_description\": \"Action-oriented SEO meta description (150-160 characters)\",
            \"image_category\": \"business|wellness|sports|music|networking|family|productivity|nature|arts|general\"
        }

        Content Requirements:
        - Target relevant semantic keywords (LSI) naturally throughout the text.
        - The formatting must be clean HTML.
        :features_requirement
        :links_requirement
        - For image_category, strictly choose one from the provided list based on the core topic.",
        'links_with_parent' => '- IMPORTANT: You MUST seamlessly integrate exactly 2 internal links in the HTML content:
          1. <a href="[https://www.eventschedule.com/:parent_url](https://www.eventschedule.com/:parent_url)">Event Schedule for :parent_title</a> (or natural variation).
          2. <a href="[https://www.eventschedule.com](https://www.eventschedule.com)">Event Schedule</a>.
        - Place these links where they provide genuine contextual value to the reader.',
        'links_without_parent' => '- IMPORTANT: Integrate exactly 2 internal links naturally in the HTML content to <a href="[https://www.eventschedule.com](https://www.eventschedule.com)">Event Schedule</a>. Do not force them; place them where they contextually fit.',
        'features_line' => "- Integrate these specific platform features into the practical advice naturally, rather than listing them like a sales pitch: :features.\n        ",
    ],

    'blog_topic' => [
        'base' => 'You are an SEO content strategist for Event Schedule, an open-source event management platform. 

Recent blog post titles published on the site:
- :titles

Suggest ONE new, highly-searchable blog post topic that:
1. :style
2. Focuses on a specific, high-intent problem for event organizers, community managers, or ticketing professionals.
3. Is distinctly different from the recent posts listed above.

CRITICAL OUTPUT FORMAT:
Return ONLY a raw JSON object. Do not use markdown blocks. Your entire response must start exactly with { and end exactly with }.
{"topic": "your optimized topic phrase here (5-15 words)"}',
    ],
];
