# QueueLess — Enterprise Smart Digital Queue Management Platform

**Campus Service Optimization & Real-Time Counter Dispatch Platform**

---

## 1. Project Overview

**QueueLess** is an enterprise-grade digital queue management and real-time counter dispatch platform designed for high-density campus services. The system replaces disorderly physical queues at administrative, medical, financial, and student services with an automated, algorithmically predicted digital queue lifecycle.

### Key Capabilities:
- **Student Mobile-First PWA Tracker**: Join queues digitally, receive sequential human-readable tokens (`ACC-001`, `REG-014`), monitor live queue positions, view dynamic M/M/c wait-time predictions, and receive audio chime alerts when called.
- **Counter Operator Console**: Desktop & tablet console equipped with atomic, race-condition-protected "Call Next", "Recall", live service stopwatch timer, "Complete", "Mark No-Show", ticket transfer workflows, walk-in quick ticketing, hotkeys, and voice synthesis.
- **Operator Overview Dashboard**: Campus-wide queue KPIs, service desk status, queue flow, and recent activity at `/operator/dashboard`.
- **Operator Desk**: Counter workstation for calling and serving students at `/operator/console`.
- **Administrative Command Center**: Comprehensive KPI cards, Chart.js hourly arrival & throughput graphs, peak hour congestion heatmap matrix, live queue inspection, CSV reporting export, department & counter status toggles, and **automated M/M/c capacity recommendations** when counter utilization exceeds 85%.
- **Public TV Digital Signage**: High-contrast, large-typography lobby display (`/display`) broadcasting currently serving tokens, counter numbers, recent calls, marquee announcements, audio chimes, Text-to-Speech (TTS) voice announcements, and fullscreen support.
- **Multi-Server M/M/c & Erlang-C Predictive Engine**: Blends multi-server stochastic queuing theory with rolling historical service session times to estimate expected wait durations dynamically.
- **Multi-Channel Notification Abstraction**: Provider architecture supporting Web Push, SMS, WhatsApp, and in-app notifications.

---

## 2. Technology Stack

- **Backend Framework**: Laravel 11 / 12 (PHP 8.2+)
- **Architecture**: Domain Service Layer (`QueueEngineService`, `QueuePredictionService`, `NotificationService`, `AuditService`)
- **Database & Persistence**: Normalized 3NF Relational Database (SQLite zero-configuration out-of-the-box, fully compatible with MySQL & PostgreSQL)
- **API & Authentication**: RESTful JSON API with session and Laravel Sanctum token authentication, protected by server-side Role-Based Access Control (`role:ADMIN,COUNTER_OPERATOR,STUDENT`)
- **Frontend Layer**: Tailwind CSS + Alpine.js + Lucide Icons + Chart.js + Vite
- **Real-Time Synchronization**: Event stream / SSE + Client-side zero-refresh live polling engine
- **Automated Testing Suite**: PHPUnit automated tests covering unit math models, concurrency locks, API contracts, and full acceptance scenarios

---

## 3. Demo Credentials (For Evaluation & Quick Access)

The system includes a **1-Click Demo Switcher Bar** pinned to the top of all pages for instantaneous role switching during live demonstration without retyping passwords.

| Role | Name | Email | Password | Assigned Counter / Scope |
|---|---|---|---|---|
| **ADMIN** | System Administrator | `admin@queueless.local` | `password` | Central Command Center |
| **OPERATOR** | Kamal Hossain | `acc.operator@queueless.local` | `password` | Accounts & Finance (Counter 1) |
| **OPERATOR** | Farhana Yasmin | `reg.operator@queueless.local` | `password` | Registrar Office (Counter 1) |
| **OPERATOR** | Dr. Shahed Ali | `med.operator@queueless.local` | `password` | Medical Center (Counter 1) |
| **STUDENT** | Tanvir Hasan | `student1@queueless.local` | `password` | Student Profile (`20210104001`) |
| **STUDENT** | Sadia Afrin | `student2@queueless.local` | `password` | Student Profile (`20210104002`) |
| **STUDENT** | Rafiqul Islam | `student3@queueless.local` | `password` | Student Profile (`20210104003`) |

---

## 4. University Departments & Services (Seed Data)

1. **Registrar Office (`REG`)**:
   - Services: *Transcript Request (6m), Student Certificate (5m), Registration (8m), Grade Sheet (5m)*
   - Physical Counters: 4 | Benchmark SLA: 6 min
