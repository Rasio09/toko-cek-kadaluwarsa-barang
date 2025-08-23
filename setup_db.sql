CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL
);

INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin');

ALTER TABLE barang
    ADD COLUMN harga_beli DECIMAL(15,2) DEFAULT 0,
    ADD COLUMN harga_jual DECIMAL(15,2) DEFAULT 0;

ALTER TABLE barang_record
    ADD COLUMN harga_beli DECIMAL(15,2) DEFAULT 0,
    ADD COLUMN harga_jual DECIMAL(15,2) DEFAULT 0;
