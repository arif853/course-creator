Below is a **professional, ready-to-use `README.md`** for your **Laravel Course Management System** project.  
It includes **project setup instructions**, **features**, **tech stack**, **folder structure**, and **contribution guidelines**.

---

```markdown
# CourseHub – Laravel Course Management System

A **modern, scalable course management platform** built with **Laravel 10+**, **Bootstrap 5**, and **jQuery**.  
Supports **rich content modules**, **drag-and-drop image uploads**, **background file processing**, and **admin CRUD**.

---

## Features

| Feature | Status |
|-------|--------|
| Create & Edit Courses | Done |
| Rich Module System (Text, Image, Video, Link) | Done |
| Drag & Drop + Reorder Feature Images | Done |
| Background Uploads (Queue + Temp Storage) | Done |
| File Previews (Images & Videos) | Done |
| Responsive Admin Panel | Done |
| Validation & Error Handling | Done |
| Clean DB Structure (Eloquent) | Done |

---

## Tech Stack

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Bootstrap 5, jQuery, Summernote
- **Database**: MySQL / PostgreSQL
- **Queue**: Laravel Queue (Redis / Database)
- **Storage**: Local + `public` disk (configurable)
- **File Processing**: Background jobs (`ProcessCourseUploads`)

---

## Folder Structure

```
app/
├── Jobs/ProcessCourseUploads.php
├── Models/Course.php, Module.php, Content.php
resources/
├── views/admin/courses/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── index.blade.php
public/
└── storage/app/public/ (symlinked)
storage/
└── app/temp/ (temp upload dir)
```

---

## Project Setup (Optional)

> **Skip if already set up**

### 1. Clone & Install

```bash
git clone [https://github.com/yourusername/coursehub.git](https://github.com/arif853/course-creator.git)
cd coursehub
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coursehub
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis   # or 'database'
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Run Migrations & Seed

```bash
php artisan migrate
php artisan db:seed --class=AdminUserSeeder   # optional
```

### 4. Create Storage Links & Temp Dir

```bash
php artisan storage:link
mkdir -p storage/app/temp
chmod 775 storage/app/temp
```

### 5. Start Queue Worker

```bash
php artisan queue:work --queue=uploads
```

### 6. Run Dev Server

```bash
php artisan serve
```

Visit: [http://localhost:8000/admin/courses](http://localhost:8000/admin/courses)

---

## Models & Relationships

```php
Course → hasMany → Module
Module → hasMany → Content
Course → stores JSON → feature_images
Content → type: text|image|video|link
```

---

## Background Uploads

- Large files (video, images) → stored in `storage/app/temp/course_{id}/`
- `ProcessCourseUploads` job moves them to `public/storage/`
- Old files **automatically cleaned** on replace/remove

---

## Contributing

1. Fork the repo
2. Create feature branch: `git checkout -b feature/xyz`
3. Commit: `git commit -m 'Add xyz'`
4. Push: `git push origin feature/xyz`
5. Open Pull Request

---

## License

[MIT License](LICENSE)
