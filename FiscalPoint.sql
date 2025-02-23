-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 23, 2025 at 02:09 PM
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
-- Table structure for table `Category`
--

CREATE TABLE `Category` (
  `cid` int(11) NOT NULL,
  `cname` varchar(255) NOT NULL
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
  `Pid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Expense_Summary`
--

CREATE TABLE `Expense_Summary` (
  `esid` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `report_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Payment_Method`
--

CREATE TABLE `Payment_Method` (
  `Pid` int(11) NOT NULL,
  `Uid` int(11) DEFAULT NULL,
  `ptype` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Report`
--

CREATE TABLE `Report` (
  `rid` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `report_month` varchar(7) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Savings`
--

CREATE TABLE `Savings` (
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
  `Phone_no` int(10) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`cid`),
  ADD UNIQUE KEY `cname` (`cname`);

--
-- Indexes for table `Expense`
--
ALTER TABLE `Expense`
  ADD PRIMARY KEY (`Eid`),
  ADD KEY `Uid` (`Uid`),
  ADD KEY `Pid` (`Pid`);

--
-- Indexes for table `Expense_Summary`
--
ALTER TABLE `Expense_Summary`
  ADD PRIMARY KEY (`esid`),
  ADD KEY `uid` (`uid`),
  ADD KEY `cid` (`cid`);

--
-- Indexes for table `Payment_Method`
--
ALTER TABLE `Payment_Method`
  ADD PRIMARY KEY (`Pid`),
  ADD UNIQUE KEY `ptype` (`ptype`),
  ADD KEY `Uid` (`Uid`);

--
-- Indexes for table `Report`
--
ALTER TABLE `Report`
  ADD PRIMARY KEY (`rid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `Savings`
--
ALTER TABLE `Savings`
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
-- AUTO_INCREMENT for table `Category`
--
ALTER TABLE `Category`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Expense`
--
ALTER TABLE `Expense`
  MODIFY `Eid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Expense_Summary`
--
ALTER TABLE `Expense_Summary`
  MODIFY `esid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Payment_Method`
--
ALTER TABLE `Payment_Method`
  MODIFY `Pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Report`
--
ALTER TABLE `Report`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Savings`
--
ALTER TABLE `Savings`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `Uid` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Budget`
--
ALTER TABLE `Budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE;

--
-- Constraints for table `Expense`
--
ALTER TABLE `Expense`
  ADD CONSTRAINT `expense_ibfk_1` FOREIGN KEY (`Uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `expense_ibfk_2` FOREIGN KEY (`Pid`) REFERENCES `Payment_Method` (`Pid`) ON DELETE SET NULL;

--
-- Constraints for table `Expense_Summary`
--
ALTER TABLE `Expense_Summary`
  ADD CONSTRAINT `expense_summary_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `expense_summary_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `Category` (`cid`) ON DELETE SET NULL;

--
-- Constraints for table `Payment_Method`
--
ALTER TABLE `Payment_Method`
  ADD CONSTRAINT `payment_method_ibfk_1` FOREIGN KEY (`Uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE;

--
-- Constraints for table `Report`
--
ALTER TABLE `Report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE;

--
-- Constraints for table `Savings`
--
ALTER TABLE `Savings`
  ADD CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `User` (`Uid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
