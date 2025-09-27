-- Comprehensive Database Schema for Petroleum Gas CMS
-- This schema supports all manageable elements from index.html

-- Site Settings Table
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_title VARCHAR(255) NOT NULL DEFAULT 'Petroleum and Gas',
    tagline VARCHAR(255),
    favicon_url VARCHAR(500),
    logo_main VARCHAR(500),
    logo_transparent VARCHAR(500),
    logo_width INT DEFAULT 150,
    logo_height INT DEFAULT 50,
    logo_alt_text VARCHAR(255),
    logo_link VARCHAR(500) DEFAULT '#home',
    logo_link_target VARCHAR(20) DEFAULT '_self',
    primary_color VARCHAR(7) DEFAULT '#FFD200',
    secondary_color VARCHAR(7) DEFAULT '#484939',
    tertiary_color VARCHAR(7) DEFAULT '#1E3A8A',
    navbar_bg_color VARCHAR(7),
    footer_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero Slides Table
CREATE TABLE hero_slides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    image_url VARCHAR(500) NOT NULL,
    title_color ENUM('primary', 'secondary', 'tertiary', 'white', 'black', 'custom') DEFAULT 'primary',
    title_color_custom VARCHAR(7),
    subtitle_color ENUM('primary', 'secondary', 'tertiary', 'white', 'black', 'custom') DEFAULT 'white',
    subtitle_color_custom VARCHAR(7),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero Slide Buttons Table
CREATE TABLE hero_slide_buttons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slide_id INT NOT NULL,
    button_text VARCHAR(100) NOT NULL,
    button_url VARCHAR(500) NOT NULL,
    button_type ENUM('primary', 'secondary') DEFAULT 'primary',
    sort_order INT DEFAULT 0,
    FOREIGN KEY (slide_id) REFERENCES hero_slides(id) ON DELETE CASCADE
);

-- Navigation Menu Table
CREATE TABLE navigation_menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    has_dropdown BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    color_class VARCHAR(20),
    FOREIGN KEY (parent_id) REFERENCES navigation_menu(id) ON DELETE CASCADE
);

-- Services Table
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_title VARCHAR(255) DEFAULT 'Our Services',
    section_subtitle VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Service Items Table
CREATE TABLE service_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    icon_class VARCHAR(100),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- About Section Table
CREATE TABLE about_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) DEFAULT 'Save The Planet!',
    description TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Features Section Table
CREATE TABLE features_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_title VARCHAR(255) DEFAULT 'We Provide Energy',
    section_subtitle TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Feature Items Table
CREATE TABLE feature_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    features_section_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (features_section_id) REFERENCES features_section(id) ON DELETE CASCADE
);

-- Projects Section Table
CREATE TABLE projects_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_title VARCHAR(255) DEFAULT 'Latest Projects',
    section_subtitle TEXT,
    show_more_button BOOLEAN DEFAULT 1,
    more_button_text VARCHAR(100) DEFAULT 'MORE PROJECT',
    more_button_link VARCHAR(500) DEFAULT '#',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects Table
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    projects_section_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    image_url VARCHAR(500),
    link_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (projects_section_id) REFERENCES projects_section(id) ON DELETE CASCADE
);

-- Quote Section Table
CREATE TABLE quote_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quote_text TEXT NOT NULL,
    author VARCHAR(255),
    background_color VARCHAR(7) DEFAULT '#FFD200',
    text_color VARCHAR(7) DEFAULT '#333333',
    font_size ENUM('small', 'medium', 'large') DEFAULT 'medium',
    is_enabled BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- News Section Table
CREATE TABLE news_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_title VARCHAR(255) DEFAULT 'Our Latest News',
    section_subtitle TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- News Articles Table
CREATE TABLE news_articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    news_section_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    image_url VARCHAR(500),
    link_url VARCHAR(500),
    publish_date DATE,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (news_section_id) REFERENCES news_section(id) ON DELETE CASCADE
);

-- Newsletter Section Table
CREATE TABLE newsletter_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_title VARCHAR(255) DEFAULT 'Get Latest Offers',
    section_subtitle TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Newsletter Subscribers Table
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1
);

-- Footer Table
CREATE TABLE footer_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    logo_url VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Footer Columns Table
CREATE TABLE footer_columns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    footer_id INT NOT NULL,
    column_type ENUM('logo', 'contact', 'newsletter', 'social', 'links', 'custom') NOT NULL,
    title VARCHAR(255),
    description TEXT,
    custom_content TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (footer_id) REFERENCES footer_settings(id) ON DELETE CASCADE
);

-- Footer Contact Information Table
CREATE TABLE footer_contact (
    id INT PRIMARY KEY AUTO_INCREMENT,
    footer_column_id INT NOT NULL,
    contact_type ENUM('phone', 'email', 'address', 'other') NOT NULL,
    contact_value TEXT NOT NULL,
    contact_label VARCHAR(100),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (footer_column_id) REFERENCES footer_columns(id) ON DELETE CASCADE
);

-- Footer Social Links Table
CREATE TABLE footer_social_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    footer_column_id INT NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,
    label VARCHAR(100),
    icon_class VARCHAR(100),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (footer_column_id) REFERENCES footer_columns(id) ON DELETE CASCADE
);

-- Footer Links Table
CREATE TABLE footer_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    footer_column_id INT NOT NULL,
    link_title VARCHAR(100) NOT NULL,
    link_url VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (footer_column_id) REFERENCES footer_columns(id) ON DELETE CASCADE
);

-- Footer Menu Table
CREATE TABLE footer_menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1
);

-- Media Library Table
CREATE TABLE media_library (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    alt_text VARCHAR(255),
    caption TEXT,
    mime_type VARCHAR(100) NOT NULL,
    width INT,
    height INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Social Media Links Table (for header)
CREATE TABLE social_media_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon_class VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- SEO Settings Table
CREATE TABLE seo_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    google_analytics_id VARCHAR(50),
    google_search_console VARCHAR(255),
    facebook_pixel VARCHAR(50),
    custom_head_code TEXT,
    custom_body_code TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO site_settings (site_title, primary_color, secondary_color, tertiary_color)
VALUES ('Petroleum and Gas – Gas and Oil WordPress theme', '#FFD200', '#484939', '#1E3A8A');

INSERT INTO services (section_title, section_subtitle)
VALUES ('Our Services', 'What we offer to our clients');

INSERT INTO about_section (title, description)
VALUES ('Save The Planet!', 'Sed posuere consectetur est at lobortis. Donec ullamcorper nulla non metus auctor fringilla.');

INSERT INTO features_section (section_title, section_subtitle)
VALUES ('We Provide Energy', 'To many clients like government, homes and offices');

INSERT INTO projects_section (section_title, section_subtitle)
VALUES ('Latest Projects', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.');

INSERT INTO quote_section (quote_text, author)
VALUES ('We help hardware startups integrate technology, scale and desirability without compromise', 'CEO, Petroleum & Gas Solutions');

INSERT INTO news_section (section_title, section_subtitle)
VALUES ('Our Latest News', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.');

INSERT INTO newsletter_section (section_title, section_subtitle)
VALUES ('Get Latest Offers', 'Sed odio orci, fringilla nec dolor et, euismod auctor mauris. Curabitur semper dui diam, nec accumsan mauris consequat sed.');

INSERT INTO footer_settings (description)
VALUES ('Petroleum is the leader in the country sed diam nonumy eirmod tempor invidunt ut labore and efficient strategy.');