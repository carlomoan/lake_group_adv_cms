-- Sample Data for Admin Dashboard Tables
-- This file populates all tables with initial data matching the admin dashboard

-- ==================================================
-- NAVBAR AND DROPDOWN SETTINGS DATA
-- ==================================================

INSERT INTO `navbar_settings` (`position`, `height`, `background_color`, `transparency`, `text_color`, `hover_color`) VALUES
('fixed', 70, '#ffffff', 0, '#333333', '#FFD200');

INSERT INTO `dropdown_settings` (`layout_type`, `background_type`, `background_color`, `gradient_start`, `gradient_end`, `text_color`, `hover_text_color`, `border_radius`, `shadow_intensity`, `animation`, `width`, `font_size`, `line_height`, `item_padding`, `border_style`, `enable_multi_level`, `arrow_style`) VALUES
('standard', 'color', '#ffffff', '#ffffff', '#f8f9fa', '#333333', '#FFD200', 4, 'medium', 'fade', 250, 14, 1.5, 12, 'none', 1, 'chevron');

-- ==================================================
-- HERO SLIDER DATA
-- ==================================================

INSERT INTO `hero_settings` (`autoplay`, `duration`) VALUES
(1, 5);

INSERT INTO `hero_slides` (`slide_order`, `image_url`, `subtitle`, `subtitle_color`, `title`, `description`, `button1_text`, `button1_url`, `button2_text`, `button2_url`, `is_active`) VALUES
(1, '', 'Welcome to Our Company', '#FFD200', 'Leading Energy Solutions Provider', 'Professional petroleum and gas services with industry expertise spanning decades. Comprehensive solutions for all your energy sector needs.', 'Our Services', '#services', 'Contact Us', '#contact', 1),
(2, '', 'Quality & Excellence', '#FFD200', 'Trusted Partnership in Energy', 'From exploration to distribution, we deliver reliable and efficient energy solutions that power industries and communities worldwide.', 'Learn More', '#about', 'Get Quote', '#contact', 1);

-- ==================================================
-- SERVICES DATA
-- ==================================================

INSERT INTO `services_settings` (`section_title`, `section_subtitle`) VALUES
('Our Core Services', 'Comprehensive solutions across energy sectors');

INSERT INTO `services` (`title`, `image_url`, `description`, `url`, `sort_order`, `is_active`) VALUES
('Oil Extraction', '', 'Advanced oil extraction technologies and methodologies for maximum efficiency and environmental responsibility.', '#extraction', 1, 1),
('Pipeline Building', '', 'Design, construction, and maintenance of robust pipeline infrastructure for safe energy transportation.', '#pipelines', 2, 1),
('Oil Refinement', '', 'State-of-the-art refinement processes to convert crude oil into high-quality petroleum products.', '#refinement', 3, 1);

-- ==================================================
-- ABOUT SECTION DATA
-- ==================================================

INSERT INTO `about_section` (`title`, `description`, `image_url`, `background_color`, `text_color`) VALUES
('Save The Planet!', 'Sed posuere consectetur est at lobortis. Nullam id dolor id nibh ultricies vehicula ut id elit. Nullam id dolor id nibh ultricies vehicula ut id elit. Sed posuere consectetur est at lobortis.', '', '#484939', '#ffffff');

-- ==================================================
-- FEATURES DATA
-- ==================================================

INSERT INTO `features_settings` (`section_title`, `section_subtitle`) VALUES
('Why Choose Us', 'Excellence in every aspect');

INSERT INTO `features` (`title`, `icon_url`, `description`, `sort_order`, `is_active`) VALUES
('Professional Service', '', 'Professional and reliable service with years of experience in the industry.', 1, 1),
('Quality Assurance', '', 'Quality guaranteed with international standards and certifications.', 2, 1),
('24/7 Support', '', 'Round-the-clock customer support for all your energy needs.', 3, 1);

