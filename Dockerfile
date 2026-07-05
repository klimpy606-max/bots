FROM php:8.2-apache

# نسخ ملفات البوت إلى السيرفر
COPY . /var/www/html/

# تفعيل مود الـ Rewrite إذا كنت تحتاجه للـ Webhook
RUN a2enmod rewrite

# فتح المنفذ 80 اللي بيستخدمه Render تلقائياً
EXPOSE 80
