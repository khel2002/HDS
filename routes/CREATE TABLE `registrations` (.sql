CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `guest_details_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `check_in_at` timestamp NULL DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`registration_id`),
  KEY `fk_registration_reservation` (`reservation_id`),
  KEY `fk_registration_guest` (`guest_details_id`),
  KEY `fk_registration_payment` (`payment_id`),
  KEY `fk_registration_user` (`user_id`),
  CONSTRAINT `fk_registration_guest` FOREIGN KEY (`guest_details_id`) REFERENCES `guest_details` (`guest_details_id`),
  CONSTRAINT `fk_registration_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`),
  CONSTRAINT `fk_registration_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`),
  CONSTRAINT `fk_registration_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
