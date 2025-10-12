-- Script para actualizar el branding de Hackademia a CrowDojo Academy

-- Actualizar videos existentes con temática de CrowDojo
UPDATE videos SET 
    title = 'Kata 1: Introducción al Hacking Ético',
    description = 'Aprende los fundamentos del hacking ético y la filosofía del guerrero cibernético'
WHERE title = 'Introducción al Bug Bounty';

UPDATE videos SET 
    title = 'Kata 2: Reconocimiento y Enumeración',
    description = 'Técnicas de reconocimiento para identificar objetivos como un verdadero ninja'
WHERE title = 'Reconocimiento y Enumeración';

UPDATE videos SET 
    title = 'Combate 1: Vulnerabilidades Web Comunes',
    description = 'Domina las vulnerabilidades más comunes del OWASP Top 10'
WHERE title = 'Vulnerabilidades Web Comunes';

UPDATE videos SET 
    title = 'Combate 2: Maestría en SQL Injection',
    description = 'Técnicas avanzadas de inyección SQL para el guerrero cibernético'
WHERE title = 'SQL Injection';

-- Actualizar módulos
UPDATE videos SET module = 'Módulo 1: Fundamentos del Dojo' WHERE module = 'Módulo 1: Fundamentos';
UPDATE videos SET module = 'Módulo 2: Técnicas de Combate' WHERE module = 'Módulo 2: Vulnerabilidades';

-- Agregar más videos temáticos
INSERT INTO videos (title, description, video_url, module, order_num, duration) VALUES
('Kata 3: El Camino del Guerrero Cibernético', 'Filosofía y ética del hacking responsable', 'https://example.com/video5', 'Módulo 1: Fundamentos del Dojo', 3, '18:30'),
('Combate 3: XSS - El Arte del Engaño', 'Domina las técnicas de Cross-Site Scripting', 'https://example.com/video6', 'Módulo 2: Técnicas de Combate', 3, '25:45'),
('Dojo Avanzado: CSRF y Manipulación de Sesiones', 'Técnicas avanzadas para el guerrero experto', 'https://example.com/video7', 'Módulo 3: Maestría Avanzada', 1, '32:15'),
('El Cuervo Observa: OSINT y Reconocimiento', 'Técnicas de inteligencia como el cuervo observador', 'https://example.com/video8', 'Módulo 3: Maestría Avanzada', 2, '28:50');