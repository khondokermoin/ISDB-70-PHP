TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER after_manufacturer_delete\nAFTER DELETE ON Manufacturer\nFOR EACH ROW\nBEGIN\n    DELETE FROM Product WHERE manufacturer_id = OLD.id;\nEND'
sql_modes=524288
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='utf8mb4_general_ci'
created=1776661136852585
