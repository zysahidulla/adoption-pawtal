-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 08:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pet_adoption_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `password_hash`, `email`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$M3LhQ.AfxgZ4TqHVbZKkCOR/Aggaha5vCiSOW/ZE2VCkhyIpmGa9q', 'admin@shelter.com', 'System Administrator', '2025-11-18 22:37:41');

-- --------------------------------------------------------

--
-- Table structure for table `adopters`
--

CREATE TABLE `adopters` (
  `adopter_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `household_type` enum('House','Apartment','Condo','Other') NOT NULL,
  `has_other_pets` tinyint(1) NOT NULL,
  `other_pets_details` text DEFAULT NULL,
  `has_children` tinyint(1) NOT NULL,
  `children_ages` varchar(100) DEFAULT NULL,
  `experience_level` enum('First-time','Some Experience','Experienced') NOT NULL,
  `reason_for_adoption` text NOT NULL,
  `reference1_name` varchar(100) DEFAULT NULL,
  `reference1_phone` varchar(20) DEFAULT NULL,
  `reference1_relationship` varchar(50) DEFAULT NULL,
  `reference2_name` varchar(100) DEFAULT NULL,
  `reference2_phone` varchar(20) DEFAULT NULL,
  `reference2_relationship` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adopters`
--

INSERT INTO `adopters` (`adopter_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `postal_code`, `household_type`, `has_other_pets`, `other_pets_details`, `has_children`, `children_ages`, `experience_level`, `reason_for_adoption`, `reference1_name`, `reference1_phone`, `reference1_relationship`, `reference2_name`, `reference2_phone`, `reference2_relationship`, `created_at`) VALUES
(1, 'Melanie', 'Dotollo', 'dotollomelanie4@gmail.com', '09123456789', 'Brgy Batumbakal', 'Biringan', '0000', 'Condo', 1, 'I have 7  pets', 0, '', 'Experienced', 'because i want to add more pets', 'jungkook', '0231456494', 'husband', 'jang ki yong', '05165554', 'affair', '2025-11-19 01:41:22'),
(2, 'Melanie', 'Dotollo', 'dotollomelanie4@gmail.com', '09123456789', 'Brgy Batumbakal', 'Biringan', '0000', 'House', 0, '', 0, '', 'First-time', 'u lobfaf', 'jang ki yong', '0516555', 'husband', 'jang ki yong', '09123456789', 'husband', '2025-11-19 03:26:47'),
(3, 'asfadf', 'fafas', 'dotollomelanie2023@gmail.com', '09123456789', 'Brgy Batumbakal', 'Biringan', '0000', 'Apartment', 0, '', 0, '', 'Some Experience', 'a', 'jang ki yong', '0516555', 'husband', 'jang ki yong', '09123456789', 'husband', '2025-11-19 03:52:04'),
(4, 'jungkook', 'jeon', 'msenpai59@gmail.com', '09123456789', 'b1 l2', 'tondo manila', '48456', 'Apartment', 0, '', 0, '', 'First-time', 'i want', 'Melanie Sevillo Dotollo', '09926568565', 'wife', 'jang ki yong', '09123456789', 'wife\'s affair', '2025-11-19 07:06:48'),
(5, 'jungkook', 'jeon', 'msenpai59@gmail.com', '09123456789', 'b1 l2', 'tondo manila', '48456', 'Apartment', 0, '', 0, '', 'Some Experience', 'hghd', 'jang ki yong', '09123456789', 'wife\'s affair', 'jang ki yong', '09123456789', 'wife\'s affair', '2025-11-19 07:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_applications`
--

CREATE TABLE `adoption_applications` (
  `application_id` int(11) NOT NULL,
  `adopter_id` int(11) NOT NULL,
  `animal_id` varchar(20) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('For Review','For Interview','Accepted','Rejected') DEFAULT 'For Review',
  `notes` text DEFAULT NULL,
  `interview_scheduled_date` datetime DEFAULT NULL,
  `decision_date` date DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_applications`
--

INSERT INTO `adoption_applications` (`application_id`, `adopter_id`, `animal_id`, `application_date`, `status`, `notes`, `interview_scheduled_date`, `decision_date`, `rejection_reason`, `updated_at`) VALUES
(1, 1, 'A1234568', '2025-11-19 01:41:22', 'For Review', 'fss', NULL, NULL, NULL, '2025-11-19 03:38:19'),
(2, 2, 'A531011', '2025-11-19 03:26:47', 'For Interview', 'sdsg', NULL, NULL, NULL, '2025-11-19 03:46:16'),
(3, 3, 'A1234568', '2025-11-19 03:52:04', 'For Interview', 'a', NULL, NULL, NULL, '2025-11-19 06:25:33'),
(4, 4, 'A537681', '2025-11-19 07:06:48', 'Accepted', NULL, NULL, NULL, NULL, '2025-11-19 07:11:26'),
(5, 5, 'A537514', '2025-11-19 07:23:22', 'Rejected', 'hm', NULL, NULL, NULL, '2025-11-19 07:24:30');

-- --------------------------------------------------------

--
-- Table structure for table `application_notes`
--

CREATE TABLE `application_notes` (
  `note_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `note_content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `log_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `sent_to` varchar(150) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Sent','Failed') DEFAULT 'Sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`log_id`, `application_id`, `email_type`, `sent_to`, `subject`, `message`, `sent_at`, `status`) VALUES
(1, 1, 'Confirmation', 'dotollomelanie4@gmail.com', 'Application Received - testing', 'Dear Melanie Dotollo,\n\nThank you for your interest in adopting testing!\n\nWe have received your application (Reference #1) and will review it shortly.\n\nYou can expect to hear from us within 3-5 business days. If your application is selected, we will contact you to schedule an interview.\n\nThank you for choosing to adopt!\n\nBest regards,\nPet Adoption Center Team\n', '2025-11-19 01:41:24', 'Failed'),
(2, 2, 'Confirmation', 'dotollomelanie4@gmail.com', 'Application Received - SAFIRA', 'Dear Melanie Dotollo,\n\nThank you for your interest in adopting SAFIRA!\n\nWe have received your application (Reference #2) and will review it shortly.\n\nYou can expect to hear from us within 3-5 business days. If your application is selected, we will contact you to schedule an interview.\n\nThank you for choosing to adopt!\n\nBest regards,\nPet Adoption Center Team\n', '2025-11-19 03:26:49', 'Failed'),
(3, 3, 'Confirmation', 'dotollomelanie2023@gmail.com', 'Application Received - testing1', 'Dear asfadf fafas,\n\nThank you for your interest in adopting testing1!\n\nWe have received your application (Reference #3) and will review it shortly.\n\nYou can expect to hear from us within 3-5 business days. If your application is selected, we will contact you to schedule an interview.\n\nThank you for choosing to adopt!\n\nBest regards,\nPet Adoption Center Team\n', '2025-11-19 03:52:06', 'Failed'),
(4, 3, 'Interview_request', 'dotollomelanie2023@gmail.com', 'Interview Request - testing1 Adoption', 'Dear asfadf fafas,\r\n\r\nThank you for your application to adopt testing1!\r\n\r\nWe have reviewed your application (Reference #3) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: testing1\r\n- Application ID: #3\r\n- Your contact: dotollomelanie2023@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 06:34:57', 'Failed'),
(5, 3, 'Interview_request', 'dotollomelanie2023@gmail.com', 'Interview Request - testing1 Adoption', 'Dear asfadf fafas,\r\n\r\nThank you for your application to adopt testing1!\r\n\r\nWe have reviewed your application (Reference #3) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: testing1\r\n- Application ID: #3\r\n- Your contact: dotollomelanie2023@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 06:37:23', 'Failed'),
(6, 3, 'Interview_request', 'dotollomelanie2023@gmail.com', 'Interview Request - testing1 Adoption', 'Dear asfadf fafas,\r\n\r\nThank you for your application to adopt testing1!\r\n\r\nWe have reviewed your application (Reference #3) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: testing1\r\n- Application ID: #3\r\n- Your contact: dotollomelanie2023@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 06:40:43', 'Failed'),
(7, 2, 'Interview_request', 'dotollomelanie4@gmail.com', 'Interview Request - SAFIRA Adoption', 'Dear Melanie Dotollo,\r\n\r\nThank you for your application to adopt SAFIRA!\r\n\r\nWe have reviewed your application (Reference #2) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: SAFIRA\r\n- Application ID: #2\r\n- Your contact: dotollomelanie4@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:04:10', 'Sent'),
(8, 4, 'Confirmation', 'msenpai59@gmail.com', 'Application Received - BUTCH', 'Dear jungkook jeon,\r\n\r\nThank you for your interest in adopting BUTCH!\r\n\r\nWe have received your application (Reference #4) and will review it shortly.\r\n\r\nWhat\'s Next?\r\n- Review: 3-5 business days\r\n- Interview: If selected, we\'ll contact you at msenpai59@gmail.com\r\n- Decision: Within 2-3 days after interview\r\n\r\nYou can contact us anytime:\r\n📧 Email: info@petadoptioncenter.com\r\n📞 Phone: (123) 456-7890\r\n\r\nThank you for choosing to adopt!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:06:53', 'Sent'),
(9, 4, 'Interview_request', 'msenpai59@gmail.com', 'Interview Request - BUTCH Adoption', 'Dear jungkook jeon,\r\n\r\nThank you for your application to adopt BUTCH!\r\n\r\nWe have reviewed your application (Reference #4) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: BUTCH\r\n- Application ID: #4\r\n- Your contact: msenpai59@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:10:05', 'Sent'),
(10, 4, 'Interview_request', 'msenpai59@gmail.com', 'Interview Request - BUTCH Adoption', 'Dear jungkook jeon,\r\n\r\nThank you for your application to adopt BUTCH!\r\n\r\nWe have reviewed your application (Reference #4) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: BUTCH\r\n- Application ID: #4\r\n- Your contact: msenpai59@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:10:10', 'Sent'),
(11, 4, 'Acceptance', 'msenpai59@gmail.com', 'Congratulations! Your Application Has Been Approved - BUTCH', 'Dear jungkook jeon,\r\n\r\nWonderful news! Your application to adopt BUTCH has been APPROVED! 🎉\r\n\r\nApplication Reference: #4\r\n\r\nNext Steps:\r\n1. Please contact us within 3 business days to schedule your adoption appointment\r\n2. Bring a valid ID and proof of address\r\n3. Adoption fee: $150 (includes vaccinations, spay/neuter, microchip)\r\n4. We\'ll provide all medical records and adoption paperwork\r\n\r\nContact Information:\r\n📞 Phone: (123) 456-7890\r\n📧 Email: info@petadoptioncenter.com\r\n🕐 Hours: Monday-Friday, 9:00 AM - 5:00 PM\r\n\r\nCongratulations on your new family member!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:10:53', 'Sent'),
(12, 5, 'Confirmation', 'msenpai59@gmail.com', 'Application Received - TANGO', 'Dear jungkook jeon,\r\n\r\nThank you for your interest in adopting TANGO!\r\n\r\nWe have received your application (Reference #5) and will review it shortly.\r\n\r\nWhat\'s Next?\r\n- Review: 3-5 business days\r\n- Interview: If selected, we\'ll contact you at msenpai59@gmail.com\r\n- Decision: Within 2-3 days after interview\r\n\r\nYou can contact us anytime:\r\n📧 Email: info@petadoptioncenter.com\r\n📞 Phone: (123) 456-7890\r\n\r\nThank you for choosing to adopt!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:23:27', 'Sent'),
(13, 5, 'Interview_request', 'msenpai59@gmail.com', 'Interview Request - TANGO Adoption', 'Dear jungkook jeon,\r\n\r\nThank you for your application to adopt TANGO!\r\n\r\nWe have reviewed your application (Reference #5) and would like to schedule an interview with you.\r\n\r\nPlease reply to this email with your availability for the following week, or call us at (123) 456-7890 to schedule.\r\n\r\nInterview Details:\r\n- Pet: TANGO\r\n- Application ID: #5\r\n- Your contact: msenpai59@gmail.com\r\n\r\nWe look forward to speaking with you!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:24:09', 'Sent'),
(14, 5, 'Rejection', 'msenpai59@gmail.com', 'Update on Your Adoption Application - TANGO', 'Dear jungkook jeon,\r\n\r\nThank you for your interest in adopting TANGO and for taking the time to complete our adoption application (Reference #5).\r\n\r\nAfter careful consideration, we have decided to move forward with another applicant whose situation we felt was the best match for TANGO\'s specific needs.\r\n\r\nWe encourage you to continue browsing our available pets at our website, as we have many wonderful animals looking for loving homes. Each pet has unique needs, and we\'re confident you\'ll find the perfect match!\r\n\r\nIf you have any questions, please don\'t hesitate to contact us.\r\n\r\nThank you for considering adoption!\r\n\r\nBest regards,\r\nPet Adoption Center Team', '2025-11-19 07:24:41', 'Sent');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `animal_id` varchar(20) NOT NULL,
  `intake_type` varchar(50) NOT NULL,
  `intake_date` date NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `animal_type` varchar(50) NOT NULL,
  `pet_age` varchar(50) NOT NULL,
  `pet_size` varchar(20) NOT NULL,
  `color` varchar(100) NOT NULL,
  `breed` varchar(100) NOT NULL,
  `sex` char(1) NOT NULL,
  `adoption_status` enum('Available','Pending','Reserved','Trial') DEFAULT 'Available',
  `photo_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`animal_id`, `intake_type`, `intake_date`, `pet_name`, `animal_type`, `pet_age`, `pet_size`, `color`, `breed`, `sex`, `adoption_status`, `photo_path`, `description`, `created_at`) VALUES
('A1234568', 'FOSTER', '2025-11-19', 'testing1', 'CAT', '1 year', 'SMALL', 'white, black, orange', 'CALICO', 'F', 'Available', NULL, '', '2025-11-19 01:38:15'),
('A348332', 'OWNER SUR', '2023-05-10', 'TIRAMISU', 'CAT', '14 YEARS', 'MED', 'BRN TABBY / WHITE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A427950', 'RETURN', '2025-09-21', 'BOSWELL', 'DOG', '8 YEARS', 'MED', 'BLACK', 'CHINESE SHARPEI', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A478716', 'OWNER SUR', '2024-06-15', 'BANANA', 'DOG', '9 YEARS', 'LARGE', 'BLACK / WHITE', 'BOXER / MIX', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A484117', 'RETURN', '2025-06-15', 'ROCKY', 'DOG', '5 YEARS', 'MED', 'BLACK / WHITE', 'SIBERIAN HUSKY / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A486296', 'OWNER SUR', '2024-08-20', 'WILLOW', 'CAT', '6 YEARS', 'MED', 'BRN TABBY', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A497580', 'EUTH REQ', '2025-10-21', 'RAJAH', 'DOG', '5 YEARS', 'LARGE', 'BRINDLE', 'BOXER / MIX', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A501607', 'OWNER SUR', '2024-12-10', 'CLEO', 'DOG', '5 YEARS', 'MED', 'BRINDLE', 'AM PIT BULL TER', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A508666', 'OWNER SUR', '2025-09-15', 'FINLEY', 'DOG', '3 YEARS', 'MED', 'TAN', 'CANE CORSO / STAFFORDSHIRE', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A512399', 'STRAY', '2025-01-15', 'MELANIE', 'CAT', '4 YEARS', 'MED', 'BRN TABBY', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A514149', 'OWNER SUR', '2025-02-05', 'NAJIA', 'DOG', '3 YEARS', 'LARGE', 'WHITE / BROWN', 'BULL TERRIER / MIX', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A514568', 'CONFISCATE', '2025-02-10', 'ZINA', 'DOG', '5 YEARS', 'MED', 'TAN / WHITE', 'AMERICAN STAFF', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A521742', 'OWNER SUR', '2025-03-20', 'VIDI', 'CAT', '1 YEAR 8 MONTHS', 'KITTE', 'BRN TABBY', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A522072', 'OWNER SUR', '2025-03-15', 'ANDREW', 'CAT', '1 YEAR 5 MONTHS', 'MED', 'ORANGE TAB / WHITE', 'DOMESTIC LH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A522706', 'FOSTER', '2024-10-18', 'ARNOLD', 'DOG', '2 YEARS', 'LARGE', 'BROWN', 'BLACK MOUTH CUR / CHINESE SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A524410', 'OWNER SUR', '2025-09-16', 'SPOT', 'DOG', '2 YEARS', 'MED', 'WHITE / BLACK', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A531011', 'OWNER SUR', '2025-11-15', 'SAFIRA', 'CAT', '2 YEARS', 'MED', 'TORTIE / WHITE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A531446', 'OWNER SUR', '2025-05-24', 'CHEEZ-IT', 'CAT', '14 YEARS', 'LARGE', 'BLACK / WHITE', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A533099', 'OWNER SUR', '2025-07-20', 'THUNDA', 'DOG', '3 YEARS', 'MED', 'BLACK / WHITE', 'LABRADOR RETR / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A533200', 'OWNER SUR', '2025-09-23', 'DOMER', 'DOG', '5 YEARS', 'X-LRG', 'BLUE', 'CANE CORSO', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A533749', 'RETURN', '2025-10-25', 'JEFE', 'DOG', '5 YEARS', 'LARGE', 'RED / WHITE', 'AM PIT BULL TER / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A533988', 'BOARDING', '2025-10-13', 'BUSTER', 'DOG', '4 YEARS', 'LARGE', 'TAN / WHITE', 'SIBERIAN HUSKY / AM PIT BULL T', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534417', 'RETURN', '2025-10-21', 'MILEY CYRUS', 'CAT', '1 YEAR 3 MONTHS', 'MED', 'TORTIE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534493', 'RETURN', '2025-08-10', 'ORDELIA', 'DOG', '4 YEARS', 'LARGE', 'BROWN / WHITE', 'BOXER / MIX', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534624', 'OWNER SUR', '2025-08-16', 'LULU', 'CAT', '4 YEARS', 'MED', 'BROWN', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534659', 'OWNER SUR', '2025-08-17', 'ETHAN', 'CAT', '1 YEAR', 'MED', 'WHITE / GRAY', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534673', 'OWNER SUR', '2025-08-17', 'PUZZLE', 'DOG', '3 YEARS', 'LARGE', 'WHITE / BROWN', 'BEAGLE / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534927', 'OWNER SUR', '2025-08-24', 'JAMESON', 'CAT', '4 YEARS', 'MED', 'WHITE / BLACK', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A534947', 'OWNER SUR', '2025-10-16', 'RODEO', 'DOG', '7 YEARS', 'LARGE', 'WHITE / BROWN', 'GREAT DANE / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535014', 'OWNER SUR', '2025-08-27', 'NEO', 'DOG', '1 YEAR 6 MONTHS', 'MED', 'BLACK / WHITE', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535032', 'OWNER SUR', '2025-08-28', 'SQUID', 'CAT', '10 YEARS', 'SMALL', 'BLACK', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535079', 'OWNER SUR', '2025-08-29', 'CUTIE', 'DOG', '8 YEARS', 'LARGE', 'BLUE / BROWN', 'AM PIT BULL TER', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535082', 'OWNER SUR', '2025-08-29', 'SULA', 'CAT', '7 YEARS', 'MED', 'BLACK', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535138', 'RETURN', '2025-10-10', 'SPICY DRAGON ROLL', 'CAT', '5 YEARS', 'MED', 'WHITE / BRN TABBY', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535494', 'OWNER SUR', '2025-09-10', 'JAGER', 'DOG', '4 YEARS', 'LARGE', 'WHITE / BLACK', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535584', 'CONFISCATE', '2025-09-14', 'SUGRE', 'DOG', '2 YEARS', 'LARGE', 'BRINDLE / WHITE', 'AMER BULLDOG / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535716', 'RETURN', '2025-10-22', 'CURTIS', 'DOG', '2 YEARS', 'LARGE', 'GRAY', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A535978', 'OWNER SUR', '2025-09-26', 'OREO', 'CAT', '4 YEARS', 'MED', 'BLACK / WHITE', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536012', 'OWNER SUR', '2025-09-27', 'MISS WAVERLY', 'CAT', '6 YEARS', 'MED', 'GRAY TAB', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536163', 'WILDLIFE', '2025-10-01', 'SULLY', 'BIRD', 'NO AGE', 'SMALL', 'GRAY / GREEN', 'PIGEON', 'U', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536304', 'STRAY', '2025-10-05', 'FRED', 'DOG', '2 YEARS', 'MED', 'BLACK / WHITE', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536481', 'OWNER SUR', '2025-10-12', 'KURO', 'OTHER', '4 YEARS', 'MED', 'BLACK / BROWN', 'RABBIT SH', 'F', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536482', 'OWNER SUR', '2025-10-12', 'BENITA', 'OTHER', '4 YEARS', 'MED', 'GRAY', 'RABBIT SH', 'F', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536483', 'OWNER SUR', '2025-10-12', 'GABRIEL', 'OTHER', '4 YEARS', 'MED', 'WHITE', 'RABBIT SH', 'F', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536484', 'OWNER SUR', '2025-10-12', 'DULCE', 'OTHER', '4 YEARS', 'MED', 'GRAY', 'RABBIT SH', 'M', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536486', 'OWNER SUR', '2025-10-12', 'PAPPY', 'OTHER', '3 YEARS', 'MED', 'BROWN', 'SUGAR GLIDER', 'M', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536487', 'OWNER SUR', '2025-10-12', 'VON', 'OTHER', '3 YEARS', 'SMALL', 'BROWN', 'SUGAR GLIDER', 'M', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536602', 'STRAY', '2025-10-15', 'AXOLOTL', 'CAT', '2 YEARS', 'SMALL', 'WHITE / BRN TABBY', 'DOMESTIC MH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536619', 'OWNER SUR', '2025-10-16', 'MELODY', 'CAT', '2 YEARS', 'MED', 'WHITE / BRN TABBY', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536722', 'OWNER SUR', '2025-10-19', 'HYDROX', 'OTHER', '5 YEARS', 'MED', 'BLACK', 'RABBIT SH / REX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536869', 'OWNER SUR', '2025-10-23', 'KILO', 'DOG', '1 YEAR 1 MONTH', 'LARGE', 'WHITE / BLACK', 'AM PIT BULL TER / AUST TERRIER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536873', 'OWNER SUR', '2025-10-23', 'PIA', 'CAT', '2 YEARS', 'MED', 'DIL CALICO', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536891', 'STRAY', '2025-10-23', 'KISMET', 'CAT', '3 YEARS', 'SMALL', 'BLACK / WHITE', 'DOMESTIC MH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536951', 'OWNER SUR', '2025-10-25', 'BUDDY', 'OTHER', 'NO AGE', 'SMALL', 'GREEN', 'TURTLE', 'U', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A536952', 'OWNER SUR', '2025-10-25', 'DUDE', 'OTHER', 'NO AGE', 'SMALL', 'GREEN', 'TURTLE', 'U', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537084', 'OWNER SUR', '2025-10-29', 'RATTLE', 'CAT', '8 MONTHS', 'SMALL', 'GRAY', 'DOMESTIC LH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537088', 'OWNER SUR', '2025-10-29', 'BEBE', 'CAT', '8 MONTHS', 'SMALL', 'BLACK', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537099', 'STRAY', '2025-10-29', 'KEVIN', 'DOG', '6 YEARS', 'LARGE', 'TAN', 'BLACK MOUTH CUR / LABRADOR', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537143', 'STRAY', '2025-10-31', 'WYCLEF', 'CAT', '2 YEARS', 'SMALL', 'BLACK', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537167', 'OWNER SUR', '2025-11-01', 'EBBIE', 'CAT', '1 YEAR 4 MONTHS', 'MED', 'BLACK', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537184', 'OWNER SUR', '2025-10-15', 'MANCH', 'CAT', '1 YEAR', 'MED', 'BRN TABBY / WHITE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537197', 'OWNER SUR', '2025-11-01', 'WAWATOSA', 'CAT', '6 YEARS', 'MED', 'GRAY / WHITE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537213', 'STRAY', '2025-11-01', 'MR TUXEDO', 'DOG', '3 YEARS', 'LARGE', 'BLACK / WHITE', 'AM PIT BULL TER', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537250', 'OWNER SUR', '2025-11-02', 'AKIRA', 'DOG', '8 YEARS', 'MED', 'BROWN / WHITE', 'AUST CATTLE DOG / MIX', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537254', 'RETURN', '2025-11-02', 'SAMANTHA', 'CAT', '4 YEARS', 'MED', 'GRAY', 'DOMESTIC LH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537392', 'OWNER SUR', '2025-11-06', 'CASH', 'DOG', '3 YEARS', 'X-LRG', 'BRINDLE / GRAY', 'CANE CORSO', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537420', 'OWNER SUR', '2025-11-06', 'SNUGG', 'DOG', '1 YEAR 6 MONTHS', 'MED', 'BROWN', 'AM PIT BULL TER / MIX', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537438', 'STRAY', '2025-11-08', 'JIGGLYPUFF', 'CAT', '3 YEARS', 'MED', 'BRN TABBY / WHITE', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537454', 'STRAY', '2025-11-08', 'HOLTBY', 'CAT', '2 YEARS', 'MED', 'BRN TABBY', 'DOMESTIC SH', 'S', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537514', 'OWNER SUR', '2025-11-10', 'TANGO', 'CAT', '4 MONTHS', 'KITTE', 'GRAY TAB', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537648', 'OWNER SUR', '2025-11-14', 'BROWNIE', 'OTHER', '4 MONTHS', 'SMALL', 'BROWN', 'RABBIT SH', 'M', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537681', 'OWNER SUR', '2025-11-15', 'BUTCH', 'BIRD', '5 YEARS', 'SMALL', 'BLUE / WHITE', 'BUDGERIGAR', 'M', 'Pending', NULL, NULL, '2025-11-19 01:19:53'),
('A537682', 'OWNER SUR', '2025-11-15', 'LOUISE', 'BIRD', '3 YEARS', 'SMALL', 'BLUE / WHITE', 'PARAKEET', 'F', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537683', 'OWNER SUR', '2025-11-15', 'JOEY', 'BIRD', '3 YEARS', 'SMALL', 'BLUE / WHITE', 'PARAKEET', 'M', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537684', 'OWNER SUR', '2025-11-15', 'CHEEP CHEEP', 'BIRD', '3 YEARS', 'SMALL', 'YELLOW / BLACK', 'BUDGERIGAR', 'F', 'Available', NULL, NULL, '2025-11-19 01:19:53'),
('A537693', 'OWNER SUR', '2025-11-15', 'SPOCK', 'CAT', '4 YEARS', 'MED', 'GRAY TAB', 'DOMESTIC SH', 'N', 'Available', NULL, NULL, '2025-11-19 01:19:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `adopters`
--
ALTER TABLE `adopters`
  ADD PRIMARY KEY (`adopter_id`);

--
-- Indexes for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `adopter_id` (`adopter_id`),
  ADD KEY `animal_id` (`animal_id`);

--
-- Indexes for table `application_notes`
--
ALTER TABLE `application_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`animal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `adopters`
--
ALTER TABLE `adopters`
  MODIFY `adopter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `application_notes`
--
ALTER TABLE `application_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD CONSTRAINT `adoption_applications_ibfk_1` FOREIGN KEY (`adopter_id`) REFERENCES `adopters` (`adopter_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoption_applications_ibfk_2` FOREIGN KEY (`animal_id`) REFERENCES `pets` (`animal_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_notes`
--
ALTER TABLE `application_notes`
  ADD CONSTRAINT `application_notes_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `adoption_applications` (`application_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_notes_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `adoption_applications` (`application_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
