# PRD — IniBisa Task Management

## 1. Product Overview

**Nama Produk:** IniBisa  
**Jenis:** Internal Product & Task Management System  
**Platform:** Web Application  
**Pengguna Awal:** 3 anggota tim IniBisa  
**Database:** MySQL

IniBisa merupakan aplikasi internal untuk membantu tim mengatur ide, produk, milestone, task, progress, dan aktivitas kerja secara terstruktur.

Sistem bukan hanya task manager, tetapi menjadi pusat kerja internal yang menghubungkan:

**Goal → Audience → Product → Milestone → Task → Progress**

Tujuan utamanya agar setiap anggota tim dapat mengetahui:

- produk apa yang sedang dibuat,
- siapa target penggunanya,
- apa tujuan produknya,
- siapa yang mengerjakan,
- progress pekerjaan,
- prioritas saat ini,
- ide produk berikutnya,
- dan arah pengembangan IniBisa.

---

## 2. Background

IniBisa merupakan grup produk digital dengan banyak produk yang dikelompokkan berdasarkan target pengguna atau tahap kehidupan.

Contoh kategori target pengguna:

- Pacaran
- Persiapan Menikah
- Menikah
- Keluarga
- Anak

Contoh produk:

### Pacaran
- Website Minta Maaf
- Anniversary Website
- Digital Love Letter
- Couple Games

### Persiapan Menikah
- Undangan Digital
- Wedding Planner
- Wedding Checklist
- Budget Planner

### Keluarga
- Family Planner
- Couple Finance
- Family Calendar

### Anak
- Buku Mewarnai
- Printable Worksheet
- Activity Book
- Educational Content

Karena jumlah produk dapat terus bertambah, dibutuhkan sistem internal agar seluruh pekerjaan tetap terarah dan setiap anggota memahami prioritas tim.

---

## 3. Product Goals

Sistem harus membuat anggota tim dapat membuka aplikasi dan langsung mengetahui:

> Apa yang sedang kita bangun, kenapa kita membangunnya, siapa yang mengerjakan, sudah sampai mana progress-nya, dan apa yang harus dilakukan berikutnya.

Tujuan utama:

1. Mengatur seluruh produk IniBisa.
2. Mengelompokkan produk berdasarkan target pengguna.
3. Mengelola ide produk.
4. Mengelola task seluruh anggota tim.
5. Menentukan prioritas kerja.
6. Memantau progress produk.
7. Mengetahui pekerjaan setiap anggota.
8. Mendukung kolaborasi realtime.
9. Menyimpan histori aktivitas.
10. Membantu tim menentukan fokus mingguan dan bulanan.

---

## 4. User Roles

### Admin

Admin memiliki akses penuh:

- membuat dan mengelola member,
- membuat audience,
- membuat produk,
- membuat milestone,
- membuat task,
- assign task,
- mengubah status task,
- mengelola idea inbox,
- menentukan product stage,
- mengatur goal,
- melihat seluruh aktivitas,
- mengatur workspace.

### Member

Member dapat:

- melihat seluruh produk,
- melihat task,
- mengerjakan task,
- mengubah status task,
- membuat task,
- membuat ide,
- memberikan komentar,
- mention anggota,
- upload attachment,
- melihat activity,
- melihat dashboard tim.

Untuk MVP, permission dibuat sederhana.

---

## 5. Struktur Utama

```text
IniBisa
│
├── Audience
│   └── Product
│       ├── Milestone
│       │   └── Task
│       │       └── Subtask
│       ├── Ideas
│       └── Activity
│
├── Goals
├── Idea Inbox
├── Product Pipeline
└── Team
```

---

## 6. Main Navigation

```text
INIBISA

Overview

WORK
├── My Tasks
├── Board
├── Timeline
└── Goals

PRODUCT
├── Products
├── Audiences
├── Ideas
└── Pipeline

TEAM
├── Members
└── Activity

ACCOUNT
└── Settings
```

Navigation wajib responsive.

---

## 7. Dashboard

Route:

```text
/dashboard
```

Dashboard menampilkan gambaran keseluruhan kerja tim.

