-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2026 at 12:09 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL DEFAULT 0,
  `room_id` int(11) NOT NULL DEFAULT 0,
  `nurse_id` int(11) NOT NULL DEFAULT 0,
  `doctor_id` int(11) NOT NULL,
  `date_appointed` datetime NOT NULL DEFAULT current_timestamp(),
  `date_ended` datetime DEFAULT NULL,
  `nurse_notes` text NOT NULL DEFAULT '',
  `diagnosis` text NOT NULL DEFAULT '\'\'',
  `clinical_notes` text NOT NULL,
  `pharmacy_notes` text NOT NULL DEFAULT '',
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `assign_doctors`
--

CREATE TABLE `assign_doctors` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `assigned_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `status`) VALUES
(1, 'Antibiotics', 1),
(2, 'Syrubs', 1);

-- --------------------------------------------------------

--
-- Table structure for table `drugs`
--

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `dosage_form` enum('Tablet','Capsule','Syrup','Injection','Ointment','Cream','Drops','Inhaler') NOT NULL,
  `strength` varchar(100) DEFAULT NULL,
  `route` enum('Oral','IV','IM','SC','Topical','Inhalation') DEFAULT NULL,
  `units_per_pack` int(11) NOT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `minimum_stock_level` int(11) DEFAULT 10,
  `status` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `drugs`
--

INSERT INTO `drugs` (`id`, `drug_name`, `generic_name`, `category`, `dosage_form`, `strength`, `route`, `units_per_pack`, `manufacturer`, `cost_price`, `selling_price`, `minimum_stock_level`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'paracetamol', 1, 'Tablet', '500mg', 'IM', 30, 'Badin', '200.00', '300.00', 10, 1, '2026-01-19 16:50:47', '2026-01-20 14:03:27'),
(2, 'Cough Syrub', 'Cough Syrub', 2, 'Syrup', '300mg', 'Oral', 1, 'Badin', '400.00', '450.00', 5, 1, '2026-01-25 23:17:33', '2026-01-25 23:17:33');

-- --------------------------------------------------------

--
-- Table structure for table `drug_list`
--

CREATE TABLE `drug_list` (
  `id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `prescription` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `amount` float NOT NULL,
  `patient_drugs_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `drug_list`
--

INSERT INTO `drug_list` (`id`, `drug_id`, `prescription`, `quantity`, `amount`, `patient_drugs_id`) VALUES
(4, 1, '2 each', 2, 600, 6),
(5, 1, '2 times', 3, 900, 7);

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `phone` text NOT NULL,
  `email` text NOT NULL,
  `address` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `families`
--

INSERT INTO `families` (`id`, `name`, `phone`, `email`, `address`, `status`) VALUES
(1, 'Mr abbare', '09064057921', 'admin@gmail.com', 'No 3 ogafa street', 1),
(2, 'Adamu Family', '09064057928', 'ibb@gmail.com', 'birnin kebbi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `file_types`
--

CREATE TABLE `file_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `amount` float NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `file_types`
--