-- ==================================================
-- PROJECTS DATA
-- ==================================================

INSERT INTO `projects_settings` (`section_title`, `section_subtitle`) VALUES
('Latest Projects', 'Our recent work');

INSERT INTO `projects` (`title`, `category`, `image_url`, `description`, `url`, `client`, `project_date`, `status`, `sort_order`, `is_active`) VALUES
('Frozen Trees In A Lake', 'Environmental', 'https://themes.webdevia.com/petroleum-gas/wp-content/uploads/2013/06/8555736073_8cde64941a_b-380x254.jpg', 'Professional environmental assessment and restoration project in arctic conditions.', '#project-1', 'Arctic Energy Corp', '2024-12-15', 'completed', 1, 1),
('Pipeline Infrastructure', 'Pipeline Construction', '', 'Major pipeline construction project connecting remote oil fields to processing facilities.', '#project-2', 'TransEnergy Solutions', '2024-10-20', 'completed', 2, 1),
('Offshore Drilling Platform', 'Offshore Operations', '', 'Advanced offshore drilling platform installation and commissioning project.', '#project-3', 'Ocean Energy Ltd', '2024-08-05', 'completed', 3, 1);

-- ==================================================
-- NEWS/ARTICLES DATA
-- ==================================================

INSERT INTO `news_settings` (`section_title`, `section_subtitle`) VALUES
('Our Latest News', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.');

INSERT INTO `news_articles` (`title`, `publication_date`, `image_url`, `excerpt`, `url`, `category`, `author`, `is_featured`, `sort_order`, `is_active`) VALUES
('Dignissim phasellus ultrices tellus', '2024-08-28', 'https://themes.webdevia.com/petroleum-gas/wp-content/uploads/2014/08/8555735563_846457fcc5_b.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', '#article-1', 'industry-news', 'Editorial Team', 1, 1, 1),
('Excepteur sint occaecat cupidatat', '2024-08-28', 'https://themes.webdevia.com/petroleum-gas/wp-content/uploads/2014/08/8555736997_53252a5258_b.jpg', 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', '#article-2', 'technology', 'Technical Team', 0, 2, 1),
('Aenean nonummy hendrerit mauris', '2024-08-28', 'https://themes.webdevia.com/petroleum-gas/wp-content/uploads/2014/08/stock-photo-oil-pump-jacks-at-sunset-sky-background-toned-316001336.jpg', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.', '#article-3', 'project-spotlight', 'Project Manager', 0, 3, 1);

-- ==================================================
-- LAYOUT SETTINGS DATA
-- ==================================================

INSERT INTO `layout_settings` (`container_width`, `mobile_breakpoint`, `tablet_breakpoint`) VALUES
('1200px', 768, 1024);

INSERT INTO `layout_sections` (`section_name`, `section_id`, `sort_order`, `is_visible`, `background_type`, `background_color`, `padding`, `margin`) VALUES
('Hero Slider', 'hero', 1, 1, 'none', '#ffffff', 0, 0),
('Services Section', 'services', 2, 1, 'none', '#f8f9fa', 80, 0),
('About Section', 'about', 3, 1, 'color', '#484939', 80, 0),
('Projects Section', 'projects', 4, 1, 'none', '#ffffff', 80, 0),
('News Section', 'news', 5, 1, 'none', '#f8f9fa', 80, 0),
('Footer', 'footer', 6, 1, 'color', '#2c3e50', 60, 0);

-- ==================================================
-- COMPONENTS DATA
-- ==================================================

INSERT INTO `components` (`name`, `type`, `is_enabled`, `is_configurable`, `description`, `settings`) VALUES
('Contact Form', 'contact-form', 1, 1, 'Interactive contact form for visitor inquiries', '{"title": "Contact Us", "style": "modern"}'),
('Scroll to Top Button', 'scroll-top', 1, 1, 'Button to smoothly scroll back to the top of the page', '{"position": "bottom-right", "style": "circle"}'),
('Loading Animations', 'animation', 1, 1, 'Smooth animations for page elements on scroll', '{"speed": "normal", "trigger": "scroll"}'),
('Google Maps Integration', 'maps', 0, 0, 'Embedded Google Maps for location display', '{}'),
('Live Chat Widget', 'chat', 0, 0, 'Real-time chat support for visitors', '{}'),
('Cookie Consent Banner', 'cookies', 0, 0, 'GDPR-compliant cookie consent notification', '{}');

INSERT INTO `custom_code` (`custom_css`, `custom_js`) VALUES
('', '');

-- ==================================================
-- FOOTER DATA
-- ==================================================

INSERT INTO `footer_settings` (`layout`, `background_type`, `background_color`, `background_size`, `gradient_start`, `gradient_end`, `text_color`) VALUES
('three-column', 'image', '#2c3e50', 'cover', '#2c3e50', '#34495e', '#ffffff');

INSERT INTO `footer_columns` (`column_number`, `title`, `content_type`, `content_data`) VALUES
(1, 'Petroleum & Gas', 'company_info', '{"logo": "", "description": "Petroleum is the leader in the country sed diam nonumy eirmod tempor invidunt ut labore and efficient strategy.", "additionalInfo": "We provide the energy to medium and big company, sadipscing elitr, sed diam nonumy."}'),
(2, 'Get in Touch', 'contact', '{"phone": "+255565195320", "email": "info@petroleum-gas.com", "address": "123 Energy Drive, Oil City, TX 75001", "businessHours": "Mon-Fri: 9AM-6PM\\nSat: 9AM-2PM\\nSun: Closed"}'),
(3, 'Newsletter', 'newsletter', '{"description": "Sign up your newsletter", "emailPlaceholder": "EMAIL ADDRESS", "buttonText": "GO", "quickLinks": [{"title": "Services", "url": "#services"}, {"title": "About Us", "url": "#about"}, {"title": "Contact", "url": "#contact"}]}');

INSERT INTO `footer_menu` (`title`, `url`, `sort_order`, `is_active`) VALUES
('Home', '/', 1, 1),
('About Us', '#about', 2, 1),
('Blog', '#blog', 3, 1),
('Contact', '#contact', 4, 1);

-- ==================================================
-- SOCIAL MEDIA DATA
-- ==================================================

INSERT INTO `social_media` (`facebook`, `instagram`, `twitter`, `linkedin`, `youtube`, `googleplus`, `show_in_header`, `show_in_footer`) VALUES
('', '', '', '', '', '', 1, 1);

-- ==================================================
-- SEO SETTINGS DATA
-- ==================================================

INSERT INTO `seo_settings_extended` (`meta_description`, `meta_keywords`, `google_analytics`, `google_search_console`, `facebook_pixel`, `custom_head_code`, `custom_body_code`) VALUES
('Professional petroleum and gas services company providing oil extraction, pipeline building, and refinement solutions.', 'petroleum, gas, oil, energy, extraction, pipeline, refinement', '', '', '', '', '');

-- ==================================================
-- UPDATE EXISTING SITE_SETTINGS WITH ENHANCED DATA
-- ==================================================

INSERT INTO `site_settings` (`site_title`, `tagline`, `logo_main`, `logo_transparent`, `logo_width`, `logo_height`, `primary_color`, `secondary_color`, `tertiary_color`) VALUES
('Petroleum and Gas – Gas and Oil WordPress theme', 'Professional Energy Solutions', '', '', 150, 50, '#FFD200', '#484939', '#1E3A8A')
ON DUPLICATE KEY UPDATE
`site_title` = VALUES(`site_title`),
`tagline` = VALUES(`tagline`),
`primary_color` = VALUES(`primary_color`),
`secondary_color` = VALUES(`secondary_color`),
`tertiary_color` = VALUES(`tertiary_color`);