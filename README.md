# Procedural PHP E‑Commerce Bookstore (Server-side only)

**Overview**
- A simple procedural PHP bookstore using MySQL (mysqli procedural functions).
- No OOP. Minimal to NO JavaScript — all interactions use server-side processing and page reloads.
- Prices in Nepalese Rupees (Rs.). Shop location: Swayambhu, Nepal.

**Structure**
- `index.php` - home / product listing
- `config.php` - database + site config (edit DB/Khalti keys)
- `functions.php` - helper functions
- `includes/header.php`, `includes/footer.php`, `includes/db.php`
- `register.php`, `login.php`, `logout.php`
- `products.php`, `product.php`
- `cart.php`, `add_to_cart.php`, `update_cart.php`, `remove_from_cart.php`
- `checkout.php`, `process_checkout.php`, `payment_success.php`, `payment_fail.php`
- `admin/` - admin authentication and product CRUD (admin/products.php, admin/add-product.php, admin/edit-product.php, admin/delete-product.php)
- `sql/bookstore.sql` - SQL schema + sample data
- `uploads/` - folder to store book images (set writable)

**Khalti Payment**
- `config.php` contains placeholders for Khalti `public_key` and `secret_key`.
- `process_checkout.php` demonstrates a **server-side redirect** flow:
  - If Khalti keys are set, it prepares a server-side payload (instructions in README).
  - If keys are empty, it redirects to an internal simulated payment success.

**Setup**
1. Extract into your server web root (e.g., `htdocs` or `www`).
2. Create a MySQL database and import `sql/bookstore.sql`.
3. Edit `config.php` to set DB credentials and (optionally) Khalti keys.
4. Ensure `uploads/` is writable by the web server (e.g., `chmod 755 uploads` or `chmod 775`).
5. Admin default credentials (see SQL seed): email `admin@example.com`, password `admin123` (change immediately).

**Security & Notes**
- Passwords use `password_hash()` and `password_verify()`.
- Inputs validated using `preg_match()` and `filter_var()` in registration and admin forms.
- All cart actions use sessions; images handled via `move_uploaded_file()`.
- No JS required; add/remove items cause redirect to processing pages which then redirect back.

If you want any feature expanded (coupons, categories, reports), tell me and I'll extend it.

