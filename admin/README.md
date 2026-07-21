# Week 4 — Complete Admin Panel Setup

## Files (put all inside your project's `admin/` folder)
- `login.php`, `logout.php`, `dashboard.php`
- `students.php`, `delete_student.php`, `student_details.php`
- `courses.php`, `delete_course.php`
- `notices.php`, `delete_notice.php`
- `results.php`
- `assignments.php` (bonus — not explicitly detailed in the handout, but sidebar lists it, so added for completeness)
- `create_admin.php` — **one-time use, delete after creating your admin login**
- `includes/admin_auth.php`, `includes/sidebar.php`

## Setup Steps

### 1. Copy files
Copy the entire `admin/` folder into `C:\xampp\htdocs\forces-academy-lms\` — so the structure is:
```
forces-academy-lms/
  admin/
    login.php, dashboard.php, ... (all the files above)
    includes/
      admin_auth.php
      sidebar.php
  config/
    db.php   (already exists from Week 1)
```

### 2. Create your first admin account
1. In browser, go to: `http://localhost/forces-academy-lms/admin/create_admin.php`
2. Fill in a username, email, password — submit
3. **Delete `create_admin.php` immediately after** (it inserts a plain-text password into a form, not safe to leave live)

### 3. Test the admin login
1. Go to: `http://localhost/forces-academy-lms/admin/login.php`
2. Log in with the username/password you just created
3. You should land on `dashboard.php` showing 4 stat cards (Students, Courses, Assignments, Notices)

### 4. Test each feature
- **Manage Students**: search by name/roll, view a student's details, delete with confirmation popup
- **Manage Courses**: add a course, edit it (pre-fills the form), delete with confirmation
- **Manage Assignments**: add one, delete one
- **Upload Results**: pick a student + course from dropdowns, fill marks/grade, submit — check it appears in "Recently Uploaded"
- **Post Notice**: post one, confirm it shows on top of the list, delete it

### 5. Confirm session separation
- Log in as a student in one tab and as admin in another — they should not interfere, since student uses `$_SESSION['student_id']` and admin uses `$_SESSION['admin_id']` / `$_SESSION['admin_role']`.
- Try opening any `admin/*.php` page directly without an admin session — it must redirect to `admin/login.php`.

## Submission Checklist (Friday July 24)
- GitHub repo link
- Screenshots: admin dashboard with stats, student management table, add course form, post notice form, upload results form

## Common issues
- **"Call to member function on bool"** → check `config/db.php` path is correct relative to `admin/` folder (should be `../config/db.php`)
- **Admin login always fails** → make sure you created an admin via `create_admin.php` first; passwords must be hashed
- **Dropdowns empty in Upload Results** → make sure `students` and `courses` tables have data
