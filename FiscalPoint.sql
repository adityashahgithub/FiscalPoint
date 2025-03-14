-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 14, 2025 at 10:21 AM
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
  `Bid` int(11) NOT NULL,
  `Uid` int(11) DEFAULT NULL,
  `Month` varchar(20) NOT NULL,
  `Amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Budget`
--

INSERT INTO `Budget` (`Bid`, `Uid`, `Month`, `Amount`) VALUES
(1, 12, 'March', 2000.00),
(2, 12, 'March', 2000.00),
(3, 22, '2025-04', 4000.00),
(6, 22, 'April', 2000.00),
(8, 22, 'March', 200000.00);

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
(8, NULL, 'Housing', 563.00, '2025-03-07', 'veggies', 'Credit / Debit card'),
(9, 12, 'Housing', 199.00, '2025-03-10', 'veggies', 'Cash'),
(10, 12, 'Transportation', 200.00, '2025-03-11', 'burger', 'Cash'),
(11, 22, 'Housing', 100.00, '2025-03-12', 'veggies', 'Cash'),
(12, 22, 'Transportation', 200.00, '2025-03-12', 'bus', 'Cash'),
(13, 22, 'Entertainment', 500.00, '2025-03-13', 'netflix', 'Credit / Debit card'),
(14, 22, 'Personal Care', 500.00, '2025-03-14', 'skin carre', 'Credit / Debit card'),
(15, 22, 'Insurance', 300.00, '2025-03-14', 'car', 'Cash'),
(16, 22, 'Food', 200.00, '2025-03-14', 'burger', 'Cash'),
(17, 22, 'Healthcare', 200.00, '2025-03-14', 'icu', 'Cash'),
(18, 22, 'Education', 200.00, '2025-03-14', 'fees', 'Cash');

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
(9, 'Adi Shah', 'adishah12@gmail.com', '$2y$10$FhxbPCmbO/AHrPrGm9kFZehZPFuoh9cA7EOUnBKnpnxnQKG/4Xx0C', '9876543210', '2025-03-07 06:04:52'),
(10, 'Suzzan Rafiquahemad Patel', 's103@gmail.com', '$2y$10$sfCzuz7ag3kyTE9yVTDkVuAXU/UwQZ3jWrP1Oh4U1PgdgEN9ga3G.', '9898006097', '2025-03-10 12:28:20'),
(11, 'Suzzan Rafiquahemad Patel', 'Su123@gmail.com', '$2y$10$OJidAz87Imq9rtnRV8mIMeOK2bGGlj.WiWhd/bueOF5WojEDNKf7y', '9898006097', '2025-03-10 12:29:26'),
(12, 'Suzzan Rafiquahemad Patel', 's123@gmail.com', '$2y$10$RnzaSTyZeeIsFxWBc1VaKOEGovavAt247yEMXcGN.S7rrK9WPNVqC', '9898006097', '2025-03-10 12:34:21'),
(13, 'Suzzan Rafiquahemad Patel', 'Suzz@gmail.com', '$2y$10$69dSw7jdjhRV5KkpUWniPe87Mh/9.wHnk8Qvftb1Rta6q3lMbcKEO', '9898006097', '2025-03-10 15:01:28'),
(14, 'Suzzan Rafiquahemad Patel', 's1234@gmail.com', '$2y$10$69S2ItyLfUucalUxpXU5G.rG2Y3kmDBOh8IoO.1lo26cvs.GQmZCS', '9898006097', '2025-03-12 05:45:45'),
(15, 'suz', 's1@gmail.com', '$2y$10$NiLv/xGfbjMvBhsC5EEp9e5leqm6Tzwxifh0MTaEmItmL4A6avBc2', '9898006097', '2025-03-12 05:49:58'),
(16, 'Suzzan Rafiquahemad Patel', 's2@gmail.com', '$2y$10$C.ejfDzFnMOB2APAII0lBuSh2Wc49AKT7WrS.yJIBOLEAvOSShFzC', '9898006097', '2025-03-12 05:52:17'),
(17, 'Suzzan Rafiquahemad Patel', 's3@gmail.com', '$2y$10$pqzUR6xz8DoEsBhfeOUgkuV2w8BhmZqg95.DEalmJXbJjgf3ax71u', '9898006097', '2025-03-12 05:54:46'),
(18, 'Suzzan Rafiquahemad Patel', 's4@gmail.com', '$2y$10$YoG/THDCBh/XqN8lsRTlIeRvALms06GLdZe/xGbJveKgRSG6ciJrC', '9898006097', '2025-03-12 05:55:20'),
(19, 'Suzzan Rafiquahemad Patel', 's0@gmail.com', '$2y$10$sMSZ0BaYNmIFdcJ1Lda7neBO/9HGUfdsP2F17Bj2R5Jbw1eNuEVOu', '9898006097', '2025-03-12 06:09:22'),
(20, 'Suzzan Rafiquahemad Patel', 's1200@gmail.com', '$2y$10$BNDl2ofVyfXnCI34bzwERudzEkdP2YuoxrbdaCrVDzbGuhauojshC', '9898006097', '2025-03-12 06:11:58'),
(21, 'Suzzan Rafiquahemad Patel', 'srp@gmail.com', '$2y$10$HkQePTNi5jmvO7F8S/IGcO4iQPhKk1IZ5fmLDh8T3RfWd1hpIW9Sy', '9898006097', '2025-03-12 06:16:30'),
(22, 'Suzzan Rafiquahemad Patel', 'sus@gmail.com', '$2y$10$Wb7h4juXvmzWMMnTV7ev7eN.1r4zG1UxBUoH/qRnmQug9WnZpojV2', '9898006097', '2025-03-12 06:20:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Budget`
--
ALTER TABLE `Budget`
  ADD PRIMARY KEY (`Bid`),
  ADD KEY `Uid` (`Uid`);

--
-- Indexes for table `Expense`
--
ALTER TABLE `Expense`
  ADD PRIMARY KEY (`Eid`),
  ADD KEY `Uid` (`Uid`);

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
  MODIFY `Bid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Expense`
--
ALTER TABLE `Expense`
  MODIFY `Eid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `Uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Budget`
--
ALTER TABLE `Budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`Uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE;

--
-- Constraints for table `Expense`
--
ALTER TABLE `Expense`
  ADD CONSTRAINT `expense_ibfk_1` FOREIGN KEY (`Uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
