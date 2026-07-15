# Quick Start Guide

## ⚡ Fastest Way to Run the Project

powershell -ExecutionPolicy Bypass -Command "npm start"

```bash
npm start
```

That's it! This single command will start:
- ✅ Laravel server (http://localhost:8000)
- ✅ Vite dev server (for assets)
- ✅ Queue worker (for jobs)

---

## Alternative: Run Commands Separately

If you prefer to run each service in a separate terminal:

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

**Terminal 3:**
```bash
php artisan queue:work
```

---

## 📝 Notes

- Make sure you're in the project directory: `c:\xampp\htdocs\syst`
- The database is already set up (SQLite)
- All dependencies are installed
- To stop all processes when using `npm start`, press `Ctrl+C`

## 🌐 Access Your Application

Once running, visit: **http://localhost:8000**
