# نرم‌افزار حسابداری (PHP + MySQL + IIS + PWA)

یک نرم‌افزار وب حسابداری دوطرفه (Double-Entry) با PHP، ذخیره‌سازی در MySQL، اجرا روی IIS و قابلیت نصب به‌عنوان اپلیکیشن (PWA).

## امکانات

- **چارت حساب‌ها**: تعریف حساب معین با کد و نوع (دارایی، بدهی، سرمایه، درآمد، هزینه). حسابی که در سند استفاده شده قابل حذف نیست و فقط غیرفعال می‌شود.
- **اسناد حسابداری**: ثبت، ویرایش، مشاهده، چاپ و حذف سند با چند ردیف بدهکار/بستانکار. سند فقط وقتی ذخیره می‌شود که جمع بدهکار و بستانکار برابر باشد. شماره سند خودکار است.
- **دفتر کل**: گردش و مانده هر حساب در بازه تاریخی (با مانده ابتدای دوره).
- **تراز آزمایشی**: مانده افتتاحیه، گردش دوره و مانده پایانی همه حساب‌ها با کنترل تراز.
- **داشبورد**: درآمد، هزینه و سود/زیان سال مالی جاری و آخرین اسناد.
- **تاریخ شمسی**: ورود و نمایش تاریخ‌ها به شمسی (مثلاً ۱۴۰۵/۰۷/۱۹)؛ در پایگاه داده به میلادی ذخیره می‌شوند.
- **کاربران و نقش‌ها**:
  - *مدیر سیستم*: همه امکانات و مدیریت کاربران
  - *حسابدار*: ثبت و ویرایش حساب و سند
  - *مشاهده‌گر*: فقط مشاهده و گزارش
- **امنیت**: رمز عبور با `password_hash`، محافظت CSRF در همه فرم‌ها، کوئری‌های آماده (Prepared Statements)، خروج خودکار بعد از بی‌فعالیتی، هدرهای امنیتی و Content-Security-Policy.
- **PWA**: منیفست، Service Worker، آیکون‌های استاندارد (شامل maskable)، صفحه آفلاین و دکمه نصب.

## ساختار پروژه

```
public/            ← ریشه سایت در IIS (فقط فایل‌های عمومی)
  index.php        نقطه ورود (روتر)
  web.config       تنظیمات IIS و PHP FastCGI
  sw.js            Service Worker
  manifest.webmanifest
  offline.html
  assets/          CSS، JS، فونت وزیرمتن، آیکون‌ها
app/               منطق برنامه (خارج از ریشه وب)
  Core/            Db، Auth، Csrf، Router، Installer
  Controllers/     کنترلرها
  Models/          کوئری‌ها و منطق داده
  Support/         Jalali (تاریخ شمسی)، Money (مبلغ)، Text (ارقام فارسی)
  views/           قالب‌ها (فارسی، راست‌به‌چپ)
  routes.php       جدول مسیرها و سطح دسترسی
config/
  config.sample.php   نمونه تنظیمات (config.php را از روی آن بسازید)
db/
  schema.sql       ساختار جداول MySQL
  install.php      نصب‌کننده خط فرمان
storage/logs/      لاگ خطاها
tests/unit.php     تست‌های واحد
```

## پیش‌نیازها

- Windows Server یا Windows 10/11 با **IIS**
- **PHP 8.1 یا جدیدتر** (نسخه Non Thread Safe، x64) با افزونه‌های `pdo_mysql`، `mbstring`، `openssl` (و `fileinfo` در صورت نیاز)
- **MySQL 8.0+** یا **MariaDB 10.4+**
- **HTTPS** (برای نصب PWA و امنیت کوکی‌های نشست الزامی است؛ فقط `localhost` استثناست)

## راه‌اندازی روی IIS

### ۱. نصب PHP
1. PHP را در `C:\PHP` نصب کنید (نسخه Non Thread Safe).
2. فایل `php.ini` را از `php.ini-production` بسازید و تنظیم کنید:
   ```ini
   extension_dir = "C:\PHP\ext"
   extension=pdo_mysql
   extension=mbstring
   extension=openssl
   date.timezone = Asia/Tehran
   cgi.force_redirect = 0
   ```
3. در IIS قابلیت **CGI** را فعال کنید (Windows Features → Internet Information Services → World Wide Web Services → Application Development → CGI).
4. در **IIS Manager** روی سرور کلیک کنید → **Handler Mappings** → **Add Module Mapping**:
   - Request path: `*.php`
   - Module: `FastCgiModule`
   - Executable: `C:\PHP\php-cgi.exe`
   - Name: `PHP-FastCGI`

   (اگر از نصب‌کننده رسمی PHP یا Web Platform Installer استفاده کرده‌اید، این مرحله معمولاً انجام شده است.)
   در `web.config` همین Handler با نام `PHP-FastCGI-Accounting` تعریف شده است؛ اگر در سطح سرور هم تعریف کرده‌اید و خطای تداخل دیدید، بخش `<handlers>` در `public/web.config` را حذف کنید.

