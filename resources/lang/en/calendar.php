<?php

return [

    // Layout / Header
    'site_title'    => 'Riviera Maya Events Calendar',
    'add_event'     => 'Add Your Own Event',
    'view_events'   => '← View Events',
    'login'         => 'Login',
    'my_events'     => 'My Events',
    'all_rights'    => 'All rights reserved.',

    // Filter bar
    'search'             => 'Search',
    'search_placeholder' => 'Events, organizers...',
    'location_label'     => 'Location',
    'all_locations'      => 'All Locations',
    'category_label'     => 'Category',
    'all_categories'     => 'All Categories',
    'from_label'         => 'From',
    'to_label'           => 'To',
    'featured_only'      => '★ Featured only',
    'view_list'          => 'List view',
    'view_month'         => 'Month view',
    'view_week'          => 'Week view',
    'view_day'           => 'Day view',

    // Cards / badges
    'featured_badge' => '★ Featured',
    'premium_badge'  => '★ Premium',
    'view_event'     => 'View Event →',

    // JS bridge — navigation
    'prev' => '← Prev',
    'next' => 'Next →',

    // JS bridge — calendar UI strings
    'all_day'      => 'All Day',
    'no_events_day'=> 'No events on this day',

    // JS bridge — locale string passed to toLocaleDateString / toLocaleTimeString
    'js_locale' => 'en-US',

    // JS bridge — month names (index 0 = January)
    'months' => [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ],

    // JS bridge — abbreviated month names (index 0 = Jan)
    'months_short' => [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ],

    // JS bridge — short day names Mon→Sun (week view order)
    'days_short' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],

    // Index page
    'no_events_found' => 'No events found',
    'adjust_filters'  => 'Try adjusting your filters.',
    'page_title'      => 'Riviera Maya Events Calendar — Upcoming Events',
    'og_description'  => 'Discover upcoming events in the Riviera Maya — Puerto Aventuras, Playa del Carmen, Tulum and beyond.',

    // Event detail (show) page
    'add_google_cal'   => '+ Add to Google Calendar',
    'download_ics'     => '↓ Download .ics',
    'event_website'    => '🔗 Event Website',
    'share'            => 'Share With Friends',
    'share_modal_title'=> 'Share With Friends',
    'copy'             => 'Copy',
    'copied'           => 'Copied!',
    'link_copied'      => 'Link copied!',
    'email_check_out'  => 'Check out this event: ',
    'sponsored'        => 'Sponsored',
    'close'            => 'Close',

    // Submit form
    'submit_title_page'     => 'Submit an Event',
    'submit_heading'        => 'Submit a Free Event',
    'submit_subheading'     => "After submitting, you'll receive a verification email. Your event goes live once our team approves it.",
    'field_title'           => 'Event Title *',
    'field_organizer'       => 'Organizer / Host *',
    'field_location'        => 'Location *',
    'field_category'        => 'Category *',
    'field_description'     => 'Description *',
    'field_website'         => 'Event Website (optional)',
    'field_image'           => 'Event Image (optional, max 4MB)',
    'field_email'           => 'Your Email Address *',
    'email_note'            => "We'll send a verification link to this address.",
    'select_location'       => 'Select location',
    'select_category'       => 'Select category',
    'select_pattern'        => 'Select pattern',
    'all_day_event'         => 'All Day Event',
    'field_start_date'      => 'Start Date *',
    'field_start_time'      => 'Start Time',
    'field_end_date'        => 'End Date',
    'field_end_time'        => 'End Time',
    'is_recurring'          => 'This is a recurring event',
    'repeats_label'         => 'Repeats *',
    'weekly_same_day'       => 'Weekly (same weekday)',
    'monthly_date'          => 'Monthly (same date) — months without this date are skipped',
    'monthly_weekday'       => 'Monthly (same weekday position, e.g. 3rd Monday)',
    'day_of_week'           => 'Day of week *',
    'week_of_month'         => 'Week of month *',
    'weekday_label'         => 'Weekday *',
    'repeat_until'          => 'Repeat until (inclusive) *',
    'max_occurrences'       => 'Maximum 52 occurrences will be generated.',
    'day_sunday'            => 'Sunday',
    'day_monday'            => 'Monday',
    'day_tuesday'           => 'Tuesday',
    'day_wednesday'         => 'Wednesday',
    'day_thursday'          => 'Thursday',
    'day_friday'            => 'Friday',
    'day_saturday'          => 'Saturday',
    'featured_addon_label'  => '★ Feature This Event — 200 MXN (~$10 USD)',
    'featured_addon_desc'   => "Your event will appear in the Featured carousel at the top of the calendar and be highlighted with a star badge across all views. Featured placement runs for 30 days from payment. After submitting you'll be redirected to Stripe to complete payment.",
    'featured_addon_note'   => "Your email verification link will be sent immediately — no need to wait for payment first.",
    'submit_button'         => 'Submit Event',
    'cancel'                => 'Cancel',

    // Category translations (keyed by English DB name; used by translateCategory())
    'categories' => [
        'Music'                  => 'Music',
        'Food & Drink'           => 'Food & Drink',
        'Arts & Culture'         => 'Arts & Culture',
        'Sports & Fitness'       => 'Sports & Fitness',
        'Family & Kids'          => 'Family & Kids',
        'Nightlife'              => 'Nightlife',
        'Business & Networking'  => 'Business & Networking',
        'Health & Wellness'      => 'Health & Wellness',
        'Charity & Causes'       => 'Charity & Causes',
        'Outdoors & Adventure'   => 'Outdoors & Adventure',
        'Film & Media'           => 'Film & Media',
        'Holiday & Seasonal'     => 'Holiday & Seasonal',
    ],
];
