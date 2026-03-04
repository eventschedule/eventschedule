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
];
