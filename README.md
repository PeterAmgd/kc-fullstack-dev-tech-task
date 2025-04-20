# KC Fullstack Dev Tech Task

This project is a full-stack application that provides a course catalog with categories and subcategories. Users can browse categories in a sidebar and view courses associated with a selected category in the dashboard. The application is built with a PHP backend (API), a JavaScript frontend, and a MySQL database, all containerized using Docker and orchestrated with Docker Compose.

## Features

- **Category Hierarchy**: View categories and subcategories (up to 4 levels deep) in the sidebar.
- **Course Counts**:
  - Main categories display the total number of courses, including those in subcategories.
  - Subcategories display only their direct course counts.
- **Course Display**: Clicking a category shows only its direct courses in the dashboard (subcategories are not displayed in the dashboard).
- **Dockerized Setup**: The application runs in Docker containers with Traefik as a reverse proxy for routing.

## Prerequisites

Before cloning and running the project, ensure you have the following installed:

- **Docker**: Required to run the application containers.
- **Docker Compose**: Required to orchestrate the containers.
- **Git**: To clone the repository.
- A modern web browser (e.g., Chrome, Firefox) to access the frontend.

## Setup Instructions

### 1. Clone the Repository

Clone the project repository to your local machine:

```bash
git clone <repository-url>
cd kc-fullstack-dev-tech-task
```

### 2. Configure Hosts (Optional but Recommended)

The application uses Traefik for routing with the following hostnames:

- Frontend: `cc.localhost`
- API: `api.cc.localhost`

To ensure these hostnames resolve correctly, add them to your `/etc/hosts` file (or `C:\Windows\System32\drivers\etc\hosts` on Windows):

```
127.0.0.1 cc.localhost
127.0.0.1 api.cc.localhost
```

### 3. Build and Start the Containers

Use Docker Compose to build and start the application:

```bash
docker-compose up --build -d
```

- The `-d` flag runs the containers in detached mode.
- This will start four containers:
  - `db-1`: MySQL database.
  - `api`: PHP backend with Apache.
  - `front`: Frontend with Apache.
  - `reverse-proxy-1`: Traefik for routing.

### 4. Apply Database Migrations

The database needs to be initialized with the schema and mock data. Run the migration script inside the `api` container:

```bash
docker exec -it kc-fullstack-dev-tech-task-api-1 bash
cd /var/www/html
php apply_migrations.php
exit
```

- This script creates the `categories` and `courses` tables and populates them with mock data.

### 5. Access the Application

- Open your browser and navigate to:
  - Frontend: `http://cc.localhost`
  - API (for testing): `http://api.cc.localhost`
- You should see the course catalog interface with a sidebar listing categories and a dashboard displaying courses.

## Usage

- **Sidebar Navigation**:
  - The sidebar displays categories and subcategories (up to 4 levels deep).
  - Main categories (e.g., "Technology") show the total course count, including subcategories.
  - Subcategories (e.g., "Software Development") show only their direct course count.
  - Click a category to view its direct courses in the dashboard.
- **Dashboard**:
  - The dashboard displays the selected category’s name and its direct courses.
  - Subcategories are not shown in the dashboard, only in the sidebar.

## Project Structure

- `api/`: PHP backend code.
  - `apply_migrations.php`: Script to initialize the database.
- `app/`: PHP backend layers.
  - `Services/`: Business logic for categories and courses.
  - `controllers/`: recieve requests and redirect to service layer.
  - `Repositories/`: Data access layer for database operations.
  - `requests/`: Data Validation.
- `front_end/`: JavaScript frontend code.
  - `js/app.js`: Main JavaScript file for fetching and rendering categories and courses.
  - `css/styles.css`: Styles for the frontend.
  - `index.php`: Styles for the frontend.
- `docker-compose.yml`: Defines the Docker services (`db`, `api`, `front`, `reverse-proxy`).
- `sql/`: SQL scripts for database migrations (used by `apply_migrations.php`).

## Troubleshooting

- **Database Connection Error**:
  - If you see `SQLSTATE[HY000] [2002]`, ensure the database host in `apply_migrations.php` is set to `db` (the Docker service name).
  - Run the migration script inside the `api` container as shown above.
- **Frontend Not Loading**:
  - Check the browser console (F12 &gt; Console) for errors.
  - Verify that `API_URL` in `front_end/js/app.js` is set to `http://api.cc.localhost`.
  - Ensure Traefik is routing correctly by checking `docker logs kc-fullstack-dev-tech-task-reverse-proxy-1`.
- **Categories or Courses Not Displaying**:
  - Confirm that migrations were applied successfully:

    ```bash
    docker exec -it kc-fullstack-dev-tech-task-db-1 mysql -uroot -proot -e "USE course_catalog; SHOW TABLES;"
    ```
  - Check API responses in the browser’s Network tab (F12 &gt; Network) for `/categories` and `/courses`.

## Development Notes

- **Backend**: The API is built with PHP 8.3 and Apache, running in the `api` container.
- **Frontend**: The frontend uses vanilla JavaScript and CSS, running in the `front` container.
- **Database**: MySQL 8.4.5 is used, running in the `db` container.
- **Routing**: Traefik handles routing for `cc.localhost` (frontend) and `api.cc.localhost` (API).

### I have insert a video (Course Catalog) for run the project if you want to check it