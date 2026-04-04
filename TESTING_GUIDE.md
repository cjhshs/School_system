# Enrollment Management System - Testing Guide

## 📋 System Overview

A multi-portal enrollment system with 7 user roles:
- **Super Admin** - Manages users, courses, departments, fees, branches
- **Registrar** - Manages students, enrollments, subjects, courses
- **Dean** - Manages teachers, subject schedules, student enrollment, grade approval
- **Teacher** - Encodes grades, views assigned subjects
- **Finance** - Manages student fees, payments, permits
- **Cashier** - Processes payments, prints receipts
- **Student** - Views enrollment status, grades, profile

---

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.2+)
- Web browser (Chrome/Edge recommended)

### Step 1: Clone the Repository
```bash
git clone https://github.com/cjhshs/School_system.git
```
Or download as ZIP and extract to `C:\xampp\htdocs\enrollment_system`

### Step 2: Import Database
1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create database: `enrollment_system`
4. Import: `sql/enrollment_system.sql`

### Step 3: Access the System
Open your browser and go to:
```
http://localhost/enrollment_system/
```

---

## 🔐 Login Credentials

| Portal | Username | Password | URL |
|--------|----------|----------|-----|
| **Super Admin** | `admin` | `password123` | `/superadmin/login.php` |
| **Registrar** | `20260001` | `password123` | `/registrar/login.php` |
| **Dean (CET)** | `20260002` | `password123` | `/dean/login.php` |
| **Dean (CRIM)** | `20260005` | `password123` | `/dean/login.php` |
| **Dean (GE)** | `20260006` | `password123` | `/dean/login.php` |
| **Teacher** | `20260003` | `password123` | `/teacher/login.php` |
| **Finance** | `20260004` | `password123` | `/finance/login.php` |
| **Cashier** | `20260007` | `password123` | `/cashier/login.php` |
| **Student** | `202600004` | `ecenorio202600004` | `/student/login.php` |

---

## 🧪 Complete Testing Flow

### Phase 1: Super Admin Setup
1. Login as **Super Admin** (`admin` / `password123`)
2. **Dashboard** - Check statistics cards
3. **Users** - View all users, try creating a new user
4. **Departments** - View departments, verify deans are assigned
5. **Fees** - View tuition fees per course
6. **Roles** - View available roles and permissions

### Phase 2: Registrar - Open Subjects
1. Login as **Registrar** (`20260001` / `password123`)
2. Go to **Subjects**
3. You should see **408 subjects** from the database
4. Filter by Course (e.g., BSCR), Semester, Year Level
5. Click the **green door icon** 🚪 to "Open" a subject
6. Set: Schedule, Room, Year Level, Semester, Capacity
7. Click **Open Subject**
8. Status should change to **Open** (green badge)

### Phase 3: Dean - Assign Teacher & Enroll Students
1. Login as **Dean** (`20260002` / `password123` for CET)
2. Go to **Subjects** - You should see subjects for your department
3. Click **Edit** on any open subject
4. **Assign Teacher**: Select from instructor dropdown → Update
5. **Enroll Students**: Scroll down → Select student → Add
6. Go to **Teachers** - View/Add teachers in your department
7. Go to **Approve Grades** - Review submitted grades

### Phase 4: Teacher - Encode Grades
1. Login as **Teacher** (`20260003` / `password123`)
2. Go to **Subjects** - Should see assigned subjects
3. Click a subject → **Encode Grades**
4. Enter Prelim, Midterm, Final for each student
5. Click **Save** per student
6. Click **Submit All Grades** to send for dean approval

### Phase 5: Dean - Approve Grades
1. Login as **Dean**
2. Go to **Approve Grades**
3. Review pending grades
4. Click **Approve** or **Approve All**

### Phase 6: Finance & Cashier - Payments
1. Login as **Finance** (`20260004` / `password123`)
2. Go to **Students** - View student balances
3. Go to **Payments** - Record a payment
4. Go to **Permits** - Issue enrollment permits
5. Login as **Cashier** (`20260007` / `password123`)
6. Process payments and print receipts

### Phase 7: Student - View Results
1. Login as **Student** (`202600004` / `ecenorio202600004`)
2. **Dashboard** - View enrollment status
3. **Grades** - Should see approved grades
4. **Profile** - View personal information
5. **Export Grades** - Download/print grade report

---

## ✅ Testing Checklist

### Super Admin
- [ ] Dashboard stats display correctly
- [ ] Can create/edit/delete users
- [ ] Can view/edit departments
- [ ] Can manage tuition fees
- [ ] Can view activity logs

### Registrar
- [ ] Can view all 408 subjects
- [ ] Can filter subjects by course/semester/year
- [ ] Can open subjects (set schedule, room, capacity)
- [ ] Can view/manage students
- [ ] Can enroll students
- [ ] Can manage courses

### Dean
- [ ] Can view subjects for their department only
- [ ] Can assign teachers to subjects
- [ ] Can enroll students in subjects
- [ ] Can manage teachers
- [ ] Can approve grades
- [ ] Cannot approve grades from other departments

### Teacher
- [ ] Can see only assigned subjects
- [ ] Can encode prelim/midterm/final grades
- [ ] Can save individual grades
- [ ] Can submit all grades for approval
- [ ] Cannot see other teachers' subjects

### Finance
- [ ] Can view student balances
- [ ] Can record payments
- [ ] Can view payment history
- [ ] Can issue permits (Valid/Not Valid)
- [ ] Can print permits
- [ ] Can export reports

### Cashier
- [ ] Can process payments
- [ ] Can print receipts
- [ ] Can view payment history
- [ ] Can search students

### Student
- [ ] Can view enrollment status
- [ ] Can view grades (only approved)
- [ ] Can view profile
- [ ] Can export/print grades
- [ ] Cannot see other students' data

---

## 🐛 Common Issues & Fixes

### "Student not found" error
- Check that the student exists in the database
- Verify the URL uses the correct student ID

### "No subjects found" for Dean
- The Registrar must first **open** subjects for that department
- Verify the dean's department matches the subject's course department

### Grades not showing in Student Portal
- Teacher must **save** the grade first
- Dean must **approve** the grade before it shows as official
- Draft/Submitted grades show as "Pending" or "Draft"

### "Invalid CSRF token"
- Refresh the page and try again
- This is a security feature to prevent duplicate submissions

### Database connection error
- Ensure MySQL is running in XAMPP
- Check `config.php` has correct database credentials
- Verify database `enrollment_system` exists

---

## 📁 Project Structure

```
enrollment_system/
├── superadmin/       # Super Admin portal
├── registrar/        # Registrar portal
├── dean/             # Dean portal
├── teacher/          # Teacher portal
├── finance/          # Finance portal
├── cashier/          # Cashier portal
├── student/          # Student portal
├── includes/         # Shared libraries
│   ├── cache.php     # Dashboard caching
│   ├── csrf.php      # CSRF protection
│   ├── pagination.php # Pagination helper
│   ├── validator.php  # Input validation
│   └── file_upload.php # File upload handler
├── js/
│   └── common.js     # Shared JavaScript
├── sql/
│   ├── complete_schema.sql  # Full schema
│   └── enrollment_system.sql # Database dump
└── config.php        # Database config & helpers
```

---

## 📞 Support

If you encounter bugs or issues:
1. Check the error log: `logs/error.log`
2. Check browser console (F12) for JavaScript errors
3. Report the issue with:
   - What you were doing
   - What error you saw
   - Screenshot if possible

---

*Last Updated: April 4, 2026*
