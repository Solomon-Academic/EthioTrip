<?php
// routes/web.php

return [
    'GET' => [
        '/' => 'PageController@home',
        '/home' => 'PageController@home',
        '/destination' => 'PageController@destination',
        '/packages' => 'PageController@packages',
        '/payment' => 'PageController@payment',
        '/about' => 'PageController@about',
        
        '/login' => 'AuthController@loginAction',
        '/register' => 'AuthController@registerAction',
        '/logout' => 'AuthController@logoutAction',
        '/dashboard' => 'DashboardController@indexAction',
        '/bookings' => 'BookingController@indexAction',
        '/bookings/create' => 'BookingController@createAction',
        '/bookings/edit' => 'BookingController@editAction',
        '/bookings/delete' => 'BookingController@deleteAction',
        '/admin/discounts' => 'AdminController@discountsAction',
        '/admin/packages' => 'PackageController@adminIndex',
        '/admin/packages/create' => 'PackageController@showCreate',
        '/admin/packages/edit' => 'PackageController@showEdit',
        '/admin/packages/delete' => 'PackageController@delete',
        '/admin/destinations' => 'DestinationController@adminIndex',
        '/admin/destinations/create' => 'DestinationController@showCreate',
        '/admin/destinations/edit' => 'DestinationController@showEdit',
        '/admin/destinations/delete' => 'DestinationController@delete',
        
        '/api/check-login' => 'ApiController@checkLogin',
        '/api/loyalty-discount' => 'ApiController@getLoyaltyDiscount',
        '/api/next-tier' => 'ApiController@getNextTier',
         '/read-more' => 'PageController@readMore',
    ],
    'POST' => [
        // add POST routes for form submissions and API endpoints
        '/login' => 'AuthController@loginAction',
        '/register' => 'AuthController@registerAction',
        '/bookings/create' => 'BookingController@createAction',
        '/bookings/edit' => 'BookingController@editAction',
        '/bookings/delete' => 'BookingController@deleteAction',
        '/admin/discounts' => 'AdminController@discountsAction',
        '/admin/packages/create' => 'PackageController@create',
        '/admin/packages/edit' => 'PackageController@update',
        '/admin/packages/delete' => 'PackageController@delete',
        '/admin/destinations/create' => 'DestinationController@create',
        '/admin/destinations/edit' => 'DestinationController@update',
        '/admin/destinations/delete' => 'DestinationController@delete',
        '/api/save-booking' => 'ApiController@saveBooking',
    ],
];
?>
