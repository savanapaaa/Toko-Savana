# DATABASE SETUP INSTRUCTIONS

## 🗄️ Database Configuration

### 1. Create Database
```sql
CREATE DATABASE toko_savana;
```

### 2. Import Database Structure
Import file `toko_savana.sql` ke database Anda menggunakan:
- **phpMyAdmin**: Import → pilih file `toko_savana.sql`
- **Command Line**: 
  ```bash
  mysql -u username -p toko_savana < toko_savana.sql
  ```

### 3. Configure Database Connection
1. Copy file `config/database.template.php` to `config/database.php`
2. Edit `config/database.php` dengan kredensial database Anda:
   ```php
   $host = "localhost";        // Database host
   $username = "your_username"; // Database username  
   $password = "your_password"; // Database password
   $database = "toko_savana";   // Database name
   ```

### 4. Test Connection
Buka aplikasi di browser dan pastikan tidak ada error koneksi database.

## 🔒 Security Notes
- File `config/database.php` tidak di-upload ke GitHub untuk keamanan
- Pastikan kredensial database Anda aman
- Gunakan password yang kuat untuk database production