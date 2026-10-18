CREATE DATABASE IF NOT EXISTS jayarathne_construction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jayarathne_construction;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','client','provider') NOT NULL DEFAULT 'client',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NULL,
  project_name VARCHAR(180) NOT NULL,
  description TEXT,
  location VARCHAR(180),
  start_date DATE NULL,
  end_date DATE NULL,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Planning','In Progress','Completed','On Hold') NOT NULL DEFAULT 'Planning',
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE projects
ADD COLUMN image VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS equipment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NULL,
  equipment_name VARCHAR(180) NOT NULL,
  description TEXT,
  daily_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
  availability ENUM('Available','Unavailable') NOT NULL DEFAULT 'Available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE equipment
ADD COLUMN image VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS project_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  client_id INT NOT NULL,
  message TEXT,
  status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rentals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  equipment_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  total_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('Pending','Approved','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;


