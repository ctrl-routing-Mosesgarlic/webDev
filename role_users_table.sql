-- Role Users Table for WSM System
-- This table stores the different user roles available in the system

CREATE TABLE IF NOT EXISTS `role_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default roles
INSERT INTO `role_users` (`role_name`, `description`) VALUES
('admin', 'System administrator with full access'),
('warehouse_manager', 'Manages warehouse operations and inventory'),
('stock_clerk', 'Handles stock management and updates'),
('supplier', 'External supplier providing products'),
('customer', 'End user who purchases products');
