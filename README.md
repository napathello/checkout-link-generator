# NP Checkout Link Generator

A WordPress plugin for WooCommerce that helps you generate checkout links with selected products and coupons quickly from the admin page.

## Features
- Generate checkout links for WooCommerce with multiple products and quantities
- Optionally add a coupon to the checkout link
- AJAX-powered product and coupon search (by name or SKU)
- Add or remove multiple product rows in the link
- Copy the generated link or preview it instantly

## Requirements
- WordPress 5.0 or higher
- WooCommerce 10.0 or higher

## Installation
1. Download `np-checkout-link-generator.php` and related files (e.g., npclg-admin.js, npclg-admin.css if available)
2. Upload the files to `/wp-content/plugins/np-checkout-link-generator/` on your WordPress site
3. Go to the "Plugins" menu in the WordPress admin and activate "NP Checkout Link Generator"

## Usage
1. Go to "Products" > "Checkout Link Generator" in the WordPress admin menu
2. Select products and quantities (add multiple rows as needed)
3. Select a coupon (optional)
4. Click "Generate Link" to get a checkout link you can copy or preview instantly

## Notes
- This plugin uses Select2 CDN for dropdown search
- To customize the checkout URL or behavior, edit `np-checkout-link-generator.php` as needed

---

## ภาษาไทย (Thai)

### คำอธิบาย
ปลั๊กอินสำหรับ WooCommerce ที่ช่วยสร้างลิงก์ checkout พร้อมเลือกสินค้าและคูปองได้อย่างรวดเร็วผ่านหน้าแอดมิน

### ฟีเจอร์
- สร้างลิงก์ checkout สำหรับ WooCommerce โดยเลือกสินค้าและจำนวนได้หลายรายการ
- เลือกคูปองเพื่อแนบไปกับลิงก์ checkout
- ค้นหาสินค้าและคูปองด้วยระบบ AJAX (ค้นหาด้วยชื่อหรือ SKU)
- รองรับการเพิ่ม/ลบรายการสินค้าในลิงก์
- คัดลอกลิงก์หรือเปิดดูตัวอย่างได้ทันที

### ข้อกำหนดเบื้องต้น
- WordPress 5.0 ขึ้นไป
- WooCommerce 10.0 ขึ้นไป

### วิธีติดตั้ง
1. ดาวน์โหลดไฟล์ `np-checkout-link-generator.php` และไฟล์ที่เกี่ยวข้อง (เช่น npclg-admin.js, npclg-admin.css ถ้ามี)
2. อัปโหลดไฟล์ไปยังโฟลเดอร์ `/wp-content/plugins/np-checkout-link-generator/` บนเว็บไซต์ WordPress ของคุณ
3. ไปที่เมนู "ปลั๊กอิน" ในแผงควบคุม WordPress แล้วเปิดใช้งาน "NP Checkout Link Generator"

### วิธีใช้งาน
1. ไปที่เมนู "สินค้า" > "Checkout Link Generator" ในแผงควบคุม WordPress
2. เลือกสินค้าและจำนวนที่ต้องการ สามารถเพิ่มหลายรายการได้
3. เลือกคูปอง (ถ้ามี)
4. กดปุ่ม "สร้างลิงก์" ระบบจะแสดงลิงก์ checkout ที่สามารถคัดลอกหรือเปิดดูตัวอย่างได้ทันที

### หมายเหตุ
- ปลั๊กอินนี้ใช้ Select2 CDN สำหรับ dropdown search
- หากต้องการปรับแต่ง URL checkout หรือพฤติกรรมเพิ่มเติม สามารถแก้ไขโค้ดในไฟล์ `np-checkout-link-generator.php` ได้ตามต้องการ 