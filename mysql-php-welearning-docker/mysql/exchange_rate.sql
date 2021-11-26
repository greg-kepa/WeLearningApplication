drop table if exists `exchange_rate`;
create table `exchange_rate` (
    ert_id int not null auto_increment,
    ert_name varchar(255) not null,
    ert_code char(3) not null unique,
    ert_value float(11,6),
    primary key (ert_id)
) CHARACTER SET utf8 COLLATE utf8_polish_ci;
insert into `exchange_rate` (ert_name, ert_code, ert_value) values
    ('Lek', 'ALL', 0.1415),
    ('Taka','BDT', 1.1972),
    ('Dolar burnejski','BDN',3.123456);