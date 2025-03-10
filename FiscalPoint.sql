-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 10, 2025 at 09:29 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `FiscalPoint`
--

-- --------------------------------------------------------

--
-- Table structure for table `Budget`
--

CREATE TABLE `Budget` (
  `bid` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Expense`
--

CREATE TABLE `Expense` (
  `Eid` int(11) NOT NULL,
  `Uid` int(11) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `Payment_Method` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Expense`
--

INSERT INTO `Expense` (`Eid`, `Uid`, `category`, `amount`, `date`, `description`, `Payment_Method`) VALUES
(1, NULL, 'Housing', 0.00, '2025-03-07', '', ''),
(2, NULL, 'Housing', 0.00, '2025-03-07', '', ''),
(3, NULL, 'Education', 0.00, '2025-03-07', '', ''),
(4, NULL, 'Housing', 0.00, '2025-03-07', '', ''),
(5, NULL, 'Food', 0.00, '2025-03-07', '', ''),
(6, NULL, 'Childcare/Dependents', 424.00, '2025-03-07', 'veggies', ''),
(7, NULL, 'Housing', 123.00, '2025-03-07', 'veggies', 'Other'),
(8, NULL, 'Housing', 563.00, '2025-03-07', 'veggies', 'Credit / Debit card');

-- --------------------------------------------------------

--
-- Table structure for table `Saving`
--

CREATE TABLE `Saving` (
  `sid` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `goal_amount` decimal(10,2) NOT NULL,
  `target_date` date NOT NULL,
  `amount_saved` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `Uid` int(11) NOT NULL,
  `Uname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Phone_no` varchar(10) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`Uid`, `Uname`, `email`, `Password`, `Phone_no`, `Created_At`) VALUES
(1, '', 'suzan10@gmail.com', '$2y$10$UD0B4bQquu6CTTmPolrpleDU5qB43Pi43Gl90iUeroTPJpKazzml2', '', '2025-03-06 23:34:49'),
(2, '', 'suzan@gmail.com', '$2y$10$KTsieCE1Wtg.wyoKD1dhbOn4h505oDUfbJBiAWqkjXg.18oC4D4Re', '', '2025-03-06 23:37:33'),
(3, 'Suzzan Rafiquahemad Patel', 'suzzanpatel10@gmail.com', '$2y$10$6AcwVAqhqq18ps.Vjyz/HeFZZru1pQZ5KZTTJ9X7A7dfC9lS6CXye', '9898006097', '2025-03-06 23:39:03'),
(4, 'Suzzan Rafiquahemad Patel', 'suzomc@gmail.com', '$2y$10$HqOY.y.J7MbF5eM47lbtduPXF7x7xyoXmjVKJDSfF1ZrI5nX6g3ey', '9898006097', '2025-03-06 23:58:53'),
(5, 'Suzzan Rafiquahemad Patel', 'suzzanp108@gmail.com', '$2y$10$uT7vduN/Ry.ozMIWLIQ3muGJpZicF.KH6GUPQl3Ug/5hPKkbWW4L6', '9898006097', '2025-03-07 00:01:50'),
(6, 'Adi Shah', 'adishah123@gmail.com', '$2y$10$bwa9Habk/WadWjtbxn.MRuzuFz5yftfOKed7lcOuFYovJPKOx7JOm', '1234567890', '2025-03-07 00:13:23'),
(7, 'Suzzan Rafiquahemad Patel', 'su103@gmail.com', '$2y$10$xYwmvXwH74eh5uQEiGQvc.U/maeFPaH0ARIkzHwJVvJPgQTbM26x.', '9898006097', '2025-03-07 03:16:46'),
(8, 'pratham soni', 'prathamsoni4545@gmail.com', '$2y$10$VKHNQtEG.XOONKcdpLner.APn6HjLzTdFH8Xz.IUUDfflT0mM8T2u', '8320351640', '2025-03-07 06:01:06'),
(9, 'Adi Shah', 'adishah12@gmail.com', '$2y$10$FhxbPCmbO/AHrPrGm9kFZehZPFuoh9cA7EOUnBKnpnxnQKG/4Xx0C', '9876543210', '2025-03-07 06:04:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Budget`
--
ALTER TABLE `Budget`
  ADD PRIMARY KEY (`bid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `Expense`
--
ALTER TABLE `Expense`
  ADD PRIMARY KEY (`Eid`),
  ADD KEY `Uid` (`Uid`);

--
-- Indexes for table `Saving`
--
ALTER TABLE `Saving`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`Uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Budget`
--
ALTER TABLE `Budget`
  MODIFY `bid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Expense`
--
ALTER TABLE `Expense`
  MODIFY `Eid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Saving`
--
ALTER TABLE `Saving`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `Uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Budget`
--
ALTER TABLE `Budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Expense`
--
ALTER TABLE `Expense`
  ADD CONSTRAINT `expense_ibfk_1` FOREIGN KEY (`Uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Saving`
--
ALTER TABLE `Saving`
  ADD CONSTRAINT `saving_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
