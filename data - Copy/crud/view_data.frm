TYPE=VIEW
query=select `crud`.`student`.`sid` AS `sid`,`crud`.`student`.`sname` AS `sname`,`crud`.`student`.`saddress` AS `saddress`,`crud`.`student`.`sclass` AS `sclass`,`crud`.`student`.`sphpne` AS `sphpne` from `crud`.`student` where `crud`.`student`.`sclass` = \'CSE\'
md5=f823de30cc2fed2e8334897ac67e27aa
updatable=1
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001776655930434052
create-version=2
source=SELECT sid, sname, saddress, sclass, sphpne \nFROM student \nWHERE sclass = \'CSE\'
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `crud`.`student`.`sid` AS `sid`,`crud`.`student`.`sname` AS `sname`,`crud`.`student`.`saddress` AS `saddress`,`crud`.`student`.`sclass` AS `sclass`,`crud`.`student`.`sphpne` AS `sphpne` from `crud`.`student` where `crud`.`student`.`sclass` = \'CSE\'
mariadb-version=100432
