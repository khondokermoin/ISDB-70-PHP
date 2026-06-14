TYPE=VIEW
query=select `exam_database`.`product`.`id` AS `p_id`,`exam_database`.`product`.`name` AS `p_name`,`exam_database`.`product`.`price` AS `p_price`,`exam_database`.`manufacturer`.`name` AS `m_name`,`exam_database`.`manufacturer`.`contact_no` AS `m_contact_no` from (`exam_database`.`manufacturer` join `exam_database`.`product` on(`exam_database`.`manufacturer`.`id` = `exam_database`.`product`.`manufacturer_id`)) where `exam_database`.`product`.`price` > 5000
md5=cde1404d16abcf060e82a302d4fd034c
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001777958182861548
create-version=2
source=SELECT \n    `exam_database`.`product`.`id` AS `p_id`,\n    `exam_database`.`product`.`name` AS `p_name`,\n    `exam_database`.`product`.`price` AS `p_price`,\n    `exam_database`.`manufacturer`.`name` AS `m_name`,\n    `exam_database`.`manufacturer`.`contact_no` AS `m_contact_no`\nFROM \n    `exam_database`.`manufacturer`\nJOIN \n    `exam_database`.`product` \n    ON `exam_database`.`manufacturer`.`id` = `exam_database`.`product`.`manufacturer_id`\nWHERE \n    `exam_database`.`product`.`price` > 5000
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `exam_database`.`product`.`id` AS `p_id`,`exam_database`.`product`.`name` AS `p_name`,`exam_database`.`product`.`price` AS `p_price`,`exam_database`.`manufacturer`.`name` AS `m_name`,`exam_database`.`manufacturer`.`contact_no` AS `m_contact_no` from (`exam_database`.`manufacturer` join `exam_database`.`product` on(`exam_database`.`manufacturer`.`id` = `exam_database`.`product`.`manufacturer_id`)) where `exam_database`.`product`.`price` > 5000
mariadb-version=100432
