# SRM HRMS - Fully Synced Full-Stack Project (PHP + MySQL + Modern Frontend)
**Administrator:** Sabiha Rumman Medha (Head of HR & Admin)
**Platform Name:** SRM HRMS

---

## 📁 সিঙ্ক করা সম্পূর্ণ ফোল্ডার কাঠামো:
```text
srm_hrms/
├── index.html              <-- সম্পূর্ণ ফ্রন্টএন্ড (Rabbit Logo, Front Date, Update Info)
├── database.sql            <-- ১-ক্লিকে ইমপোর্ট করার জন্য সম্পূর্ণ MySQL ডাটাবেজ
├── database/
│   ├── schema.sql          <-- মূল টেবিল স্কিমা (PDF এর ৪টি টেবিল সহ)
│   └── seed_data.sql        <-- প্রাথমিক ডামি ডেটা রেকর্ড
├── config/
│   └── db.php              <-- MySQL ডাটাবেজ কানেকশন (PDO)
├── core/
│   ├── Database.php        <-- কুয়েরি হেল্পার ক্লাস
│   └── Response.php        <-- এপিআই রেসপন্স স্ট্যান্ডার্ডাইজার
└── api/
    ├── dashboard.php       <-- ড্যাশবোর্ড কেপিআই
    ├── employees.php       <-- কর্মী রেজিস্ট্রেশন, প্রমোশন, এবং অফবোর্ডিং
    ├── departments.php     <-- ডিপার্টমেন্ট তালিকা (বাজেট ছাড়া)
    ├── attendance.php      <-- দৈনিক হাজিরা ট্র্যাকিং
    ├── payroll.php         <-- পে-রোল ও পে-স্লিপ ডাটা
    ├── reports.php         <-- পিডিএফ Q1 মাস্টার রিপোর্ট ও CSV এক্সপোর্ট
    └── system_info.php     <-- অ্যাডমিন ও নোটিশ তথ্য
```

---

## 🚀 এক নজরে সেটআপ করার ৩টি সহজ ধাপ:

### ধাপ ১: ডাটাবেজ ইমপোর্ট করা
1. ব্রাউজারে `http://localhost/phpmyadmin` ওপেন করুন।
2. `srm_hrms` নামে একটি নতুন ডাটাবেজ তৈরি করুন।
3. **Import** ট্যাবে গিয়ে ফোল্ডারের `database.sql` ফাইলটি সিলেক্ট করে **Import** বাটনে চাপুন।

### ধাপ ২: ফাইলগুলো লোকাল সার্ভার ফোল্ডারে রাখা
* **লিনাক্স (Apache):** ফোল্ডারটি `/var/www/html/srm_hrms` ডিরেক্টরিতে রাখুন।
* **উইন্ডোজ (XAMPP):** ফোল্ডারটি `C:\xampp\htdocs\srm_hrms` লোকেশনে পেস্ট করুন।

### ধাপ ৩: ব্রাউজারে চালু করা
ব্রাউজার ওপেন করে শুধু নিচের লিংকে যান:
```text
http://localhost/srm_hrms/
```
এখন আপনার ফ্রন্টএন্ডের প্রতিটি বাটনের কাজ সরাসরি ব্যাকএন্ড API হয়ে MySQL ডাটাবেজের সাথে সিঙ্ক হয়ে কাজ করবে!
# DBMS-
