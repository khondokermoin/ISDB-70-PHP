TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER `after_manufacturer_delete` AFTER DELETE ON `manufacturer` FOR EACH ROW BEGIN\n    DELETE FROM product \n    WHERE manufacturer_id = OLD.m_id; \nEND'
sql_modes=1098907648
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='utf8mb4_general_ci'
created=1777089888495978
