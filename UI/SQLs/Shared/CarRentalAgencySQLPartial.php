<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$arrPluginReplaceSQL = !isset($arrPluginReplaceSQL) ? array() : $arrPluginReplaceSQL;

// NOTE: 'faq_id' does not matter here and can be set automatically
$arrPluginReplaceSQL['faqs'] = "(`faq_question`, `faq_answer`, `faq_order`, `blog_id`) VALUES
('Are the vehicles insured?', 'Yes, all our vehicles have mandatory Civilian & KASKO insurances.', 1, [BLOG_ID]),
('What are the methods of payment for the car rental services?', 'You can pay by cash, PayPal or by bank transfer.', 2, [BLOG_ID]),
('How do I rent a car?', 'You can rent a car by simply clicking on the Reservation button in the menu or by calling +1 450 610 0990. If you choose to reserve a car online, we will contact you to confirm your reservation.', 3, [BLOG_ID]),
('Can I change the car after the reservation?', 'Yes. If you would like to change or cancel your reservation of a car after approving it, please call +1 450 610 0990 or reach us by contacts provided in the Contacts page.', 4, [BLOG_ID]),
('Where can I take the car from and where should I return it?', 'You can take the car at the address 625 2nd Street, San Francisco. If you have reserved a car for more than three days, we can deliver the car to the location of your choice. The car has to be returned to an agreed location in San Francisco.', 5, [BLOG_ID]),
('What should I do, if the car breaks down?', 'If your car breaks down, please call us immediately at +1 450 600 4000.', 6, [BLOG_ID]),
('Do you deliver cars outside San Francisco?', 'Yes, we will deliver you the car to the location of your choice for an additional price.', 7, [BLOG_ID]),
('What time can I contact you?', 'You can call us 24/7 by phone no. +1 450 600 4000.', 8, [BLOG_ID]),
('Are there any maps in the car?', 'Yes, if you wish, we will provide maps of San Francisco and United States for free.', 9, [BLOG_ID]);";

// Note: 'settings' table is different for each demo version, so it is not listed here