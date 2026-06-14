TYPE=VIEW
query=select `class_63`.`product`.`p_id` AS `p_id`,`class_63`.`product`.`p_name` AS `p_name`,`class_63`.`product`.`p_price` AS `p_price`,`class_63`.`manufacturer`.`m_name` AS `m_name`,`class_63`.`manufacturer`.`m_contact_no` AS `m_contact_no` from `class_63`.`manufacturer` join `class_63`.`product` where `class_63`.`manufacturer`.`m_id` = `class_63`.`product`.`manufacturer_id` and `class_63`.`product`.`p_price` > 5000
md5=e42cc447bcefc30edda0ef9b1bf24995
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001777092119263050
create-version=2
source=SELECT \n    product.p_id, \n    product.p_name, \n    product.p_price, \n    manufacturer.m_name, \n    manufacturer.m_contact_no \nFROM manufacturer, product \nWHERE manufacturer.m_id = product.manufacturer_id \nAND product.p_price > 5000
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `class_63`.`product`.`p_id` AS `p_id`,`class_63`.`product`.`p_name` AS `p_name`,`class_63`.`product`.`p_price` AS `p_price`,`class_63`.`manufacturer`.`m_name` AS `m_name`,`class_63`.`manufacturer`.`m_contact_no` AS `m_contact_no` from `class_63`.`manufacturer` join `class_63`.`product` where `class_63`.`manufacturer`.`m_id` = `class_63`.`product`.`manufacturer_id` and `class_63`.`product`.`p_price` > 5000
mariadb-version=100432
