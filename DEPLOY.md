# راهنمای استقرار (IIS + PHP + MySQL)

این راهنما خلاصه‌ی مراحل نصب روی سرور ویندوز است. توضیح کامل‌تر در [README.md](README.md) آمده است.

## چک‌لیست قبل از نصب
- [ ] PHP 8.1+ (Non Thread Safe، x64) در `C:\php` نصب شده و افزونه‌های `pdo_mysql`، `mbstring`، `openssl` فعال‌اند.
- [ ] نقش‌های IIS نصب شده‌اند: CGI، Static Content، Default Document، Request Filtering، HTTP Errors، Static/Dynamic Compression.
- [ ] ماژول **URL Rewrite** برای IIS نصب شده است.
- [ ] MySQL 8.0+ یا MariaDB 10.4+ در دسترس است.
- [ ] گواهی HTTPS برای دامنه‌ی سایت آماده است.

## مراحل
1. پروژه (یا `release/accounting-source.zip`) را در `C:\inetpub\accounting` قرار دهید.
2. به کاربر `IIS_IUSRS` دسترسی نوشتن روی `storage` و `config` بدهید:
   ```bat
   icacls "C:\inetpub\accounting\storage" /grant "IIS_IUSRS:(OI)(CI)M"
   icacls "C:\inetpub\accounting\config" /grant "IIS_IUSRS:(OI)(CI)M"
   ```
3. پایگاه داده‌ی خالی و کاربر MySQL بسازید (نمونه‌ی دستور در صفحه‌ی نصب و README).
4. سایت IIS را با **Physical path = `C:\inetpub\accounting\public`** و HTTPS بسازید.
5. مرورگر را باز کنید: `https://<دامنه>/index.php?r=setup`
6. فرم را تکمیل کنید (مشخصات پایگاه داده و حساب مدیر). پس از موفقیت، `config\config.php` ساخته می‌شود. تا قبل از نصب، هر کسی که آدرس را داشته باشد می‌تواند نصب را انجام دهد؛ بلافاصله نصب کنید.
7. با حساب مدیر وارد شوید و رمز را در صورت نیاز عوض کنید.

## عیب‌یابی

| علامت | علت احتمالی | راه‌حل |
|---|---|---|
| خطای 500.19 با پیام درباره‌ی `rewrite` | ماژول URL Rewrite نصب نیست | ماژول را نصب کنید. |
| خطای 500.19 درباره‌ی `urlCompression` | نقش Static/Dynamic Compression نصب نیست | نقش را نصب کنید یا خط `<urlCompression>` را حذف کنید. |
| خطای 403 یا 500.19 با PHP | Handler تکراری (هم در سرور و هم در `web.config`) | بخش `<handlers>` را از `public/web.config` حذف کنید. |
| صفحه‌ی نصب «نوشتن ممکن نیست» می‌دهد | دسترسی نوشتن برای `IIS_IUSRS` نیست | دستورهای `icacls` مرحله‌ی ۲ را اجرا کنید. |
| «این پایگاه داده قبلاً نصب شده است» | پایگاه داده کاربر دارد | از همان حساب وارد شوید یا پایگاه داده‌ی خالی تازه بسازید. |
| «اتصال به پایگاه داده برقرار نشد» | نشانی، پورت، نام، کاربر یا رمز اشتباه؛ یا کاربر دسترسی ندارد | مشخصات را بررسی کنید و `GRANT` را دوباره اجرا کنید. |
| بعد از نصب، همه‌ی صفحات به نصب برمی‌گردند | `config\config.php` ساخته نشده | پوشه `config` را بررسی کنید؛ اگر نوشتن ممکن نبود، صفحه‌ی نصب محتوای فایل را نشان می‌دهد. |
| آدرس `index.php?r=setup` بعد از نصب `404` می‌دهد | رفتار مورد انتظار | صفحه‌ی نصب فقط تا قبل از نصب باز است. |
| فونت یا منیفست PWA بارگذاری نمی‌شود | MIME ناقص در IIS | بخش `<staticContent>` در `web.config` را بررسی کنید. |
| تغییرات استایل یا Service Worker دیده نمی‌شود | کش مرورگر یا نسخه‌ی قدیمی SW | `asset_version` در `config.php` و `CACHE_VERSION` در `sw.js` را افزایش دهید. |
| نصب PWA ممکن نیست | سایت روی HTTP است | HTTPS فعال کنید (فقط `localhost` استثناست). |

## پشتیبان‌گیری
- هر روز از پایگاه داده نسخه‌ی پشتیبان بگیرید: `mysqldump accounting > backup.sql`
- فایل `config\config.php` شامل رمز پایگاه داده است؛ آن را در جای امن نگه دارید و در گیت یا پشتیبان عمومی قرار ندهید.
