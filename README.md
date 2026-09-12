# GBLSS Garho — Laravel

Laravel rebuild of the GBLSS Garho school management system.
Migrating from the React + Supabase version, phase by phase.

## Setup

```bash
composer install
npm install
cp .env.example .env   # already copied for you
php artisan key:generate
php artisan migrate
php artisan db:seed     # creates the first admin user
npm run dev             # in one terminal
php artisan serve       # in another
```

Default seeded admin: `wariskatyar2015@gmail.com` / `password` (change immediately).

## Build phases
1. Laravel skeleton — **done**
2. Auth + roles (admin/teacher/parent/student)
3. Database: Supabase schema → MySQL migrations + models
4. Attendance, QR attendance, face recognition, promotion system
5. ID cards, admit cards, leaving certificates (PDF)
6. WhatsApp notifications, queued jobs
7. Public website + Education Hub, SEO/AdSense
8. PWA: manifest, service worker, offline
9. Data migration (Supabase → MySQL) + full testing

## Reference
The original site is not included in this skeleton — keep using your live
GitHub Pages + Supabase site as the reference while each phase is rebuilt.
