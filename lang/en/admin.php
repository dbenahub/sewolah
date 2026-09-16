<?php

return [
    'panel_title' => 'SUPER ADMIN PANEL',
    'login' => [
        'username' => 'Email',
        'password' => 'Password',
        'submit' => 'LOG IN',
        'invalid' => 'Invalid email or password.',
    ],
    'nav' => [
        'dashboard' => 'Dashboard',
        'bookings' => 'Bookings & Customers',
        'pixels' => 'Pixel Settings',
        'vehicles' => 'Vehicles',
        'page_settings' => 'Edit Landing Page',
        'change_password' => 'Change Password',
        'logout' => 'Log Out',
    ],
    'change_password' => [
        'title' => 'Change Password',
        'subtitle' => 'Update your admin account password. Make sure to use a strong password that is not shared with anyone.',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm New Password',
        'submit' => 'UPDATE PASSWORD',
        'success' => 'Password updated successfully.',
        'errors' => [
            'current_required' => 'Please enter your current password.',
            'current_invalid' => 'The current password is incorrect.',
            'password_required' => 'Please enter a new password.',
            'password_min' => 'The new password must be at least 8 characters.',
            'password_confirmed' => 'Password confirmation does not match.',
        ],
    ],
];
