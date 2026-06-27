<img src="logo.png" alt="Project Logo" width="200">

# Headless WordPress + Next.js Monorepo

A production-ready headless WordPress boilerplate with Bedrock, Next.js frontend, and Docker containerization.

---

## 📦 Overview

This monorepo provides a complete headless WordPress setup with:

- **Backend**: WordPress with [Bedrock](https://roots.io/bedrock/) for modern PHP structure and Composer dependency management.
- **Frontend**: Next.js application with custom REST API integration and a responsive newspaper-style grid.
- **Infrastructure**: Docker Compose for containerized development (PHP-FPM, Nginx, MariaDB, Next.js).
- **Demo Content**: WP-CLI commands to import/delete sample news content with categories and menus.

---

## 🏗️ Project Structure

```text
.
├── backend/                  # Bedrock WordPress installation
│   ├── config/               # Environment configuration
│   ├── web/                  # Document root
│   │   ├── app/              # Plugins, themes, mu-plugins
│   │   ├── wp/               # WordPress core
│   │   └── index.php
│   └── composer.json
├── infrastructure/           # Docker and orchestration
│   ├── docker-compose.yml
│   ├── db/                   # Database configuration
│   ├── frontend/             # Next.js Dockerfile
│   ├── nginx/                # Nginx configuration
│   ├── php-fpm/              # PHP-FPM Dockerfile
│   └── wp-cli/               # WP-CLI Dockerfile
└── frontend/                 # Next.js application
    ├── components/           # Reusable React components
    ├── hooks/                # Custom React hooks
    ├── pages/                # Next.js pages
    └── lib/                  # Utilities

```

---

## 🚀 Features

- **Headless CMS** – WordPress serves as a pure content backend via REST API.
- **Next.js Frontend** – Fast server-side rendered React with dynamic routing.
- **Custom REST Endpoints**
  - `/headless-news/v1/page-data/home`
  - `/headless-news/v1/page-data/category/{slug}`
  - `/headless-news/v1/page-data/news/{slug}`
- **Modular Architecture** – PHP modules with contracts for extensibility.
- **Demo Content Seeder** – WP-CLI commands to import/delete sample content.
- **Dockerized Development** – Consistent environment with Docker Compose.
- **Image Optimization** – Custom thumbnail sizes:
  - `news-1024`
  - `news-750x500`
  - `news-270x180`
  - `news-180x120`
- **Horizontal Draggable Menu** – Responsive, scrollable navigation.

---

# 🛠️ Getting Started

## Prerequisites

- Docker & Docker Compose
- Node.js 18+ (optional for local frontend development)
- Composer (optional for local backend development)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/denisaleman/headless-wp-next-js.git headless-wp-next-js
cd headless-wp-next-js
```

### 2. Start Docker containers

```bash
cd infrastructure
docker compose up -d
```

This starts the database, PHP-FPM, Nginx, Next.js frontend, WP-CLI, and the setup helper container.

### 3. Install WordPress

Install WordPress through the browser as usual, or use WP-CLI:

```bash
docker exec -it headless-wp-wp-cli wp core install \
  --url="http://localhost" \
  --title="Headless WordPress" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com \
  --allow-root
```

> **Note:** Update the admin credentials as needed.

### 4. Configure permalink structure

After installation (either method), configure the permalink structure:

```bash
docker exec headless-wp-wp-cli wp rewrite structure '/%postname%/'
```

> **Note:** If you used the manual installation method, you can also configure permalinks from the WordPress admin dashboard under **Settings → Permalinks**.

### 5. Activate Headless News theme

```bash
docker exec headless-wp-wp-cli wp theme activate headless-news
```

### 6. Import demo content

To populate the site with sample news, categories, and menus:

```bash
docker exec headless-wp-wp-cli wp demo-content import
```

### 7. Access the site

| Service | URL |
|-----------|------|
| Frontend | http://localhost:3000 |
| WordPress Admin | http://localhost/wp/wp-admin |
| REST API | http://localhost/wp-json |

---

# 🔧 Manual Setup

## Copy environment files

```bash
cp infrastructure/.env.example infrastructure/.env
cp backend/.env.example backend-src/.env
cp frontend/.env.local.example frontend-src/.env.local
```

## Start containers

```bash
cd infrastructure
docker compose up -d
```

## Install WordPress (Bedrock)

```bash
docker compose run --rm composer create-project roots/bedrock /app --ignore-platform-req=ext-gd
```

## Import demo content

```bash
docker exec headless-wp-wp-cli wp demo-content import
```

---

# 🧩 PHP Architecture

```text
src/
├── Shared/
│   ├── Contracts/          # Interfaces (ModuleInterface)
│   └── Config/             # Shared configuration
├── Menu/                   # Menu module
├── News/                   # News module
├── Media/                  # Thumbnail module
├── DemoContent/            # Demo content seeder
├── PageData/               # Aggregated page data endpoint
└── Bootstrap.php           # Module loader
```

## Module System

Each module implements `ModuleInterface` and exposes a `register()` method which hooks into WordPress actions and filters.

## Data Aggregator

The `PageData` module uses filters to aggregate data from other modules:

- `headless_news_page_menu`
- `headless_news_page_menus`
- `headless_news_page_posts`
- `headless_news_page_article`

---

# 🖥️ Frontend Architecture

## Pages

| Route | Description |
|---------|------------|
| `/` | Homepage |
| `/category/[slug]` | Category archive |
| `/[slug]` | Single article |

## Key Components

- `PageLayout` – Wraps pages with header, menu and footer.
- `NewsGrid` – Three-column newspaper-style layout.
- `MainMenu` – Horizontal draggable navigation.
- `Footer` – Multi-column footer with nested menus.

## Data Fetching

Custom hooks:

```ts
useWordPressPageData(endpoint)
useWordPressPosts(category)
useWordPressMenu(location)
```

---

# 🧪 Development Commands

Start development server:

```bash
docker exec -it headless-wp-frontend npm run dev
```

Production build:

```bash
docker exec -it headless-wp-frontend npm run build
```

Access WP-CLI:

```bash
docker exec -it headless-wp-wp-cli wp --info
```

---

## PHP Linting

```bash
cd backend-src/web/app/themes/headless-theme

# Run PHPCS
composer lint

# Auto-fix issues
composer fix
```

---

# 🧩 Custom WP-CLI Commands

### Import demo content

```bash
docker exec headless-wp-wp-cli wp demo-content import [--dry-run]
```

Imports demo news, categories and menus.

### Delete demo content

```bash
docker exec headless-wp-wp-cli wp demo-content delete [--dry-run]
```

Removes imported content.

---

# 📌 Environment Variables

| Variable | Description | Default |
|------------|-------------|---------|
| `NEXT_PUBLIC_WP_URL` | WordPress API URL (client-side) | `http://localhost` |
| `WP_INTERNAL_URL` | WordPress API URL (server-side) | `http://nginx` |
| `WP_AUTO_INSTALL` | Automatic WordPress installation | `true` |
| `WP_ADMIN_USER` | Admin username | `admin` |
| `WP_ADMIN_PASSWORD` | Admin password | `admin` |

---


# 📄 License

MIT

---

# 🤝 Contributing

1. Fork the repository.
2. Create a feature branch:

```bash
git checkout -b feature/amazing-feature
```

3. Commit your changes:

```bash
git commit -m "Add amazing feature"
```

4. Push to your branch:

```bash
git push origin feature/amazing-feature
```

5. Open a Pull Request.

---

Built with **WordPress + Bedrock + Next.js + Docker**.