### Main Focus

Contoh:

```text
FOCUS THIS MONTH

Launch IniBisa Invitation

Progress
██████████████░░░ 72%

Target Launch
20 Oktober 2026
```

### Statistics

- Active Products
- Tasks In Progress
- Tasks Completed
- Blocked Tasks
- Ideas
- Ready To Launch

### Team Workload

Menampilkan:

- nama anggota,
- task aktif,
- progress,
- jumlah task aktif.

### Recent Activity

Contoh:

```text
21:30
Hael moved "Wedding Editor"
In Progress → Review

21:20
Member 3 added an idea
"Couple Question Generator"

20:45
Member 2 completed
"Romantic Template #2"
```

Activity harus realtime.

### Upcoming Deadline

Kelompok:

- Overdue
- Today
- Tomorrow
- This Week

---

## 8. Audience Management

Route:

```text
/audiences
```

Audience merupakan kelompok target pengguna.

Contoh:

- Pacaran
- Mau Menikah
- Sudah Menikah
- Keluarga
- Anak

Data:

```text
name
slug
description
icon
status
order
```

Admin dapat create, edit, archive, dan mengatur urutan.

---

## 9. Product Management

Route:

```text
/products
/products/{product}
```

Field produk:

```text
Product Name
Audience
Description
Problem
Solution
Target User
Business Model
Price Idea
Product Owner
Product Stage
Priority
Progress
Target Launch
Status
```

---

## 10. Product Detail

Tabs:

```text
Overview
Tasks
Milestones
Ideas
Activity
Files
```

Overview menampilkan:

- product goal,
- description,
- owner,
- member,
- target user,
- product stage,
- progress,
- target launch,
- priority,
- latest activity.

---

## 11. Product Pipeline

Route:

```text
/pipeline
```

Stage:

```text
Idea
↓
Validation
↓
Design
↓
Development
↓
Content Preparation
↓
Ready To Launch
↓
Launched
↓
Growth
```

Tampilan menggunakan Kanban.

Product dapat dipindahkan dengan drag & drop.

Perubahan tersimpan otomatis.

---

## 12. Milestone

Setiap Product dapat memiliki beberapa milestone.

Contoh:

```text
MVP v1
↓
Launch v1
↓
Payment Integration
↓
Template Expansion
↓
Growth Experiment
```

Field:

```text
name
description
product_id
start_date
due_date
status
progress
```

Progress milestone dihitung berdasarkan task.

---

## 13. Task Management

Struktur:

```text
Product
↓
Milestone
↓
Task
↓
Subtask
```

Field task:

```text
Title
Description
Product
Milestone
Assignee
Created By
Priority
Status
Start Date
Due Date
Progress
Estimated Time
Labels
Attachments
```

---

## 14. Task Status

Gunakan:

```text
Backlog
Planned
In Progress
Review
Done
```

Tambahkan kondisi:

```text
Blocked
```

Blocked tidak perlu menjadi kolom utama.

Gunakan:

```text
is_blocked
blocked_reason
```

---

## 15. Task Priority

```text
P0 — Critical
P1 — High
P2 — Medium
P3 — Low
```

---

## 16. Task Board

Route:

```text
/tasks/board
```

Kolom:

```text
BACKLOG
PLANNED
IN PROGRESS
REVIEW
DONE
```

Fitur:

- drag task antar status,
- drag ordering,
- filter member,
- filter product,
- filter priority,
- filter milestone,
- filter deadline,
- search task.

Perubahan status harus realtime.

---

## 17. My Tasks

Route:

```text
/my-tasks
```

Section:

- Today
- Upcoming
- Overdue
- In Progress
- Waiting / Blocked
- Completed

---

## 18. Task Detail

Informasi:

```text
Title
Description
Status
Priority
Assignee
Product
Milestone
Deadline
Subtasks
Attachments
Comments
Activity
```

User dapat:

- edit task,
- pindah status,
- assign member,
- membuat subtask,
- komentar,
- mention anggota,
- upload file,
- menandai blocked.

---

## 19. Subtasks