INSERT INTO `file_types` (`id`, `name`, `amount`, `status`) VALUES
(1, 'Family', 2500, 1),
(2, 'Individual', 6000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `hospital_details`
--

CREATE TABLE `hospital_details` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `address` text NOT NULL,
  `logo` text NOT NULL,
  `website` text NOT NULL,
  `year` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `consultation_fee` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hospital_details`
--

INSERT INTO `hospital_details` (`id`, `name`, `email`, `phone`, `address`, `logo`, `website`, `year`, `user_id`, `consultation_fee`) VALUES
(1, 'Godiya Hospital', 'gh@gmail.com', '09065478965', 'Badariyya', 'hospital_6965711592a560.19366332.jpeg', '', '2005', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lgas`
--

CREATE TABLE `lgas` (
  `id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lgas`
--

INSERT INTO `lgas` (`id`, `state_id`, `name`, `price`) VALUES
(1, 1, 'Aba North', 0),
(2, 1, 'Aba South', 0),
(3, 1, 'Arochukwu', 0),
(4, 1, 'Bende', 0),
(5, 1, 'Ikwuano', 0),
(6, 1, 'Isiala Ngwa North', 0),
(7, 1, 'Isiala Ngwa South', 0),
(8, 1, 'Isuikwuato', 0),
(9, 1, 'Obi Ngwa', 0),
(10, 1, 'Ohafia', 0),
(11, 1, 'Osisioma', 0),
(12, 1, 'Ugwunagbo', 0),
(13, 1, 'Ukwa East', 0),
(14, 1, 'Ukwa West', 0),
(15, 1, 'Umuahia North', 0),
(16, 1, 'Umuahia South', 0),
(17, 1, 'Umu Nneochi', 0),
(18, 2, 'Demsa', 2000),
(19, 2, 'Fufore', 0),
(20, 2, 'Ganye', 0),
(21, 2, 'Girei', 0),
(22, 2, 'Gombi', 0),
(23, 2, 'Guyuk', 0),
(24, 2, 'Hong', 0),
(25, 2, 'Jada', 0),
(26, 2, 'Lamurde', 0),
(27, 2, 'Madagali', 0),
(28, 2, 'Maiha', 0),
(29, 2, 'Mayo-Belwa', 0),
(30, 2, 'Michika', 0),
(31, 2, 'Mubi North', 0),
(32, 2, 'Mubi South', 0),
(33, 2, 'Numan', 0),
(34, 2, 'Shelleng', 0),
(35, 2, 'Song', 0),
(36, 2, 'Toungo', 0),
(37, 2, 'Yola North', 0),
(38, 2, 'Yola South', 0),
(39, 3, 'Abak', 0),
(40, 3, 'Eastern Obolo', 0),
(41, 3, 'Eket', 0),
(42, 3, 'Esit Eket', 0),
(43, 3, 'Essien Udim', 0),
(44, 3, 'Etim Ekpo', 0),
(45, 3, 'Etinan', 0),
(46, 3, 'Ibeno', 0),
(47, 3, 'Ibesikpo Asutan', 0),
(48, 3, 'Ibiono-Ibom', 0),
(49, 3, 'Ika', 0),
(50, 3, 'Ikono', 0),
(51, 3, 'Ikot Abasi', 0),
(52, 3, 'Ikot Ekpene', 0),
(53, 3, 'Ini', 0),
(54, 3, 'Itu', 0),
(55, 3, 'Mbo', 0),
(56, 3, 'Mkpat-Enin', 0),
(57, 3, 'Nsit-Atai', 0),
(58, 3, 'Nsit-Ibom', 0),
(59, 3, 'Nsit-Ubium', 0),
(60, 3, 'Obot Akara', 0),
(61, 3, 'Okobo', 0),
(62, 3, 'Onna', 0),
(63, 3, 'Oron', 0),
(64, 3, 'Oruk Anam', 0),
(65, 3, 'Udung-Uko', 0),
(66, 3, 'Ukanafun', 0),
(67, 3, 'Uruan', 0),
(68, 3, 'Urue-Offong/Oruko', 0),
(69, 3, 'Uyo', 0),
(70, 4, 'Aguata', 0),
(71, 4, 'Anambra East', 0),
(72, 4, 'Anambra West', 0),
(73, 4, 'Anaocha', 0),
(74, 4, 'Awka North', 0),
(75, 4, 'Awka South', 0),
(76, 4, 'Ayamelum', 0),
(77, 4, 'Dunukofia', 0),
(78, 4, 'Ekwusigo', 0),
(79, 4, 'Idemili North', 0),
(80, 4, 'Idemili South', 0),
(81, 4, 'Ihiala', 0),
(82, 4, 'Njikoka', 0),
(83, 4, 'Nnewi North', 0),
(84, 4, 'Nnewi South', 0),
(85, 4, 'Ogbaru', 0),
(86, 4, 'Onitsha North', 0),
(87, 4, 'Onitsha South', 0),
(88, 4, 'Orumba North', 0),
(89, 4, 'Orumba South', 0),
(90, 4, 'Oyi', 0),
(91, 5, 'Alkaleri', 0),
(92, 5, 'Bauchi', 0),
(93, 5, 'Bogoro', 0),
(94, 5, 'Damban', 0),
(95, 5, 'Darazo', 0),
(96, 5, 'Dass', 0),
(97, 5, 'Ganjuwa', 0),
(98, 5, 'Giade', 0),
(99, 5, 'Itas/Gadau', 0),
(100, 5, 'Jama\'are', 0),
(101, 5, 'Katagum', 0),
(102, 5, 'Kirfi', 0),
(103, 5, 'Misau', 0),
(104, 5, 'Ningi', 0),
(105, 5, 'Shira', 0),
(106, 5, 'Tafawa Balewa', 0),
(107, 5, 'Toro', 0),
(108, 5, 'Warji', 0),
(109, 5, 'Zaki', 0),
(110, 6, 'Brass', 0),
(111, 6, 'Ekeremor', 0),
(112, 6, 'Kolokuma/Opokuma', 0),
(113, 6, 'Nembe', 0),
(114, 6, 'Ogbia', 0),
(115, 6, 'Sagbama', 0),
(116, 6, 'Southern Ijaw', 0),
(117, 6, 'Yenagoa', 0),
(118, 7, 'Ado', 0),
(119, 7, 'Agatu', 0),
(120, 7, 'Apa', 0),
(121, 7, 'Buruku', 0),
(122, 7, 'Gboko', 0),
(123, 7, 'Guma', 0),
(124, 7, 'Gwer East', 0),
(125, 7, 'Gwer West', 0),
(126, 7, 'Katsina-Ala', 0),
(127, 7, 'Konshisha', 0),
(128, 7, 'Kwande', 0),
(129, 7, 'Logo', 0),
(130, 7, 'Makurdi', 0),
(131, 7, 'Obi', 0),
(132, 7, 'Ogbadibo', 0),
(133, 7, 'Ohimini', 0),
(134, 7, 'Oju', 0),
(135, 7, 'Okpokwu', 0),
(136, 7, 'Otukpo', 0),
(137, 7, 'Tarka', 0),
(138, 7, 'Ukum', 0),
(139, 7, 'Ushongo', 0),
(140, 7, 'Vandeikya', 0),
(141, 8, 'Abadam', 0),
(142, 8, 'Askira/Uba', 0),
(143, 8, 'Bama', 0),
(144, 8, 'Bayo', 0),
(145, 8, 'Biu', 0),
(146, 8, 'Chibok', 0),
(147, 8, 'Damboa', 0),
(148, 8, 'Dikwa', 0),
(149, 8, 'Gubio', 0),
(150, 8, 'Guzamala', 0),
(151, 8, 'Gwoza', 0),
(152, 8, 'Hawul', 0),
(153, 8, 'Jere', 0),
(154, 8, 'Kaga', 0),
(155, 8, 'Kala/Balge', 0),
(156, 8, 'Konduga', 0),
(157, 8, 'Kukawa', 0),
(158, 8, 'Kwaya Kusar', 0),
(159, 8, 'Mafa', 0),
(160, 8, 'Magumeri', 0),
(161, 8, 'Maiduguri', 0),
(162, 8, 'Marte', 0),
(163, 8, 'Mobbar', 0),
(164, 8, 'Monguno', 0),
(165, 8, 'Ngala', 0),
(166, 8, 'Nganzai', 0),
(167, 8, 'Shani', 0),
(168, 9, 'Abi', 0),
(169, 9, 'Akamkpa', 0),
(170, 9, 'Akpabuyo', 0),
(171, 9, 'Bakassi', 0),
(172, 9, 'Bekwarra', 0),
(173, 9, 'Biase', 0),
(174, 9, 'Boki', 0),
(175, 9, 'Calabar Municipal', 0),
(176, 9, 'Calabar South', 0),
(177, 9, 'Etung', 0),
(178, 9, 'Ikom', 0),
(179, 9, 'Obanliku', 0),
(180, 9, 'Obubra', 0),
(181, 9, 'Obudu', 0),
(182, 9, 'Odukpani', 0),
(183, 9, 'Ogoja', 0),
(184, 9, 'Yakuur', 0),
(185, 9, 'Yala', 0),
(186, 10, 'Aniocha North', 0),
(187, 10, 'Aniocha South', 0),
(188, 10, 'Bomadi', 0),
(189, 10, 'Burutu', 0),
(190, 10, 'Ethiope East', 0),
(191, 10, 'Ethiope West', 0),
(192, 10, 'Ika North East', 0),
(193, 10, 'Ika South', 0),
(194, 10, 'Isoko North', 0),
(195, 10, 'Isoko South', 0),
(196, 10, 'Ndokwa East', 0),
(197, 10, 'Ndokwa West', 0),
(198, 10, 'Okpe', 0),
(199, 10, 'Oshimili North', 0),
(200, 10, 'Oshimili South', 0),
(201, 10, 'Patani', 0),
(202, 10, 'Sapele', 0),
(203, 10, 'Udu', 0),
(204, 10, 'Ughelli North', 0),
(205, 10, 'Ughelli South', 0),
(206, 10, 'Ukwuani', 0),
(207, 10, 'Uvwie', 0),
(208, 10, 'Warri North', 0),
(209, 10, 'Warri South', 0),
(210, 10, 'Warri South West', 0),
(211, 11, 'Abakaliki', 0),
(212, 11, 'Afikpo North', 0),
(213, 11, 'Afikpo South', 0),
(214, 11, 'Ebonyi', 0),
(215, 11, 'Ezza North', 0),
(216, 11, 'Ezza South', 0),
(217, 11, 'Ikwo', 0),
(218, 11, 'Ishielu', 0),
(219, 11, 'Ivo', 0),
(220, 11, 'Izzi', 0),
(221, 11, 'Ohaukwu', 0),
(222, 11, 'Onicha', 0),
(223, 11, 'Ohaozara', 0),
(224, 12, 'Akoko-Edo', 0),
(225, 12, 'Egor', 0),
(226, 12, 'Esan Central', 0),
(227, 12, 'Esan North-East', 0),
(228, 12, 'Esan South-East', 0),
(229, 12, 'Esan West', 0),
(230, 12, 'Etsako Central', 0),
(231, 12, 'Etsako East', 0),
(232, 12, 'Etsako West', 0),
(233, 12, 'Igueben', 0),
(234, 12, 'Ikpoba-Okha', 0),
(235, 12, 'Oredo', 0),
(236, 12, 'Orhionmwon', 0),
(237, 12, 'Ovia North-East', 0),
(238, 12, 'Ovia South-West', 0),
(239, 12, 'Owan East', 0),
(240, 12, 'Owan West', 0),
(241, 12, 'Uhunmwonde', 0),
(242, 13, 'Ado-Ekiti', 0),
(243, 13, 'Efon', 0),
(244, 13, 'Ekiti East', 0),
(245, 13, 'Ekiti South-West', 0),
(246, 13, 'Ekiti West', 0),
(247, 13, 'Emure', 0),
(248, 13, 'Gbonyin', 0),
(249, 13, 'Ido-Osi', 0),
(250, 13, 'Ijero', 0),
(251, 13, 'Ikere', 0),
(252, 13, 'Ikole', 0),
(253, 13, 'Ilejemeje', 0),
(254, 13, 'Irepodun/Ifelodun', 0),
(255, 13, 'Moba', 0),
(256, 13, 'Oye', 0),
(257, 14, 'Aninri', 0),
(258, 14, 'Awgu', 0),
(259, 14, 'Enugu East', 0),
(260, 14, 'Enugu North', 0),
(261, 14, 'Enugu South', 0),
(262, 14, 'Ezeagu', 0),
(263, 14, 'Igbo-Etiti', 0),
(264, 14, 'Igbo-Eze North', 0),
(265, 14, 'Igbo-Eze South', 0),
(266, 14, 'Isi Uzo', 0),
(267, 14, 'Nkanu East', 0),
(268, 14, 'Nkanu West', 0),
(269, 14, 'Nsukka', 0),
(270, 14, 'Oji River', 0),
(271, 14, 'Udenu', 0),
(272, 14, 'Udi', 0),
(273, 14, 'Uzo-Uwani', 0),
(274, 15, 'Akko', 0),
(275, 15, 'Balanga', 0),
(276, 15, 'Billiri', 0),
(277, 15, 'Dukku', 0),
(278, 15, 'Funakaye', 0),
(279, 15, 'Gombe', 0),
(280, 15, 'Kaltungo', 0),
(281, 15, 'Kwami', 0),
(282, 15, 'Nafada', 0),
(283, 15, 'Shongom', 0),
(284, 15, 'Yamaltu/Deba', 0),
(285, 16, 'Aboh Mbaise', 0),
(286, 16, 'Ahiazu Mbaise', 0),
(287, 16, 'Ehime Mbano', 0),
(288, 16, 'Ezinihitte', 0),
(289, 16, 'Ideato North', 0),
(290, 16, 'Ideato South', 0),
(291, 16, 'Ihitte/Uboma', 0),
(292, 16, 'Ikeduru', 0),
(293, 16, 'Isiala Mbano', 0),
(294, 16, 'Isu', 0),
(295, 16, 'Mbaitoli', 0),
(296, 16, 'Ngor Okpala', 0),
(297, 16, 'Njaba', 0),
(298, 16, 'Nkwerre', 0),
(299, 16, 'Nwangele', 0),
(300, 16, 'Obowo', 0),
(301, 16, 'Oguta', 0),
(302, 16, 'Ohaji/Egbema', 0),
(303, 16, 'Okigwe', 0),
(304, 16, 'Orlu', 0),
(305, 16, 'Orsu', 0),
(306, 16, 'Oru East', 0),
(307, 16, 'Oru West', 0),
(308, 16, 'Owerri Municipal', 0),
(309, 16, 'Owerri North', 0),
(310, 16, 'Owerri West', 0),
(311, 16, 'Unuimo', 0),
(312, 17, 'Auyo', 0),
(313, 17, 'Babura', 0),
(314, 17, 'Biriniwa', 0),
(315, 17, 'Birnin Kudu', 0),
(316, 17, 'Buji', 0),
(317, 17, 'Dutse', 0),
(318, 17, 'Gagarawa', 0),
(319, 17, 'Garki', 0),
(320, 17, 'Gumel', 0),
(321, 17, 'Guri', 0),
(322, 17, 'Gwaram', 0),
(323, 17, 'Gwiwa', 0),
(324, 17, 'Hadejia', 0),
(325, 17, 'Jahun', 0),
(326, 17, 'Kafin Hausa', 0),
(327, 17, 'Kaugama', 0),
(328, 17, 'Kazaure', 0),
(329, 17, 'Kiri Kasama', 0),
(330, 17, 'Kiyawa', 0),
(331, 17, 'Maigatari', 0),
(332, 17, 'Malam Madori', 0),
(333, 17, 'Miga', 0),
(334, 17, 'Ringim', 0),
(335, 17, 'Roni', 0),
(336, 17, 'Sule Tankarkar', 0),
(337, 17, 'Taura', 0),
(338, 17, 'Yankwashi', 0),
(339, 18, 'Birnin Gwari', 0),
(340, 18, 'Chikun', 0),
(341, 18, 'Giwa', 0),
(342, 18, 'Igabi', 0),
(343, 18, 'Ikara', 0),
(344, 18, 'Jaba', 0),
(345, 18, 'Jema\'a', 0),
(346, 18, 'Kachia', 0),
(347, 18, 'Kaduna North', 0),
(348, 18, 'Kaduna South', 0),
(349, 18, 'Kagarko', 0),
(350, 18, 'Kajuru', 0),
(351, 18, 'Kaura', 0),
(352, 18, 'Kauru', 0),
(353, 18, 'Kubau', 0),
(354, 18, 'Kudan', 0),
(355, 18, 'Lere', 0),
(356, 18, 'Makarfi', 0),
(357, 18, 'Sabon Gari', 0),
(358, 18, 'Sanga', 0),
(359, 18, 'Soba', 0),
(360, 18, 'Zangon Kataf', 0),
(361, 18, 'Zaria', 0),
(362, 19, 'Ajingi', 0),
(363, 19, 'Albasu', 0),
(364, 19, 'Bagwai', 0),
(365, 19, 'Bebeji', 0),
(366, 19, 'Bichi', 0),
(367, 19, 'Bunkure', 0),
(368, 19, 'Dala', 0),
(369, 19, 'Dambatta', 0),
(370, 19, 'Dawakin Kudu', 0),
(371, 19, 'Dawakin Tofa', 0),
(372, 19, 'Doguwa', 0),
(373, 19, 'Fagge', 0),
(374, 19, 'Gabasawa', 0),
(375, 19, 'Garko', 0),
(376, 19, 'Garun Mallam', 0),
(377, 19, 'Gaya', 0),
(378, 19, 'Gezawa', 0),
(379, 19, 'Gwale', 0),
(380, 19, 'Gwarzo', 0),
(381, 19, 'Kabo', 0),
(382, 19, 'Kano Municipal', 0),
(383, 19, 'Karaye', 0),
(384, 19, 'Kibiya', 0),
(385, 19, 'Kiru', 0),
(386, 19, 'Kumbotso', 0),
(387, 19, 'Kunchi', 0),
(388, 19, 'Kura', 0),
(389, 19, 'Madobi', 0),
(390, 19, 'Makoda', 0),
(391, 19, 'Minjibir', 0),
(392, 19, 'Nasarawa', 0),
(393, 19, 'Rano', 0),
(394, 19, 'Rimin Gado', 0),
(395, 19, 'Rogo', 0),
(396, 19, 'Shanono', 0),
(397, 19, 'Sumaila', 0),
(398, 19, 'Takai', 0),
(399, 19, 'Tarauni', 0),
(400, 19, 'Tofa', 0),
(401, 19, 'Tsanyawa', 0),
(402, 19, 'Tudun Wada', 0),
(403, 19, 'Ungogo', 0),
(404, 19, 'Warawa', 0),
(405, 19, 'Wudil', 0),
(406, 20, 'Bakori', 0),
(407, 20, 'Batagarawa', 0),
(408, 20, 'Batsari', 0),
(409, 20, 'Baure', 0),
(410, 20, 'Bindawa', 0),
(411, 20, 'Charanchi', 0),
(412, 20, 'Dandume', 0),
(413, 20, 'Danja', 0),
(414, 20, 'Dan Musa', 0),
(415, 20, 'Daura', 0),
(416, 20, 'Dutsi', 0),
(417, 20, 'Dutsin-Ma', 0),
(418, 20, 'Faskari', 0),
(419, 20, 'Funtua', 0),
(420, 20, 'Ingawa', 0),
(421, 20, 'Jibia', 0),
(422, 20, 'Kafur', 0),
(423, 20, 'Kaita', 0),
(424, 20, 'Kankara', 0),
(425, 20, 'Kankia', 0),
(426, 20, 'Katsina', 0),
(427, 20, 'Kurfi', 0),
(428, 20, 'Kusada', 0),
(429, 20, 'Mai\'Adua', 0),
(430, 20, 'Malumfashi', 0),
(431, 20, 'Mani', 0),
(432, 20, 'Mashi', 0),
(433, 20, 'Matazu', 0),
(434, 20, 'Musawa', 0),
(435, 20, 'Rimi', 0),
(436, 20, 'Sabuwa', 0),
(437, 20, 'Safana', 0),
(438, 20, 'Sandamu', 0),
(439, 20, 'Zango', 0),
(440, 21, 'Aleiro', 0),
(441, 21, 'Arewa Dandi', 0),
(442, 21, 'Argungu', 0),
(443, 21, 'Augie', 0),
(444, 21, 'Bagudo', 0),
(445, 21, 'Birnin Kebbi', 0),
(446, 21, 'Bunza', 0),
(447, 21, 'Dandi', 0),
(448, 21, 'Fakai', 0),
(449, 21, 'Gwandu', 0),
(450, 21, 'Jega', 0),
(451, 21, 'Kalgo', 0),
(452, 21, 'Koko/Besse', 0),
(453, 21, 'Maiyama', 0),
(454, 21, 'Ngaski', 0),
(455, 21, 'Sakaba', 0),
(456, 21, 'Shanga', 0),
(457, 21, 'Suru', 0),
(458, 21, 'Wasagu/Danko', 0),
(459, 21, 'Yauri', 0),
(460, 21, 'Zuru', 0),
(461, 22, 'Adavi', 0),
(462, 22, 'Ajaokuta', 0),
(463, 22, 'Ankpa', 0),
(464, 22, 'Bassa', 0),
(465, 22, 'Dekina', 0),
(466, 22, 'Ibaji', 0),
(467, 22, 'Idah', 0),
(468, 22, 'Igalamela-Odolu', 0),
(469, 22, 'Ijumu', 0),
(470, 22, 'Kabba/Bunu', 0),
(471, 22, 'Kogi', 0),
(472, 22, 'Lokoja', 0),
(473, 22, 'Mopa-Muro', 0),
(474, 22, 'Ofu', 0),
(475, 22, 'Ogori/Magongo', 0),
(476, 22, 'Okehi', 0),
(477, 22, 'Okene', 0),
(478, 22, 'Olamaboro', 0),
(479, 22, 'Omala', 0),
(480, 22, 'Yagba East', 0),
(481, 22, 'Yagba West', 0),
(482, 23, 'Asa', 0),
(483, 23, 'Baruten', 0),
(484, 23, 'Edu', 0),
(485, 23, 'Ekiti', 0),
(486, 23, 'Ifelodun', 0),
(487, 23, 'Ilorin East', 0),
(488, 23, 'Ilorin South', 0),
(489, 23, 'Ilorin West', 0),
(490, 23, 'Irepodun', 0),
(491, 23, 'Isin', 0),
(492, 23, 'Kaiama', 0),
(493, 23, 'Moro', 0),
(494, 23, 'Offa', 0),
(495, 23, 'Oke Ero', 0),
(496, 23, 'Oyun', 0),
(497, 23, 'Pategi', 0),
(498, 24, 'Agege', 0),
(499, 24, 'Ajeromi-Ifelodun', 0),
(500, 24, 'Alimosho', 0),
(501, 24, 'Amuwo-Odofin', 0),
(502, 24, 'Apapa', 0),
(503, 24, 'Badagry', 0),
(504, 24, 'Epe', 0),
(505, 24, 'Eti Osa', 0),
(506, 24, 'Ibeju-Lekki', 0),
(507, 24, 'Ifako-Ijaiye', 0),
(508, 24, 'Ikeja', 0),
(509, 24, 'Ikorodu', 0),
(510, 24, 'Kosofe', 0),
(511, 24, 'Lagos Island', 0),
(512, 24, 'Lagos Mainland', 0),
(513, 24, 'Mushin', 0),
(514, 24, 'Ojo', 0),
(515, 24, 'Oshodi-Isolo', 0),
(516, 24, 'Shomolu', 0),
(517, 24, 'Surulere', 0),
(518, 25, 'Akwanga', 0),
(519, 25, 'Awe', 0),
(520, 25, 'Doma', 0),
(521, 25, 'Karu', 0),
(522, 25, 'Keana', 0),
(523, 25, 'Keffi', 0),
(524, 25, 'Kokona', 0),
(525, 25, 'Lafia', 0),
(526, 25, 'Nasarawa', 0),
(527, 25, 'Nasarawa Egon', 0),
(528, 25, 'Obi', 0),
(529, 25, 'Toto', 0),
(530, 25, 'Wamba', 0),
(531, 26, 'Agaie', 0),
(532, 26, 'Agwara', 0),
(533, 26, 'Bida', 0),
(534, 26, 'Borgu', 0),
(535, 26, 'Bosso', 0),
(536, 26, 'Chanchaga', 0),
(537, 26, 'Edati', 0),
(538, 26, 'Gbako', 0),
(539, 26, 'Gurara', 0),
(540, 26, 'Katcha', 0),
(541, 26, 'Kontagora', 0),
(542, 26, 'Lapai', 0),
(543, 26, 'Lavun', 0),
(544, 26, 'Magama', 0),
(545, 26, 'Mariga', 0),
(546, 26, 'Mashegu', 0),
(547, 26, 'Mokwa', 0),
(548, 26, 'Muya', 0),
(549, 26, 'Paikoro', 0),
(550, 26, 'Rafi', 0),
(551, 26, 'Rijau', 0),
(552, 26, 'Shiroro', 0),
(553, 26, 'Suleja', 0),
(554, 26, 'Tafa', 0),
(555, 26, 'Wushishi', 0),
(556, 27, 'Abeokuta North', 0),
(557, 27, 'Abeokuta South', 0),
(558, 27, 'Ado-Odo/Ota', 0),
(559, 27, 'Ewekoro', 0),
(560, 27, 'Ifo', 0),
(561, 27, 'Ijebu East', 0),
(562, 27, 'Ijebu North', 0),
(563, 27, 'Ijebu North East', 0),
(564, 27, 'Ijebu Ode', 0),
(565, 27, 'Ikenne', 0),
(566, 27, 'Imeko Afon', 0),
(567, 27, 'Ipokia', 0),
(568, 27, 'Obafemi Owode', 0),
(569, 27, 'Odeda', 0),
(570, 27, 'Odogbolu', 0),
(571, 27, 'Ogun Waterside', 0),
(572, 27, 'Remo North', 0),
(573, 27, 'Shagamu', 0),
(574, 27, 'Yewa North', 0),
(575, 27, 'Yewa South', 0),
(576, 28, 'Akoko North-East', 0),
(577, 28, 'Akoko North-West', 0),
(578, 28, 'Akoko South-East', 0),
(579, 28, 'Akoko South-West', 0),
(580, 28, 'Akure North', 0),
(581, 28, 'Akure South', 0),
(582, 28, 'Ese Odo', 0),
(583, 28, 'Idanre', 0),
(584, 28, 'Ifedore', 0),
(585, 28, 'Ilaje', 0),
(586, 28, 'Ile Oluji/Okeigbo', 0),
(587, 28, 'Irele', 0),
(588, 28, 'Odigbo', 0),
(589, 28, 'Okitipupa', 0),
(590, 28, 'Ondo East', 0),
(591, 28, 'Ondo West', 0),
(592, 28, 'Ose', 0),
(593, 28, 'Owo', 0),
(594, 29, 'Aiyedaade', 0),
(595, 29, 'Aiyedire', 0),
(596, 29, 'Atakumosa East', 0),
(597, 29, 'Atakumosa West', 0),
(598, 29, 'Boluwaduro', 0),
(599, 29, 'Boripe', 0),
(600, 29, 'Ede North', 0),
(601, 29, 'Ede South', 0),
(602, 29, 'Egbedore', 0),
(603, 29, 'Ejigbo', 0),
(604, 29, 'Ife Central', 0),
(605, 29, 'Ife East', 0),
(606, 29, 'Ife North', 0),
(607, 29, 'Ife South', 0),
(608, 29, 'Ifedayo', 0),
(609, 29, 'Ifelodun', 0),
(610, 29, 'Ila', 0),
(611, 29, 'Ilesa East', 0),
(612, 29, 'Ilesa West', 0),
(613, 29, 'Irepodun', 0),
(614, 29, 'Irewole', 0),
(615, 29, 'Isokan', 0),
(616, 29, 'Iwo', 0),
(617, 29, 'Obokun', 0),
(618, 29, 'Odo Otin', 0),
(619, 29, 'Ola Oluwa', 0),
(620, 29, 'Olorunda', 0),
(621, 29, 'Oriade', 0),
(622, 29, 'Orolu', 0),
(623, 29, 'Osogbo', 0),
(624, 30, 'Afijio', 0),
(625, 30, 'Akinyele', 0),
(626, 30, 'Atiba', 0),
(627, 30, 'Atigbo', 0),
(628, 30, 'Egbeda', 0),
(629, 30, 'Ibadan North', 0),
(630, 30, 'Ibadan North-East', 0),
(631, 30, 'Ibadan North-West', 0),
(632, 30, 'Ibadan South-East', 0),
(633, 30, 'Ibadan South-West', 0),
(634, 30, 'Ibarapa Central', 0),
(635, 30, 'Ibarapa East', 0),
(636, 30, 'Ibarapa North', 0),
(637, 30, 'Ido', 0),
(638, 30, 'Iseyin', 0),
(639, 30, 'Isiala', 0),
(640, 30, 'Itesiwaju', 0),
(641, 30, 'Kajola', 0),
(642, 30, 'Lagelu', 0),
(643, 30, 'Ogbomosho North', 0),
(644, 30, 'Ogbomosho South', 0),
(645, 30, 'Oyo East', 0),
(646, 30, 'Oyo West', 0),
(647, 30, 'Saki East', 0),
(648, 30, 'Saki West', 0),
(649, 30, 'Surulere', 0),
(650, 31, 'Barkin Ladi', 0),
(651, 31, 'Bassa', 0),
(652, 31, 'Batagum', 0),
(653, 31, 'Bokkos', 0),
(654, 31, 'Jos East', 0),
(655, 31, 'Jos North', 0),
(656, 31, 'Jos South', 0),
(657, 31, 'Kanam', 0),
(658, 31, 'Kanke', 0),
(659, 31, 'Mangu', 0),
(660, 31, 'Mikang', 0),
(661, 31, 'Pankshin', 0),
(662, 31, 'Qua’an Pan', 0),
(663, 31, 'Riyom', 0),
(664, 31, 'Shendam', 0),
(665, 31, 'Wase', 0),
(666, 32, 'Abua/Odual', 0),
(667, 32, 'Ahoada East', 0),
(668, 32, 'Ahoada West', 0),
(669, 32, 'Akuku-Toru', 0),
(670, 32, 'Andoni', 0),
(671, 32, 'Asari-Toru', 0),
(672, 32, 'Bonny', 0),
(673, 32, 'Degema', 0),
(674, 32, 'Emohua', 0),
(675, 32, 'Ikwerre', 0),
(676, 32, 'Khana', 0),
(677, 32, 'Obio/Akpor', 0),
(678, 32, 'Ogba/Egbema/Ndoni', 0),
(679, 32, 'Ogu/Bolo', 0),
(680, 32, 'Opobo/Nkoro', 0),
(681, 32, 'Okrika', 0),
(682, 32, 'Omuma', 0),
(683, 32, 'Port Harcourt', 0),
(684, 32, 'Tai', 0),
(685, 33, 'Binji', 0),
(686, 33, 'Bodinga', 0),
(687, 33, 'Dange Shuni', 0),
(688, 33, 'Gada', 0),
(689, 33, 'Goronyo', 0),
(690, 33, 'Gudu', 0),
(691, 33, 'Illela', 0),
(692, 33, 'Kebbe', 0),
(693, 33, 'Kware', 0),
(694, 33, 'Rabah', 0),
(695, 33, 'Shagari', 0),
(696, 33, 'Silame', 0),
(697, 33, 'Sokoto North', 0),
(698, 33, 'Sokoto South', 0),
(699, 33, 'Tambuwal', 0),
(700, 33, 'Tangaza', 0),
(701, 33, 'Tureta', 0),
(702, 33, 'Wamako', 0),
(703, 33, 'Wurno', 0),
(704, 33, 'Yabo', 0),
(705, 34, 'Ardo Kola', 0),
(706, 34, 'Donga', 0),
(707, 34, 'Gashaka', 0),
(708, 34, 'Ibi', 0),
(709, 34, 'Jalingo', 0),
(710, 34, 'Karim Lamido', 0),
(711, 34, 'Karin', 0),
(712, 34, 'Kurmi', 0),
(713, 34, 'Lau', 0),
(714, 34, 'Sardauna', 0),
(715, 34, 'Takum', 0),
(716, 34, 'Ussa', 0),
(717, 34, 'Wukari', 0),
(718, 34, 'Yorro', 0),
(719, 34, 'Zing', 0),
(720, 35, 'Bade', 0),
(721, 35, 'Bursari', 0),
(722, 35, 'Damaturu', 0),
(723, 35, 'Fika', 0),
(724, 35, 'Geidam', 0),
(725, 35, 'Gujba', 0),
(726, 35, 'Gulani', 0),
(727, 35, 'Jakusko', 0),
(728, 35, 'Karasuwa', 0),
(729, 35, 'Nangere', 0),
(730, 35, 'Nguru', 0),
(731, 35, 'Potiskum', 0),
(732, 35, 'Tarmuwa', 0),
(733, 35, 'Yunusari', 0),
(734, 35, 'Yobe South', 0),
(735, 36, 'Anka', 0),
(736, 36, 'Bakura', 0),
(737, 36, 'Birnin Magaji', 0),
(738, 36, 'Bukkuyum', 0),
(739, 36, 'Chafe', 0),
(740, 36, 'Gummi', 0),
(741, 36, 'Gusau', 0),
(742, 36, 'Kara', 0),
(743, 36, 'Maradun', 0),
(744, 36, 'Maru', 0),
(745, 36, 'Shinkafi', 0),
(746, 36, 'Talata Mafara', 0),
(747, 36, 'Wannan', 0),
(748, 36, 'Zamfara North', 0),
(749, 36, 'Zamfara South', 0),
(750, NULL, 'Abaji', 0),
(751, NULL, 'Bwari', 0),
(752, NULL, 'Gwagwalada', 0),
(753, NULL, 'Kuje', 0),
(754, NULL, 'Municipal Area Council', 0),
(755, NULL, 'Nyanya', 0),
(756, NULL, 'Suleja', 0);

-- --------------------------------------------------------

--
-- Table structure for table `patient_drugs`
--

CREATE TABLE `patient_drugs` (
  `id` int(11) NOT NULL,
  `priority` text NOT NULL,
  `notes` text NOT NULL,
  `delivery_option` text NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `collection_date` datetime DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patient_drugs`
--

INSERT INTO `patient_drugs` (`id`, `priority`, `notes`, `delivery_option`, `appointment_id`, `payment_id`, `user_id`, `status`, `collection_date`, `created_date`) VALUES
(6, 'normal', '', 'pickup', 1, 10, 1, 2, '2026-01-28 01:46:37', '2026-01-24 14:44:32'),
(7, 'normal', '', 'pickup', 2, 13, 1, 1, NULL, '2026-01-25 23:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `patient_test`
--

CREATE TABLE `patient_test` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `priority` text NOT NULL,
  `specimen_collection` text NOT NULL,
  `preferred_date` text NOT NULL,
  `preferred_time` text NOT NULL,
  `status` int(11) NOT NULL,
  `date_request` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patient_test`
--

INSERT INTO `patient_test` (`id`, `appointment_id`, `user_id`, `payment_id`, `priority`, `specimen_collection`, `preferred_date`, `preferred_time`, `status`, `date_request`) VALUES
(4, 1, 67, 11, 'routine', '{\"blood\":1,\"urine\":0,\"fasting\":0}', '2026-01-24', 'asap', 0, '2026-01-30 15:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `patient_vitals`
--

CREATE TABLE `patient_vitals` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `vital_id` int(11) NOT NULL,
  `vital_value` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patient_vitals`
--

INSERT INTO `patient_vitals` (`id`, `appointment_id`, `vital_id`, `vital_value`, `status`) VALUES
(2, 1, 1, '20', 0),
(3, 2, 3, '24', 0),
(4, 2, 2, '76', 0),
(5, 2, 1, '30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `accountant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `discount` int(11) NOT NULL,
  `net_amount` float NOT NULL,
  `payment-method` text NOT NULL,
  `record_date` datetime NOT NULL,
  `payment_date` datetime DEFAULT NULL,
  `purpose` int(11) NOT NULL,
  `note` text NOT NULL,
  `reciept_num` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `patient_id`, `appointment_id`, `accountant_id`, `user_id`, `amount`, `discount`, `net_amount`, `payment-method`, `record_date`, `payment_date`, `purpose`, `note`, `reciept_num`, `status`) VALUES
(1, 71, 0, 0, 1, 2500, 10, 2250, '', '2026-01-18 14:46:56', NULL, 1, 'Patient registration payment', 'RCT-20260118-145542', 0),
(10, 67, 1, 1, 0, 600, 10, 540, 'Cash', '2026-01-24 14:44:31', '2026-01-27 19:22:54', 2, 'Drug Purchase Payment', 'RCT-20260124-340982', 1),
(11, 67, 1, 0, 0, 3000, 10, 2700, 'Cash', '2026-01-24 16:52:23', '2026-01-25 21:26:36', 3, 'Lab Test Payment', 'RCT-20260124-704228', 1),
(12, 72, 0, 1, 1, 2500, 5, 2375, 'Cash', '2026-01-25 22:43:56', '2026-01-25 23:03:04', 1, 'Patient registration payment', 'RCT-20260125-291782', 1),
(13, 72, 2, 1, 1, 900, 5, 855, 'Card', '2026-01-25 23:09:03', '2026-01-25 23:13:37', 2, 'Drug Purchase Payment', 'RCT-20260125-915868', 1),
(14, 75, 0, 0, 1, 6000, 10, 5400, '', '2026-02-05 15:03:25', NULL, 1, 'Patient registration payment', 'RCT-20260205-358303', 0),
(15, 76, 0, 0, 1, 6000, 10, 5400, '', '2026-02-17 11:49:30', NULL, 1, 'Patient registration payment', 'RCT-20260217-458724', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_activities`
--

CREATE TABLE `pharmacy_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pharmacy_activities`
--

INSERT INTO `pharmacy_activities` (`id`, `user_id`, `notes`, `created_at`) VALUES
(1, 1, 'Muhammad ibrahim added 10 more units of Paracetamol to pharmacy stock', '2026-01-20 14:44:40'),
(2, 1, 'Muhammad ibrahim added 50 units of Cough Syrub to pharmacy stock', '2026-01-25 23:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_stock`
--

CREATE TABLE `pharmacy_stock` (
  `id` int(11) NOT NULL,
  `drug` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pharmacy_stock`
--

INSERT INTO `pharmacy_stock` (`id`, `drug`, `quantity`) VALUES
(1, 1, 15),
(2, 2, 50);

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `patient_test_id` int(11) NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `parameter_id`, `patient_test_id`, `answer`) VALUES
(1, 1, 58, '0.2'),
(2, 3, 58, '5.2');

-- --------------------------------------------------------

--
-- Table structure for table `result_parameters`
--

CREATE TABLE `result_parameters` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `unit` text NOT NULL,
  `para_range` text NOT NULL,
  `normal_range` text NOT NULL DEFAULT '',
  `test_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `result_parameters`
--

INSERT INTO `result_parameters` (`id`, `name`, `unit`, `para_range`, `normal_range`, `test_id`, `status`) VALUES
(1, 'status 1', 'ml', '5-19', '', 1, 1),
(3, 'Sodium', 'mm', '4-8', '', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` text NOT NULL,
  `room_type` int(11) NOT NULL,
  `bed_space` int(11) NOT NULL,
  `room_price` float NOT NULL,
  `ward` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `room_type`, `bed_space`, `room_price`, `ward`, `status`, `user_id`) VALUES
(1, 'ROOM 101', 0, 0, 0, 0, 1, 1),
(2, 'ROOM 102', 1, 2, 200, 0, 1, 1),
(3, 'Room 103', 1, 2, 2000, 1, 1, 1),
(4, 'Room 104', 0, 0, 0, 0, 1, 1),
(5, 'Room 105', 0, 0, 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `schemes`
--

CREATE TABLE `schemes` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `discount_fee` float NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `schemes`
--

INSERT INTO `schemes` (`id`, `name`, `discount_fee`, `status`) VALUES
(1, 'NHIS', 10, 1),
(2, 'corporate', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `specimen`
--

CREATE TABLE `specimen` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `specimen`
--

INSERT INTO `specimen` (`id`, `name`, `status`) VALUES
(1, 'Blood 2', -1),
(2, 'Blood', 1),
(3, 'Urine', 1);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`) VALUES
(1, 'Abia'),
(2, 'Adamawa'),
(3, 'Akwa Ibom'),
(4, 'Anambra'),
(5, 'Bauchi'),
(6, 'Bayelsa'),
(7, 'Benue'),
(8, 'Borno'),
(9, 'Cross River'),
(10, 'Delta'),
(11, 'Ebonyi'),
(12, 'Edo'),
(13, 'Ekiti'),
(14, 'Enugu'),
(15, 'Gombe'),
(16, 'Imo'),
(17, 'Jigawa'),
(18, 'Kaduna'),
(19, 'Kano'),
(20, 'Katsina'),
(21, 'Kebbi'),
(22, 'Kogi'),
(23, 'Kwara'),
(24, 'Lagos'),
(25, 'Nasarawa'),
(26, 'Niger'),
(27, 'Ogun'),
(28, 'Ondo'),
(29, 'Osun'),
(30, 'Oyo'),
(31, 'Plateau'),
(32, 'Rivers'),
(33, 'Sokoto'),
(34, 'Taraba'),
(35, 'Yobe'),
(36, 'Zamfara'),
(37, 'FCT');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `amount` float NOT NULL,
  `specimen` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `name`, `amount`, `specimen`, `type`, `status`) VALUES
(1, 'HIV', 3000, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `test_lists`
--

CREATE TABLE `test_lists` (
  `id` int(11) NOT NULL,
  `patient_test_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `result_releaser_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `asker_id` int(11) NOT NULL,
  `sample_collector` int(11) NOT NULL DEFAULT 0,
  `acknowleger_id` int(11) NOT NULL,
  `date_request` date DEFAULT NULL,
  `sample_collection_date` text NOT NULL DEFAULT '',
  `date_acknowledge` date DEFAULT NULL,
  `date_of_result` date DEFAULT NULL,
  `notes` text NOT NULL DEFAULT '',
  `invoice_id` int(11) NOT NULL,
  `labno` text NOT NULL,
  `reason` text NOT NULL DEFAULT '',
  `paid` int(11) NOT NULL DEFAULT 0,
  `amount` float NOT NULL DEFAULT 0,
  `payment_confirmer` int(11) NOT NULL DEFAULT 0,
  `payment_date` datetime DEFAULT NULL,
  `verifier` int(11) NOT NULL DEFAULT 0,
  `verified_date` datetime DEFAULT NULL,
  `collector` text NOT NULL DEFAULT '',
  `collected_date` text NOT NULL DEFAULT '',
  `hos_no` text NOT NULL DEFAULT '',
  `dr_name` text NOT NULL DEFAULT '',
  `clinical` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `test_lists`
--

INSERT INTO `test_lists` (`id`, `patient_test_id`, `test_id`, `result_releaser_id`, `status`, `asker_id`, `sample_collector`, `acknowleger_id`, `date_request`, `sample_collection_date`, `date_acknowledge`, `date_of_result`, `notes`, `invoice_id`, `labno`, `reason`, `paid`, `amount`, `payment_confirmer`, `payment_date`, `verifier`, `verified_date`, `collector`, `collected_date`, `hos_no`, `dr_name`, `clinical`) VALUES
(58, 4, 1, 0, 7, 0, 1, 1, '2026-01-24', '2026-01-30 19:02:39', '2026-01-30', '2026-01-30', 'good resyult', 0, '', '', 1, 0, 0, NULL, 0, '2026-01-30 00:00:00', 'oga', '2026-01-31', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `userno` text NOT NULL,
  `gender` text NOT NULL,
  `category` text NOT NULL,
  `dob` text NOT NULL DEFAULT '',
  `marital_status` text NOT NULL DEFAULT '',
  `password` text NOT NULL,
  `department_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `pic` text NOT NULL DEFAULT '',
  `age` text NOT NULL DEFAULT '',
  `kin` text NOT NULL DEFAULT '',
  `state` int(11) NOT NULL,
  `lga` int(11) NOT NULL,
  `tribe` text NOT NULL,
  `address` text NOT NULL DEFAULT '',
  `hospital_num` int(11) NOT NULL,
  `blood_group` text NOT NULL DEFAULT '',
  `genotype` text NOT NULL DEFAULT '',
  `allergies` text NOT NULL DEFAULT '',
  `chronic_condition` text NOT NULL,
  `kin_phone` text NOT NULL,
  `kin_relationship` text NOT NULL,
  `file_type` int(11) NOT NULL DEFAULT 0,
  `scheme_type` int(11) NOT NULL DEFAULT 0,
  `family_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `userno`, `gender`, `category`, `dob`, `marital_status`, `password`, `department_id`, `user_id`, `type`, `status`, `pic`, `age`, `kin`, `state`, `lga`, `tribe`, `address`, `hospital_num`, `blood_group`, `genotype`, `allergies`, `chronic_condition`, `kin_phone`, `kin_relationship`, `file_type`, `scheme_type`, `family_id`) VALUES
(1, 'Muhammad ibrahim', 'admin@gmail.com', '08037244467', '', '1', 'Admin', '', '-1', '$2y$10$SiB4Qx5nY.1wgIW8uW/Lt.OSjaPljLgJePbeMonXoQgVE63rCiRim', 0, 0, 0, 1, 'E2heLY2900.jpeg', '', '', 0, 0, '', '', 0, '', '', '', '', '', '', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `vitals`
--

CREATE TABLE `vitals` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `unit` text NOT NULL DEFAULT '',
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vitals`
--

INSERT INTO `vitals` (`id`, `name`, `unit`, `status`) VALUES
(1, 'Temperature', '&deg;F', 1),
(2, 'Weight', 'kg', 1),
(3, 'BP', 'C', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wards`
--

CREATE TABLE `wards` (
  `id` int(11) NOT NULL,
  `ward_name` text NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `wards`
--

INSERT INTO `wards` (`id`, `ward_name`, `status`) VALUES
(1, 'ICU ward', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assign_doctors`
--
ALTER TABLE `assign_doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drug_list`
--
ALTER TABLE `drug_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_types`
--
ALTER TABLE `file_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospital_details`
--
ALTER TABLE `hospital_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lgas`
--
ALTER TABLE `lgas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `patient_drugs`
--
ALTER TABLE `patient_drugs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_test`
--
ALTER TABLE `patient_test`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_vitals`
--
ALTER TABLE `patient_vitals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy_activities`
--
ALTER TABLE `pharmacy_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pharmacy_stock`
--
ALTER TABLE `pharmacy_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `result_parameters`
--
ALTER TABLE `result_parameters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schemes`
--
ALTER TABLE `schemes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `specimen`
--
ALTER TABLE `specimen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_lists`
--
ALTER TABLE `test_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vitals`
--
ALTER TABLE `vitals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wards`
--
ALTER TABLE `wards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assign_doctors`
--
ALTER TABLE `assign_doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `drug_list`
--
ALTER TABLE `drug_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `file_types`
--
ALTER TABLE `file_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hospital_details`
--
ALTER TABLE `hospital_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lgas`
--
ALTER TABLE `lgas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=757;

--
-- AUTO_INCREMENT for table `patient_drugs`
--
ALTER TABLE `patient_drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `patient_test`
--
ALTER TABLE `patient_test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patient_vitals`
--
ALTER TABLE `patient_vitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pharmacy_activities`
--
ALTER TABLE `pharmacy_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pharmacy_stock`
--
ALTER TABLE `pharmacy_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `result_parameters`
--
ALTER TABLE `result_parameters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schemes`
--
ALTER TABLE `schemes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `specimen`
--
ALTER TABLE `specimen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_lists`
--
ALTER TABLE `test_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `vitals`
--
ALTER TABLE `vitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wards`
--
ALTER TABLE `wards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lgas`
--
ALTER TABLE `lgas`
  ADD CONSTRAINT `lgas_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;
COMMIT;

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `bed_number` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `admitted_by` int(11) NOT NULL,
  `admission_date` datetime NOT NULL DEFAULT current_timestamp(),
  `discharge_date` datetime DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0=active, 1=discharged, -1=cancelled',
  `notes` text NOT NULL DEFAULT '',
  `last_billed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_status` (`status`),
  KEY `idx_room` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admission_reports`
--

CREATE TABLE `admission_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admission_id` int(11) NOT NULL,
  `nurse_id` int(11) NOT NULL,
  `report` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admission` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admission_billing`
--

CREATE TABLE `admission_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admission_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `amount` float NOT NULL,
  `billing_type` int(11) NOT NULL COMMENT '1=room_stay, 2=drug, 3=other',
  `reference_id` int(11) NOT NULL DEFAULT 0,
  `paid` int(11) NOT NULL DEFAULT 0 COMMENT '0=unpaid, 1=paid',
  `payment_id` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admission_billing` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `scans`
-- --------------------------------------------------------

CREATE TABLE `scans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `modality` enum('X-Ray','CT','MRI','Ultrasound','Mammography','Fluoroscopy','Nuclear','PET') NOT NULL DEFAULT 'X-Ray',
  `body_part` text NOT NULL DEFAULT '',
  `amount` float NOT NULL DEFAULT 0,
  `description` text NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `patient_scan`
-- --------------------------------------------------------

CREATE TABLE `patient_scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL DEFAULT 0,
  `patient_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL DEFAULT 0,
  `priority` text NOT NULL DEFAULT 'routine',
  `clinical_info` text NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT 0,
  `is_walkin` int(11) NOT NULL DEFAULT 0,
  `walkin_name` text NOT NULL DEFAULT '',
  `walkin_phone` text NOT NULL DEFAULT '',
  `date_request` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_patient_scan_appointment` (`appointment_id`),
  KEY `idx_patient_scan_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `scan_lists`
-- --------------------------------------------------------

CREATE TABLE `scan_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_scan_id` int(11) NOT NULL,
  `scan_id` int(11) NOT NULL,
  `asker_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `radiologist_id` int(11) NOT NULL DEFAULT 0,
  `verifier_id` int(11) NOT NULL DEFAULT 0,
  `date_request` datetime NOT NULL DEFAULT current_timestamp(),
  `date_performed` datetime DEFAULT NULL,
  `date_reported` datetime DEFAULT NULL,
  `date_verified` datetime DEFAULT NULL,
  `date_released` datetime DEFAULT NULL,
  `paid` int(11) NOT NULL DEFAULT 0,
  `amount` float NOT NULL DEFAULT 0,
  `notes` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_scan_lists_patient_scan` (`patient_scan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `scan_results`
-- --------------------------------------------------------

CREATE TABLE `scan_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scan_list_id` int(11) NOT NULL,
  `clinical_info` text NOT NULL DEFAULT '',
  `findings` text NOT NULL DEFAULT '',
  `impression` text NOT NULL DEFAULT '',
  `recommendation` text NOT NULL DEFAULT '',
  `radiologist_id` int(11) NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_scan_results_list` (`scan_list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
