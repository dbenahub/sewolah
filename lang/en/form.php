<?php

return [
    'meta_title' => 'Booking Application Form | SEWOLAH',
    'meta_description' => 'Apply for a SEWOLAH rental vehicle anywhere in Peninsular Malaysia. Every application is reviewed for eligibility and vehicle availability.',

    'eyebrow' => 'BOOKING APPLICATION FORM',
    'title' => 'Apply for a Vehicle',
    'intro' => 'Complete the application below. The SEWOLAH team will review your eligibility and vehicle availability, then contact you for the next stage of screening.',

    'aside' => [
        'title' => 'Before you begin',
        'points' => [
            ['title' => 'By application only', 'desc' => 'Submitting this form is not a booking confirmation. Every application is reviewed individually.'],
            ['title' => 'Minimum :days working days', 'desc' => 'Your pickup date must be at least :days working days from today. Last-minute bookings are not accepted.'],
            ['title' => 'Subject to availability', 'desc' => 'Vehicle models are confirmed based on current availability within our network.'],
            ['title' => 'Screening & verification', 'desc' => 'Documents such as your IC / passport and a valid driving licence are verified before a booking is confirmed.'],
        ],
        'next_title' => 'After you submit',
        'next' => [
            'A notification is sent directly to the SEWOLAH team.',
            'You receive an email confirming we have your application.',
            'You are taken to WhatsApp to reach our person-in-charge.',
            'Our team contacts you for screening and a quotation.',
        ],
        'earliest' => 'Earliest pickup date today',
    ],

    'step_label' => 'STEP :step OF 3',
    'steps' => ['Category', 'Rental Details', 'Applicant'],

    'step1' => [
        'title' => 'Who are you?',
        'subtitle' => 'Choose the category that fits best. It helps us review your application correctly.',
    ],

    'categories' => [
        'individual' => ['title' => 'Individual & Family', 'desc' => 'Personal matters, holidays or travelling with family.'],
        'corporate' => ['title' => 'Corporate & Company', 'desc' => 'Executives, company guests, roadshows or business travel.'],
        'outstation' => ['title' => 'Outstation & Airport', 'desc' => 'Arriving from another state or overseas via KLIA, KLIA2 or another airport.'],
        'event' => ['title' => 'Weddings & Special Events', 'desc' => 'Weddings, VIP guests, corporate events or celebrations.'],
        'long_term' => ['title' => 'Long-Term Rental', 'desc' => 'Monthly or longer rentals for individuals or companies.'],
    ],

    'step2' => [
        'title' => 'Rental details',
        'subtitle' => 'Tell us where and when you need the vehicle.',
    ],

    'step3' => [
        'title' => 'Applicant details',
        'subtitle' => 'We use these details to contact you and carry out screening.',
    ],

    'fields' => [
        'company_name' => 'Company / Organisation Name',
        'company_name_ph' => 'e.g. ABC Holdings Sdn Bhd',
        'purpose' => 'Purpose of Rental',
        'pickup_state' => 'Pickup State',
        'pickup_location' => 'Pickup Location / Area',
        'pickup_location_ph' => 'e.g. KLIA, Bangsar, Ipoh city centre',
        'return_location' => 'Return Location (if different)',
        'return_location_ph' => 'Leave blank if same as pickup',
        'pickup_date' => 'Pickup Date',
        'pickup_time' => 'Pickup Time',
        'return_date' => 'Return Date',
        'earliest_hint' => 'Earliest: :date (:days working days from today).',
        'vehicle' => 'Preferred Vehicle',
        'vehicle_any' => 'No specific preference — recommend one for me',
        'vehicle_other' => 'Other (specify model)',
        'other_vehicle' => 'Vehicle Model / Type',
        'other_vehicle_ph' => 'e.g. Mercedes-Benz E-Class, Honda Accord',
        'passengers' => 'Number of Passengers',
        'passengers_ph' => 'e.g. 4',
        'full_name' => 'Full Name (as per IC / Passport)',
        'full_name_ph' => 'Full name',
        'phone' => 'Phone / WhatsApp Number',
        'phone_ph' => 'e.g. 0123456789',
        'email' => 'Email Address',
        'email_ph' => 'name@email.com',
        'driver_license' => 'Driving Licence',
        'notes' => 'Additional Information',
        'notes_ph' => 'e.g. itinerary, child seat requirement, amount of luggage',
        'consent' => 'I understand this application is subject to eligibility screening and vehicle availability, that the pickup date must be at least :days working days from today, and I agree to be contacted by the SEWOLAH team. I have read the',
        'and' => 'and',
    ],

    'purpose_options' => [
        'Personal / Family',
        'Holiday',
        'Work / Business',
        'Corporate / VIP Guest',
        'Wedding / Event',
        'Temporary Replacement Vehicle',
        'Other',
    ],

    'license_options' => [
        'malaysia' => 'Valid Malaysian driving licence (CDL)',
        'international' => 'Foreign licence / International Driving Permit (IDP)',
    ],

    'states' => [
        'Perlis', 'Kedah', 'Pulau Pinang', 'Perak', 'Selangor', 'W.P. Kuala Lumpur', 'W.P. Putrajaya',
        'Negeri Sembilan', 'Melaka', 'Johor', 'Pahang', 'Terengganu', 'Kelantan',
    ],

    'select' => 'Select',
    'next' => 'Continue',
    'back' => 'Back',
    'submit' => 'Submit Application',
    'submitting' => 'Submitting...',
    'disclaimer' => 'Submitting this form is not a booking confirmation. A booking is only valid once screening is complete and written confirmation is issued by the SEWOLAH team.',

    'errors' => [
        'required' => 'Please complete this field.',
        'category' => 'Please choose a category.',
        'email' => 'Please enter a valid email address.',
        'phone' => 'Please enter a valid Malaysian phone number (e.g. 0123456789).',
        'min_date' => 'The earliest pickup date is :date. Last-minute bookings are not accepted.',
        'return_date' => 'Return date must be on or after the pickup date.',
        'passengers' => 'Passengers must be between 1 and 50.',
        'consent' => 'Please tick the agreement to continue.',
        'date' => 'Please enter a valid date.',
    ],

    'success' => [
        'eyebrow' => 'APPLICATION RECEIVED',
        'title' => 'Thank you, :name.',
        'copy' => 'Your application has been sent to the SEWOLAH team and a confirmation email is on its way to :email. We will review your eligibility and vehicle availability before contacting you.',
        'redirect' => 'You will be taken to WhatsApp to reach our person-in-charge in a few seconds...',
        'cta' => 'Open WhatsApp Now',
        'ref' => 'Reference No.',
    ],

    'whatsapp_message' => "Hello SEWOLAH, I have submitted a booking application through the website form.\n\nReference No.: :ref\nName: :name\nPhone: :phone\nEmail: :email\nCategory: :category\nCompany: :company\nPurpose: :purpose\nPickup State: :state\nPickup Location: :pickup_location\nReturn Location: :return_location\nPickup Date/Time: :pickup\nReturn Date: :return_date\nPreferred Vehicle: :vehicle\nPassengers: :passengers\nLicence: :license\nNotes: :notes\n\nPlease review my eligibility and vehicle availability. Thank you.",
];
