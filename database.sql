CREATE DATABASE IF NOT EXISTS vaal_solutions;
USE vaal_solutions;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'editor',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(60),
    title VARCHAR(150) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255),
    title VARCHAR(150) NOT NULL,
    sort_order INT DEFAULT 0
);

CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote TEXT NOT NULL,
    author VARCHAR(100),
    sort_order INT DEFAULT 0
);

CREATE TABLE stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0
);

CREATE TABLE pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price VARCHAR(50),
    features TEXT,
    featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0
);

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
);

-- Default admin: admin@vaaltech.co.za / admin123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@vaaltech.co.za', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB4g0Vqk5UZ1jW8u', 'admin');

INSERT INTO services (icon, title, description, sort_order) VALUES
('fa-solid fa-code', 'Software Development', 'Web applications, mobile apps, desktop systems and APIs.', 1),
('fa-solid fa-network-wired', 'Networking Solutions', 'WiFi setup, LAN/WAN configuration, internet solutions and servers.', 2),
('fa-solid fa-house-signal', 'Smart Home Automation', 'CCTV, IoT devices, smart lighting and automation systems.', 3),
('fa-solid fa-shield-halved', 'Cybersecurity', 'Security monitoring, firewalls, penetration testing and protection.', 4);

INSERT INTO projects (image, title, sort_order) VALUES
('images/software.jpg', 'Software Development', 1),
('images/network.jpg', 'Networking Solutions', 2),
('images/smart-home.jpg', 'Smart Home Systems', 3);

INSERT INTO testimonials (quote, author, sort_order) VALUES
('Excellent software and networking solutions. Highly professional team.', 'Business Client', 1),
('The smart home installation changed everything for our office.', 'Smart Home Client', 2),
('Reliable support and cybersecurity services.', 'Corporate Client', 3);

INSERT INTO stats (target, label, sort_order) VALUES
(150, 'Projects Completed', 1),
(98, 'Client Satisfaction %', 2),
(24, 'Hours Support', 3),
(10, 'Technology Services', 4);

INSERT INTO pricing (name, price, features, featured, sort_order) VALUES
('Starter', 'R999', 'Basic Website\nHosting Setup\nEmail Support', 0, 1),
('Professional', 'R4999', 'Business Website\nNetworking Setup\nPriority Support', 1, 2),
('Enterprise', 'Custom', 'Cloud Infrastructure\nCybersecurity\nSmart Solutions', 0, 3);