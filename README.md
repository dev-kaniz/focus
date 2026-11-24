# FOCUS_OS — AI Study Focus Command Prompt

Neon full-screen Pomodoro and study dashboard with command terminal, AI coach proxy, charts, particle effects, sound, XP and local progress.

## Run with XAMPP
1. Copy the repository folder to C:/xampp/htdocs/focus.
2. Start Apache and MySQL in XAMPP.
3. Import db.sql using phpMyAdmin.
4. Enable pdo_mysql and curl in php.ini; restart Apache.
5. Configure environment variables for Apache/PHP: DB_HOST=127.0.0.1, DB_NAME=focus_os, DB_USER=root, DB_PASS=, OPENAI_API_KEY=your-secret-key. Optional OPENAI_BASE_URL and OPENAI_MODEL. Never place API keys in browser JavaScript or commit them.
6. Open http://localhost/focus/ and verify http://localhost/focus/api.php/health.

## API routing
The frontend calls api.php/session/start, api.php/session/end, api.php/stats, api.php/history and api.php/ai/chat. Configure Apache PATH_INFO/rewrite routing for api.php/path endpoints. A typical project .htaccess uses RewriteEngine On, RewriteCond %{REQUEST_FILENAME} !-f, RewriteCond %{REQUEST_FILENAME} !-d, RewriteRule ^(.*)$ api.php/$1 [QSA,L]. If your Apache setup does not support PATH_INFO, configure equivalent rewrite routing.

## Commands
/start, /pause, /resume, /stop, /stats, /goal 120, /break, /music on|off, /subject Physics, /ai your question, /help, /clear. Space toggles timer; Ctrl+K focuses command input; Up/Down recalls history.

## Optional Java service
FocusEngine.java is a Spring Boot controller source. Use Java 17+, Spring Boot 3 and spring-boot-starter-web. Put it at src/main/java/com/focusos/engine/FocusEngine.java in a Spring Boot project, then run mvn spring-boot:run. POST /analyze accepts JSON scores from 0–100.

## Production hardening
This starter uses a local demo user ID for the PHP endpoints. Add authentication, authorization, CSRF protection, rate limiting, HTTPS and per-user session ownership before public deployment. Verify PHP/MySQL configuration and CDN access in your environment.