TYPE=VIEW
query=select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`price` AS `price`,`p`.`stock_qty` AS `stock_qty`,`m`.`name` AS `manufacturer_name`,`m`.`contact` AS `manufacturer_contact`,`p`.`created_at` AS `created_at` from (`company`.`product` `p` join `company`.`manufacturer` `m` on(`p`.`manufacturer_id` = `m`.`id`))
md5=e5b05f6baa62e9841b3efa72549da059
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001776665487534212
create-version=2
source=SELECT\n    p.id,\n    p.name,\n    p.price,\n    p.stock_qty,\n    m.name AS manufacturer_name,\n    m.contact AS manufacturer_contact,\n    p.created_at\nFROM product p\nINNER JOIN manufacturer m ON p.manufacturer_id = m.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`price` AS `price`,`p`.`stock_qty` AS `stock_qty`,`m`.`name` AS `manufacturer_name`,`m`.`contact` AS `manufacturer_contact`,`p`.`created_at` AS `created_at` from (`company`.`product` `p` join `company`.`manufacturer` `m` on(`p`.`manufacturer_id` = `m`.`id`))
mariadb-version=100432