### ۲. ساخت پایگاه داده MySQL
با کاربر مدیر MySQL اجرا کنید (رمز را تغییر دهید):
```sql
CREATE DATABASE accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'accounting_user'@'localhost' IDENTIFIED BY 'رمز-قوی-شما';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, REFERENCES ON accounting.* TO 'accounting_user'@'localhost';
FLUSH PRIVILEGES;
```

### ۳. کپی برنامه و تنظیمات
1. پوشه پروژه را در مسیری مثل `C:\inetpub\accounting` کپی کنید.
2. فایل `config/config.sample.php` را به `config/config.php` کپی کنید و مقادیر `db` را تنظیم کنید (`host`، `name`، `user`، `password`).
3. اگر تنظیمات `debug` را روی `false` نگه دارید (پیش‌فرض)، خطاها فقط در `storage/logs/app.log` ثبت می‌شوند.

### ۴. نصب جداول و کاربر مدیر
از خط فرمان، داخل پوشه پروژه:
```bat
C:\PHP\php.exe db\install.php
```
این دستور جداول، چارت حساب پیش‌فرض (۱۸ حساب نمونه) و کاربر **admin** را می‌سازد و رمز تصادفی را **یک‌بار** نمایش می‌دهد. می‌توانید رمز دلخواه بدهید:
```bat
C:\PHP\php.exe db\install.php --admin-password=رمز-حداقل-۸-کاراکتر
```
اجرای مجدد نصب‌کننده داده‌های موجود را حذف نمی‌کند.

### ۵. ساخت سایت در IIS
1. **IIS Manager → Sites → Add Website**
2. **Physical path**: `C:\inetpub\accounting\public` (حتماً پوشه `public`؛ پوشه `config` نباید در دسترس وب باشد)
3. Binding: HTTPS با گواهی معتبر و نام دامنه/زیردامنه‌ی سازمان
4. دسترسی نوشتن برای کاربر `IIS_IUSRS` (یا `IIS AppPool\<نام AppPool>`) روی پوشه `storage` بدهید:
   ```bat
   icacls "C:\inetpub\accounting\storage" /grant "IIS_IUSRS:(OI)(CI)M"
   ```
5. مرورگر را باز کنید و با `admin` و رمز تولیدشده وارد شوید؛ سپس از منوی **تغییر رمز** رمز را عوض کنید.

### ۶. (اختیاری) ساخت کاربران
از منوی **کاربران** (فقط مدیر) کاربر جدید با نقش مناسب بسازید.

## نصب به‌عنوان اپلیکیشن (PWA)
- در کروم یا Edge روی آیکون «نصب» در نوار آدرس یا دکمه **نصب برنامه** در نوار بالا کلیک کنید.
- در موبایل: «Add to Home Screen» (اندروید: منوی مرورگر → Install app؛ iOS: Share → Add to Home Screen).
- PWA فقط روی HTTPS (یا localhost) نصب می‌شود.
- **محدودیت آفلاین**: بدون اینترنت فقط صفحه «اتصال برقرار نیست» نمایش داده می‌شود. ثبت یا مشاهده داده در حالت آفلاین انجام نمی‌شود، زیرا داده‌های مالی فقط روی سرور نگهداری می‌شوند.
- بعد از تغییر فایل‌های استاتیک، در `config.php` مقدار `asset_version` را افزایش دهید و در `public/sw.js` مقدار `CACHE_VERSION` را تغییر دهید.

## توسعه و تست محلی

```bash
# تست‌های واحد (بدون نیاز به پایگاه داده)
php tests/unit.php

# اجرای محلی با سرور داخلی PHP (MySQL لازم است و config.php باید تنظیم شده باشد)
php db/install.php
php -S localhost:8080 -t public
```
سپس به `http://localhost:8080` بروید.

## نکات امنیتی
- حتماً از HTTPS استفاده کنید و رمز مدیر پیش‌فرض را بلافاصله تغییر دهید.
- فایل `config/config.php` را در گیت یا پشتیبان عمومی قرار ندهید (در `.gitignore` است).
- پشتیبان‌گیری منظم از پایگاه داده را فراموش نکنید (`mysqldump accounting > backup.sql`).
- برای محدودسازی تلاش‌های ورود ناموفق می‌توانید از قابلیت **Dynamic IP Restrictions** در IIS استفاده کنید.

## محدودیت‌ها و گام‌های بعدی
این نسخه بر هسته حسابداری دوطرفه تمرکز دارد. موارد زیر در نسخه فعلی **پیاده‌سازی نشده‌اند** و می‌توانند در مرحله بعد اضافه شوند: فاکتور فروش/خرید، موجودی کالا، حقوق و دستمزد، بستن دوره مالی، چند شرکتی، و گزارش‌های Excel/PDF.