2. **Accounts & Finance (`ACC`)**:
   - Services: *Tuition Fee Payment (8m), Dues Clearance (6m), Scholarship Claim (10m), Hall Deposit (6m)*
   - Physical Counters: 5 | Benchmark SLA: 8 min
3. **Medical Center (`MED`)**:
   - Services: *Physician Consultation (8m), Emergency Triage (4m), Prescription Medicine Pickup (3m)*
   - Physical Counters: 2 | Benchmark SLA: 5 min
4. **Central Cafeteria (`CAF`)**:
   - Services: *Meal Token (2m), Food Order (4m), Card Top-up (2m)*
   - Physical Counters: 4 | Benchmark SLA: 3 min
5. **IT Support & Helpdesk (`ITS`)**:
   - Services: *Wi-Fi Credentials (5m), Portal Password Reset (4m), LMS Issue (8m), Hardware Support (12m)*
   - Physical Counters: 2 | Benchmark SLA: 7 min
6. **Central Library (`LIB`)**:
   - Services: *Book Checkout (3m), Book Return (3m), Clearance Processing (5m), Study Room Booking (4m)*
   - Physical Counters: 3 | Benchmark SLA: 4 min

---

## 5. Quick Start & Installation

### Prerequisites
- PHP 8.2 or higher (with `pdo_sqlite` or `pdo_mysql`)
- Composer 2.x
- Node.js 18+ and npm

### Installation Steps

```bash
# 1. Clone repository and navigate to root
git clone <repo-url>
cd "Project SpaceMaker"

# 2. Install PHP backend dependencies
composer install

# 3. Install frontend assets
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Run database migrations and seed realistic departments and metrics
php artisan migrate:fresh --seed

# 6. Build production frontend assets
npm run build

# 7. Start local web server
php artisan serve
```

Access the application in your browser at: **`http://127.0.0.1:8000`**

---

## 6. Testing & Quality Verification

### Run Automated PHPUnit Test Suite
```bash
php artisan test
```
*Executes all 16 automated tests covering Erlang-C mathematical modeling, queue lifecycle, concurrency row-locking, and API endpoints.*

### Run 1,000+ Concurrent Token Load Test
```bash
php artisan queue:simulate-load --tokens=1000
```
*Simulates high-concurrency token generation, student queue joining, and atomic operator dispatching with throughput measurements.*

---

## 7. Core End-to-End Demonstration Scenario (Section 33 Acceptance)

1. **Student Queue Joining**:
   - Click **"👤 Student (Tanvir)"** in top demo bar.
   - Click **"Join Queue"**, select **Accounts & Finance** -> **Dues Clearance**.
   - Click **"Join Queue & Receive Token"**.
   - Student receives token **`ACC-001`**. Tracker shows:
     - *Position: #1*
     - *People Ahead: 0*
     - *Est. Wait: calculated dynamically via M/M/c*
2. **Second Student Joins**:
   - Open a private/incognito window (or switch to **"👤 Student 2 (Sadia)"**).
   - Join **Accounts & Finance** -> receives token **`ACC-002`**.
   - Sees *People Ahead: 1*. Student 1 dashboard remains synchronized.
3. **Operator Calls Next**:
   - Switch to **"🎯 Accounts Counter 1"** (Operator Console).
   - Click **"CALL NEXT STUDENT"**.
   - Ticket `ACC-001` is atomically claimed and changes to `CALLED`.
   - Student 1 mobile tracker immediately displays pulsating **"YOUR TOKEN IS CALLED! Proceed to Counter 1"** with audio chime.
   - Public TV display at `/display` immediately updates showing `ACC-001` at Counter 1.
4. **Service Execution**:
   - Operator clicks **"Start Service"** -> live stopwatch timer begins.
   - Operator clicks **"Complete"** -> duration logged, SLA compliance evaluated, Counter 1 freed to `AVAILABLE`.
5. **Next Ticket Called**:
   - Operator clicks **"Call Next"** again -> `ACC-002` is summoned.
6. **Admin Verification**:
   - Switch to **"⚡ Admin"** -> Command Center updates with completed service count, updated wait averages, and throughput graph.

---

## 8. Architectural Documentation Links

- [System Architecture Specification](ARCHITECTURE.md)
- [Database Normalized 3NF Model & ERD](DATABASE.md)
- [REST API Specification](API.md)
- [M/M/c & Erlang-C Queuing Theory Model](QUEUE_ALGORITHM.md)
