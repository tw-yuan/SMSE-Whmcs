SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `smse_pay_record` (
  `invoice_id` int(255) NOT NULL,
  `payment` text DEFAULT NULL,
  `atm_SmilePayNO` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Fami_SmilePayNO` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ibon_SmilePayNO` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Amount` int(255) NOT NULL,
  `AtmBankNo` text DEFAULT NULL,
  `AtmNo` text DEFAULT NULL,
  `atm_PayEndDate` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IbonNo` text DEFAULT NULL,
  `IbonNo_PayEndDate` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `FamiNO` text DEFAULT NULL,
  `FamiNO_PayEndDate` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;