CREATE TABLE products
(
    id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name varchar(256) NOT NULL,
    price decimal(19,2) NOT NULL,
    producer varchar(256),
    country varchar(256),
    expired_at date
);
CREATE UNIQUE INDEX products_id_uindex ON products (id);
CREATE INDEX products_name_index ON products (name);
