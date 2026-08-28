-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2026 at 01:57 PM
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
-- Database: `community_item_sharing`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `borrow_date` date DEFAULT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Returned') DEFAULT 'Pending',
  `owner_message` varchar(255) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_date` datetime DEFAULT NULL,
  `returned_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`request_id`, `item_id`, `borrower_id`, `owner_id`, `borrow_date`, `expected_return_date`, `actual_return_date`, `status`, `owner_message`, `request_date`, `approved_date`, `returned_date`) VALUES
(1, 5, 2, 1, '2026-08-18', '2026-09-03', '2026-08-03', 'Returned', NULL, '2026-08-02 06:35:10', NULL, '2026-08-03 08:02:06'),
(2, 6, 1, 2, '2026-08-29', '2026-08-29', '2026-08-03', 'Returned', NULL, '2026-08-02 06:52:47', NULL, '2026-08-03 07:56:27'),
(4, 6, 1, 2, '2026-08-05', '2026-08-27', '2026-08-12', 'Returned', NULL, '2026-08-05 08:25:07', '2026-08-05 15:26:16', '2026-08-12 16:35:19'),
(5, 8, 1, 2, '2026-08-18', '2026-08-19', '2026-08-12', 'Returned', NULL, '2026-08-12 11:04:23', '2026-08-12 16:35:24', '2026-08-12 16:36:00'),
(6, 5, 2, 1, '2026-08-12', '2026-08-13', '2026-08-12', 'Returned', NULL, '2026-08-12 11:08:22', '2026-08-12 16:38:51', '2026-08-12 16:42:23'),
(7, 8, 1, 2, '2026-08-25', '2026-08-26', NULL, 'Approved', NULL, '2026-08-25 11:48:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'data structure book'),
(2, 'Scientific Calculator'),
(3, 'Laptop'),
(4, 'Project Kit'),
(5, 'Lab Equipment'),
(6, 'Sports Equipment'),
(7, 'Electronics'),
(8, 'Stationery');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `item_condition` varchar(50) DEFAULT NULL,
  `availability` enum('Available','Borrowed') DEFAULT 'Available',
  `location` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `user_id`, `category_id`, `item_name`, `description`, `item_condition`, `availability`, `location`, `image`, `created_at`) VALUES
(3, 1, 1, 'data visualization book', 'used for exam', 'Good', 'Available', 'kottayam', '1785651771_data visualization.jpg', '2026-08-02 06:22:51'),
(4, 1, 1, 'java', 'used for exam', 'Good', 'Available', 'kottayam', '1785651841_java.jpg', '2026-08-02 06:24:01'),
(5, 1, 1, 'data structurebook', 'used in computer science students', 'Good', 'Available', 'kottayam', '1785651940_datastructurebook.jpeg', '2026-08-02 06:25:40'),
(6, 2, 2, 'scientific calculator', 'used for students', 'Good', 'Available', 'kottayam', '1785652650_c.jpg.jpg', '2026-08-02 06:37:30'),
(8, 2, 6, 'badminton kit', 'used for students', 'Good', 'Borrowed', 'kottayam', '1785923761_badmintonkit.jpg', '2026-08-05 09:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('Request','Approved','Rejected','Returned','Reminder','Overdue') NOT NULL,
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `request_id`, `title`, `message`, `type`, `status`, `created_at`) VALUES
(1, 2, 1, 'Item Returned', 'The item \"data structurebook\" has been marked as returned successfully.', 'Returned', 'Read', '2026-08-03 02:32:06'),
(2, 2, 3, 'Request Approved', 'Your borrow request for badminton kit has been approved.', 'Approved', 'Read', '2026-08-03 02:58:57'),
(3, 2, 3, 'Return Reminder', 'Your borrowed item \'badminton kit\' should be returned tomorrow.', 'Reminder', 'Read', '2026-08-03 03:40:57'),
(4, 2, 3, 'Item Returned', 'The item \"badminton kit\" has been marked as returned successfully.', 'Returned', 'Read', '2026-08-03 04:13:50'),
(5, 1, 4, 'Request Approved', 'Your borrow request for scientific calculator has been approved.', 'Approved', 'Unread', '2026-08-05 09:56:16'),
(6, 1, 4, 'Item Returned', 'The item \"scientific calculator\" has been marked as returned successfully.', 'Returned', 'Unread', '2026-08-12 11:05:19'),
(7, 1, 5, 'Request Approved', 'Your borrow request for badminton kit has been approved.', 'Approved', 'Unread', '2026-08-12 11:05:24'),
(8, 1, 5, 'Item Returned', 'The item \"badminton kit\" has been marked as returned successfully.', 'Returned', 'Unread', '2026-08-12 11:06:00'),
(9, 2, 6, 'Request Approved', 'Your borrow request for data structurebook has been approved.', 'Approved', 'Read', '2026-08-12 11:08:52'),
(10, 2, 6, 'Return Reminder', 'Your borrowed item \'data structurebook\' should be returned tomorrow.', 'Reminder', 'Read', '2026-08-12 11:09:36'),
(11, 2, 6, 'Item Returned', 'The item \"data structurebook\" has been marked as returned successfully.', 'Returned', 'Unread', '2026-08-12 11:12:23'),
(12, 1, 7, 'Return Reminder', 'Your borrowed item \'badminton kit\' should be returned tomorrow.', 'Reminder', 'Unread', '2026-08-25 11:51:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `year_of_study` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `security_question` varchar(255) NOT NULL,
  `security_answer` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `department`, `year_of_study`, `password`, `security_question`, `security_answer`, `profile_image`, `created_at`) VALUES
(1, 'Krishnapriya ', 'krishnapriyaintenship@gmail.com', '7654890123', 'BSc Computer Science', '', '$2y$10$.xQ.E3av8x2FHcL3ROxGo.S2nmj8C9rzLPEx9x4AMM/Vru5aDoR9y', '', '', '1785637659_krishna.jpg', '2026-08-02 02:27:39'),
(2, 'unni', 'unnikrishnankalarickal039@gmail.com', '9087654321', 'BA English', '1st Year', '$2y$10$PwN.FHmYfyW63ai8jaGswOULeMTg5TD5N6DW/FAHl1riOARrHZWrG', '', '', 'default.png', '2026-08-02 06:34:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