Contoh:

```text
Wedding Editor

☑ Input nama pasangan
☑ Upload foto
☐ Pilih template
☐ Musik
☐ Preview
☐ Publish
```

Progress task dapat dihitung:

```text
completed subtasks / total subtasks
```

---

## 20. Idea Inbox

Route:

```text
/ideas
```

Semua member dapat menambahkan ide.

Field:

```text
Title
Description
Audience
Problem
Proposed Solution
Target User
Monetization Idea
Submitted By
Status
Votes
Comments
```

Status:

```text
New
Discuss
Research
Approved
Rejected
Planned
```

---

## 21. Idea Detail

Anggota dapat:

- vote,
- comment,
- approve,
- reject,
- convert to product.

---

## 22. Convert Idea to Product

Saat ide disetujui, tersedia tombol:

```text
Convert to Product
```

Mapping:

```text
Idea Title → Product Name
Audience → Product Audience
Problem → Product Problem
Solution → Product Solution
Monetization → Business Model
```

Idea kemudian berubah status menjadi:

```text
Planned
```

---

## 23. Goals

Route:

```text
/goals
```

Jenis goal:

```text
Monthly
Quarterly
Product
```

Contoh:

```text
October Goal

Launch IniBisa Invitation

Key Results:

☑ Invitation Editor
☑ Template System
☐ Payment
☐ Landing Page
☐ TikTok Launch Campaign
```

---

## 24. Weekly Planning

Contoh:

```text
WEEK 41

MAIN GOAL

Finish MVP IniBisa Invitation

P0
Wedding Editor

P1
Payment Integration

P1
Landing Page

P2
Analytics
```

Task dapat dimasukkan ke minggu tertentu.

---

## 25. Weekly Review

Contoh:

```text
Weekly Review

Tasks Planned
18

Tasks Completed
14

Carried Over
3

Blocked
1

Completion Rate
78%
```

Tidak perlu Scrum kompleks.

---

## 26. Comments

Setiap Task, Idea, dan Product dapat memiliki komentar.

Support:

- @mention
- emoji
- attachment
- reply

---

## 27. Notifications

Route:

```text
/notifications
```

Notification muncul ketika:

- Task assigned
- Mention
- Comment
- Deadline approaching
- Task blocked
- Task completed
- Product stage changed
- Idea approved

Support:

```text
Unread
Read
Mark All As Read
```

Notification harus realtime.

---

## 28. Realtime System

Gunakan:

```text
Laravel Reverb
Laravel Broadcasting
Laravel Echo
```

Realtime event:

```text
TaskCreated
TaskUpdated
TaskStatusChanged
TaskAssigned

CommentCreated

IdeaCreated
IdeaUpdated

ProductUpdated
ProductStageChanged

NotificationCreated

ActivityCreated
```

Jika satu anggota mengubah task, anggota lain melihat perubahan tanpa reload.

---

## 29. Online Presence

Dapat menampilkan anggota aktif:

```text
● Hael
Online

● Member 2
Online

○ Member 3
Offline
```

Gunakan Presence Channel.

---

## 30. Activity Log

Route:

```text
/activity
```

Aktivitas yang dicatat:

- task dibuat,
- task dipindahkan,
- task selesai,
- product stage berubah,
- idea dibuat,
- idea disetujui,
- task di-assign,
- deadline berubah.

Field:

```text
user
action
subject_type
subject_id
metadata
created_at
```

---

## 31. Team Members

Route:

```text
/team
```

Informasi member:

```text
Name
Avatar
Role
Position
Current Task
Active Tasks
Completed Tasks
```

---

## 32. Member Detail

Menampilkan:

- Current Tasks
- Completed Tasks
- Products
- Recent Activity

---

## 33. Global Search

Search dapat menemukan:

```text
Products
Tasks
Ideas
Members
```

Shortcut:

```text
CMD + K
CTRL + K
```

---

## 34. Command Palette

Action:

```text
Create Task
Create Idea
Create Product
Search Task
Open Product
Go to My Tasks
```

---

## 35. Database

Gunakan:

