# Deploy Render Free

Project `khai-tri-edu` hien deploy tren Render theo huong `Docker + MongoDB`.

## Checklist nhanh

1. Push code len GitHub/GitLab/Bitbucket.
2. Tao MongoDB Atlas cluster hoac mot MongoDB service co public URI.
3. Vao Render, chon `New > Blueprint`.
4. Chon repo co file `render.yaml`.
5. Dien cac env secret ben duoi.
6. Deploy lan dau.
7. Mo log de kiem tra `php artisan mongodb:bootstrap` chay xong.
8. Neu dang co du lieu cu tu MySQL dump, chay `php artisan mongodb:import-sql-dump --dry-run` de preview truoc.

## Bien moi truong bat buoc

Di vao service tren Render, tab `Environment`, dam bao co:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_KEY=base64:YOUR_32_BYTE_KEY

DB_CONNECTION=mongodb
DB_URI=mongodb+srv://USERNAME:PASSWORD@cluster0.xxxxx.mongodb.net/khai_tri_edu?retryWrites=true&w=majority&appName=KhaiTriEdu

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
LOG_CHANNEL=stderr
LOG_LEVEL=info
```

## Bien moi truong MongoDB tuy chon

Chi can dung neu ban khong muon nhung het vao `DB_URI`:

```dotenv
DB_HOST=...
DB_PORT=27017
DB_DATABASE=khai_tri_edu
DB_USERNAME=...
DB_PASSWORD=...
MONGODB_AUTH_SOURCE=admin
MONGODB_REPLICA_SET=
MONGODB_TLS=true
```

Neu da co `DB_URI` day du, thuong khong can set them bo nay.

## Import du lieu cu tu `khaitriedu.sql`

Neu repo dang co file `khaitriedu.sql`, co the import sang MongoDB bang artisan command:

```bash
php artisan mongodb:import-sql-dump --dry-run
php artisan mongodb:import-sql-dump --fresh
```

Giai thich nhanh:

- `--dry-run`: chi parse va thong ke so dong se import, khong ghi vao MongoDB
- `--fresh`: xoa cac collection app truoc khi import lai tu dump

Neu dump co table `migrations`, command se bo qua table nay.

## Bien moi truong seed demo

Neu muon Render tu do du lieu mau trong lan deploy dau:

```dotenv
RENDER_SEED_DEMO=true
RENDER_RESET_DATABASE=true
```

Sau khi deploy xong lan dau, nen doi lai:

```dotenv
RENDER_SEED_DEMO=true
RENDER_RESET_DATABASE=false
```

Neu ban de `RENDER_RESET_DATABASE=true`, moi lan redeploy se xoa sach collection app roi seed lai.

## Cach tao APP_KEY

Co the tao local bang lenh:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32));"
```

Copy gia tri in ra vao `APP_KEY` tren Render.

## MongoDB Atlas can set gi

Trong Atlas:

1. Tao database user co quyen doc ghi.
2. Whitelist IP:
   - nhanh nhat la `0.0.0.0/0`
   - an toan hon la them outbound IP cua Render neu ban co plan ho tro
3. Lay `Connection String`.
4. Thay `<username>`, `<password>`, `<db>` thanh gia tri that.

Mau:

```text
mongodb+srv://myuser:mypassword@cluster0.xxxxx.mongodb.net/khai_tri_edu?retryWrites=true&w=majority
```

## Tai khoan demo sau khi seed

- Admin: `admin@khaitri.edu.vn` / `Demo@123`
- Giang vien: `giangvien@khaitri.edu.vn` / `Demo@123`
- Hoc vien 1: `hocvien1@khaitri.edu.vn` / `Demo@123`
- Hoc vien 2: `hocvien2@khaitri.edu.vn` / `Demo@123`
- Hoc vien 3: `hocvien3@khaitri.edu.vn` / `Demo@123`

## Kiem tra deploy thanh cong

Render log nen thay cac buoc chinh:

- `APP_KEY was missing or invalid...` neu ban chua set key
- `php artisan optimize:clear`
- `php artisan mongodb:bootstrap`
- `MongoDB bootstrap completed.`
- `apache2-foreground`

Neu mo app len bi loi 500, check lai theo thu tu:

1. `DB_URI` co dung khong.
2. Atlas da mo IP chua.
3. `APP_KEY` da set chua.
4. Co de `RENDER_RESET_DATABASE=true` qua lau khong.

## Ghi chu

- Docker image da cai san PHP extension `mongodb` trong `Dockerfile`.
- Render khong con bootstrap bang MySQL dump hay SQLite fallback nua.
- Listing khoa hoc, dashboard thong ke va notification da duoc refactor de chay theo huong Mongo-friendly.
- Neu local dung XAMPP/PHP rieng, ban van can cai them PHP extension `mongodb` thi moi import that, chay feature test va ket noi MongoDB duoc.
