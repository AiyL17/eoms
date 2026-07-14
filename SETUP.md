# Laravel Project Setup

## Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm
- XAMPP (or any PHP server)

## Installation Complete! ✅

Your Laravel project has been successfully set up with all dependencies installed.

## Running the Project

You have **two options** to run this Laravel project:

### Option 1: Single Command (Recommended) ⭐
Simply run this command in one terminal:
```bash
npm start
```
This will automatically start all three required processes:
- Laravel development server (http://localhost:8000)
- Vite development server (for frontend assets)
- Queue worker (for background jobs)

### Option 2: Manual (Three Separate Terminals)
If you prefer to run each command separately, open three terminal windows:

#### Terminal 1: Start the Laravel Development Server
```bash
php artisan serve
```
This will start the application at `http://localhost:8000`

#### Terminal 2: Start Vite Development Server (for frontend assets)
```bash
npm run dev
```
This compiles and hot-reloads your CSS and JavaScript assets.

#### Terminal 3: Start the Queue Worker
```bash
php artisan queue:work
```
This processes queued jobs in the background.

## Project Configuration

- **Database**: SQLite (already configured and migrated)
- **Queue Driver**: Database
- **Cache Driver**: Database
- **Session Driver**: Database

## Important Notes

- The `.env` file is already configured with SQLite database
- Database migrations have been run automatically
- All npm dependencies are installed
- Composer dependencies are installed

## Troubleshooting

If you encounter any issues:

1. Make sure XAMPP is running (Apache and MySQL services)
2. Ensure you're in the project directory: `c:\xampp\htdocs\syst`
3. If using `npm start`, check that all three processes are running (you'll see colored output)
4. Clear cache if needed: `php artisan cache:clear`

## Development Workflow

1. Open a terminal
2. Navigate to `c:\xampp\htdocs\syst`
3. Run `npm start`
4. Start coding!

Your application will be available at: **http://localhost:8000**