```text
MySQL
```

Recommended tables:

```text
users

audiences

products
product_members

milestones

tasks
task_assignees
task_labels

subtasks

labels

ideas
idea_votes

goals
goal_items

weekly_plans

comments

attachments

notifications

activities
```

---

## 36. Main Relationships

```text
audience
hasMany products

product
belongsTo audience
hasMany milestones
hasMany tasks
hasMany members
hasMany ideas

milestone
belongsTo product
hasMany tasks

task
belongsTo product
belongsTo milestone
belongsTo creator
belongsToMany assignees
hasMany subtasks
hasMany comments

idea
belongsTo audience
belongsTo user
hasMany votes
hasMany comments
```

---

## 37. Suggested Tasks Table

```text
id

product_id
milestone_id

title
description

status
priority

start_date
due_date

estimated_minutes

position

is_blocked
blocked_reason

created_by

created_at
updated_at
```

Pivot:

```text
task_assignees
```

---

## 38. Attachments

Attachments dapat digunakan pada:

- Task
- Idea
- Product
- Comment

Untuk MVP gunakan Laravel Storage local/public.

Nantinya dapat dipindahkan ke:

- S3
- Cloudflare R2

---

## 39. Authentication

Fitur:

```text
Login
Logout
Forgot Password
Reset Password
```

Tidak perlu public registration.

Member dibuat melalui Admin.

---

## 40. UI / UX Direction

Style:

- Modern
- Clean
- Minimal
- Friendly
- Slightly Playful
- Professional

Jangan terlalu corporate seperti Jira.

Typography:

```text
Poppins
```

Aplikasi wajib responsive dan tetap nyaman digunakan melalui smartphone.

---

## 41. UI Components

Gunakan reusable components:

```text
Button
Input
Select
Combobox
Modal
Drawer
Dropdown
Avatar
Badge
Tabs
Tooltip
Progress Bar
Kanban Card
Task Card
Product Card
Idea Card
Activity Item
Empty State
Skeleton Loader
Toast
```

---

## 42. Responsive Design

Desktop:

```text
Sidebar + Content
```

Tablet:

```text
Collapsed Sidebar
```

Mobile:

```text
Drawer Navigation
Single Column
Horizontal Kanban Scroll
Bottom Sheet / Drawer Task Detail
```

---

## 43. Technology Stack

### Backend

```text
Laravel 12
PHP 8.3+
```

### Frontend

```text
React
Inertia.js
Tailwind CSS
```

### Database

```text
MySQL
```

### Realtime

```text
Laravel Reverb
Laravel Broadcasting
Laravel Echo
```

### Utilities

```text
Axios
date-fns
Lucide Icons
dnd-kit
```

---

## 44. Backend Architecture

Gunakan struktur sederhana:

```text
Controllers
Models
Form Requests
Policies
Services jika diperlukan
Events
Listeners
Broadcast Events
```

Hindari over-engineering.

---

## 45. API Approach

Karena menggunakan Laravel + Inertia, tidak perlu membuat REST API penuh.

Gunakan standard Laravel routes:

```text
GET
POST
PUT/PATCH
DELETE
```

API dapat ditambahkan nanti jika ada aplikasi mobile.

---

## 46. Performance

Target:

```text
Page Load < 2 seconds
Interaction responsive
Realtime update < 2 seconds
```

Gunakan:

- database indexes,
- eager loading,
- pagination,
- lazy loading,
- query optimization.

---

## 47. Security

Implementasikan:

- Authentication
- Authorization
- CSRF Protection
- Input Validation
- Rate Limiting
- Secure File Upload
- Laravel Policies

Semua route internal hanya dapat diakses user login.

---

## 48. Audit Trail

Aktivitas penting:

```text
Task Created
Task Deleted
Task Status Changed
Task Assigned
Deadline Changed

Product Created
Product Stage Changed

Idea Approved
Idea Converted

Member Added
```

---

## 49. MVP Scope

Versi pertama:

