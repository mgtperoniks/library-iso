# 🔒 PROMPT: Safe Production Deployment untuk Library-ISO

Gunakan prompt ini untuk di-copy paste ke AI agent lain ketika mau deploy ke production.

---

## Copy Prompt Berikut:

```
Tolong bantu saya deploy kode dari local (Laragon) ke server production dengan AMAN.

ATURAN PENTING:
1. JANGAN PERNAH jalankan `migrate:fresh` atau `migrate:rollback` di production - ini MENGHAPUS semua data!
2. SELALU backup database dulu jika ada migration baru
3. Perubahan PHP/Blade biasa TIDAK butuh migration

LANGKAH-LANGKAH:

1. Di LOCAL (Laragon):
   - git status → git add -A → git commit -m "pesan" → git push prod main

2. Di SERVER (SSH):
   - cd /srv/docker/apps/Library-ISO
   - (Jika ada migration baru) Backup DB dulu:
     sudo docker compose exec db mysqldump -u root -p[PASSWORD] library-iso > /home/peroniks/backups/library_iso_backup_$(date +%Y%m%d_%H%M%S).sql
   - Pull kode: sudo git pull origin main
   - Clear cache:
     sudo docker compose exec app php artisan config:clear
     sudo docker compose exec app php artisan view:clear
     sudo docker compose exec app php artisan route:clear
   - (Jika ada migration baru) sudo docker compose exec app php artisan migrate
   - Re-cache: sudo docker compose exec app php artisan config:cache && sudo docker compose exec app php artisan route:cache

3. Verifikasi aplikasi berjalan normal

PERINTAH TERLARANG DI PRODUCTION:
❌ php artisan migrate:fresh
❌ php artisan migrate:fresh --seed
❌ php artisan migrate:rollback
❌ php artisan db:wipe
```

---

## Versi Singkat (Quick Reference)

```
DEPLOY AMAN:
1. Local: git add -A → git commit → git push prod main
2. Server: git pull → clear cache → migrate (BUKAN migrate:fresh!)
3. Verify

⛔ JANGAN: migrate:fresh, migrate:rollback, db:wipe
```