```text
Authentication

Dashboard

Audience Management

Product Management

Product Detail

Product Pipeline

Milestone

Task Management

Subtask

Kanban Board

My Tasks

Idea Inbox

Idea → Product

Comments

Mentions

Notifications

Realtime Updates

Activity Log

Goals

Team Members
```

---

## 50. Out of Scope for MVP

Belum perlu dibuat:

```text
Financial Management
Revenue Dashboard
Advanced Product Analytics
TikTok API
Social Media Auto Posting
AI Assistant
File Collaboration
Video Call
Payroll
Public API
Mobile Native App
```

---

## 51. Future Features

### Content Management

```text
Content Ideas
TikTok Scripts
Content Calendar
Posting Status
Performance
```

### Product Analytics

```text
Users
Orders
Revenue
Conversion
Traffic
```

### Revenue Dashboard

Menampilkan revenue tiap produk.

### Knowledge Base

Untuk menyimpan:

- Brand Guidelines
- Marketing Strategy
- Technical Documentation
- SOP
- Meeting Notes
- Research

### Bug Tracker

Untuk produk yang sudah launch:

```text
Bug
Feature Request
Improvement
```

---

## 52. Main User Flow

### Membuat Produk

```text
Login
↓
Products
↓
Create Product
↓
Pilih Audience
↓
Isi Problem
↓
Isi Solution
↓
Tentukan Owner
↓
Tentukan Target Launch
↓
Create
```

### Membuat Task

```text
Product
↓
Tasks
↓
Create Task
↓
Assign Member
↓
Priority
↓
Deadline
↓
Create
```

Task langsung muncul realtime kepada anggota terkait.

### Mengajukan Ide

```text
Idea Inbox
↓
New Idea
↓
Masukkan Problem
↓
Masukkan Solution
↓
Submit
↓
Discussion
↓
Approve
↓
Convert to Product
```

---

## 53. Default Product Workflow

```text
Idea
Validation
Design
Development
Content Preparation
Ready To Launch
Launched
Growth
```

Untuk MVP dapat hardcoded terlebih dahulu.

---

## 54. Progress Calculation

Progress produk dihitung berdasarkan task.

Contoh:

```text
Total Task
20

Completed
12

Progress
60%
```

Task dengan status `Done` dianggap completed.

Milestone menggunakan perhitungan serupa.

---

## 55. Dashboard Priority

Urutan informasi:

```text
Current Goal
Product Progress
My Tasks
Team Workload
Upcoming Deadline
Blocked Tasks
Product Pipeline
Recent Activity
```

---

## 56. Empty States

Contoh:

```text
Belum ada task.

Mulai dengan membuat pekerjaan pertama untuk tim IniBisa.

[ + Buat Task ]
```

```text
Belum ada ide.

Punya ide produk baru?

[ + Tambah Ide ]
```

---

## 57. Product Vision

IniBisa bukan sekadar task manager.

Sistem ini menjadi pusat kerja internal untuk seluruh produk IniBisa:

```text
IDEA
↓
PRODUCT
↓
PLANNING
↓
DEVELOPMENT
↓
CONTENT
↓
LAUNCH
↓
GROWTH
```

Dengan demikian, meskipun jumlah produk terus bertambah, seluruh tim tetap dapat memahami arah kerja secara jelas.

---

## 58. MVP Success Criteria

MVP dianggap berhasil apabila:

- seluruh anggota menggunakan sistem untuk task harian,
- seluruh produk aktif tercatat,
- setiap task memiliki owner,
- tim dapat melihat progress produk,
- ide tidak lagi tercecer di chat,
- anggota dapat mengetahui pekerjaan rekannya,
- task update muncul realtime,
- weekly focus dapat terlihat,
- produk yang sedang diprioritaskan jelas.

---

## 59. Core Principle

Setiap fitur harus mendukung pertanyaan:

> Apa yang sedang kita kerjakan?

> Kenapa kita mengerjakannya?

> Siapa yang bertanggung jawab?

> Sudah sampai mana?

> Apa yang harus dilakukan berikutnya?

Jika sebuah fitur tidak membantu menjawab salah satu pertanyaan tersebut, fitur tersebut belum perlu masuk MVP.
