# QueueLess — REST API Specification

---

## 1. Overview & Standards

All endpoints adhere to REST conventions:
- Standard JSON envelope:
  ```json
  {
    "success": true,
    "message": "Human readable status",
    "data": { ... }
  }
  ```
- Error response envelope:
  ```json
  {
    "success": false,
    "message": "Specific error explanation",
    "errors": { ... }
  }
  ```
- Authentication: Session / Laravel Sanctum Bearer Token
- Standard HTTP Status Codes: `200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Content`, `429 Too Many Requests`, `500 Server Error`.

---

## 2. Authentication Endpoints

### `POST /api/auth/register`
Register a new student account.
- **Request Body**:
  ```json
  {
    "name": "Tanvir Hasan",
    "email": "tanvir@queueless.local",
    "student_id": "20210104001",
    "department": "Engineering",
    "batch": "14th",
    "phone": "01711223344",
    "password": "SecretPassword123!",
    "password_confirmation": "SecretPassword123!"
  }
  ```
- **Response**: `201 Created` with user & token.

### `POST /api/auth/login`
Authenticate Student, Operator, or Administrator.
- **Request Body**:
  ```json
  {
    "email": "student@queueless.local",
    "password": "password"
  }
  ```
- **Response**: `200 OK` with user details, roles, and session token.

### `POST /api/auth/logout`
Revoke active session/token.

---

## 3. Department & Service Endpoints

### `GET /api/departments`
List all active university departments with real-time queue length, active counters, and current wait estimations.
- **Response**: Array of departments with codes (`REG`, `ACC`, `MED`, `CAF`, `ITS`, `LIB`).

### `GET /api/departments/{id}`
Department detail including available counters and services.

### `GET /api/departments/{id}/services`
List services under a specific department.

---

## 4. Student Queue Endpoints

### `POST /api/queues/join`
Generate a new digital queue token for the authenticated student.
- **Request Body**:
  ```json
  {
    "department_id": 2,
    "service_id": 5
  }
  ```
- **Business Logic**:
  - Checks if department is active and open.
  - Verifies student has no conflicting active token in this department.
  - Atomically calculates daily sequence number.
  - Runs M/M/c algorithm to compute estimated initial wait time.
  - Broadcasts `queue:joined` event to all relevant listeners.
- **Response**: `201 Created` with ticket details:
  ```json
  {
    "success": true,
    "data": {
      "ticket": {
        "id": 142,
        "ticket_number": "ACC-015",
        "department_code": "ACC",
        "service_name": "Dues Clearance",
        "position": 3,
        "people_ahead": 2,
        "estimated_wait_min": 16,
        "status": "WAITING"
      }
    }
  }
  ```

### `GET /api/queues/active`
Retrieve the authenticated student's active queue token (if any) with live recalculated wait time, current position, and now-serving status.

### `GET /api/queues/{id}`
Inspect specific ticket state.

### `POST /api/queues/{id}/cancel`
Student cancels their waiting token. Re-orders queue and notifies behind tickets.

### `GET /api/queues/history`
Student's completed and past tickets.

---

## 5. Counter Operator Console Endpoints

### `GET /api/operator/queue`
Retrieve waiting queue for the operator's assigned department.

### `POST /api/operator/call-next`
Atomically call the next waiting ticket in line for the operator's counter.
- **Request Body**:
  ```json
  {
    "counter_id": 1
  }
  ```
- **Concurrency Guard**: Protected with `lockForUpdate()` in a serial transaction.
- **Response**: Ticket set to `CALLED`, audio broadcast triggered.

### `POST /api/operator/recall`
Re-announces the currently called token on public display.

### `POST /api/operator/start`
Operator begins serving the student at the counter. Sets status to `SERVING`, starts service timer.

### `POST /api/operator/complete`
Finishes service. Sets ticket to `COMPLETED`, saves `service_session` with exact duration, updates department rolling handling time.

### `POST /api/operator/no-show`
Marks student as `NO_SHOW` after reasonable grace period.

### `POST /api/operator/transfer`
Forwards ticket to another department or counter.

---

## 6. Public Display & Real-Time Sync Endpoints

### `GET /api/display/live`
Public TV signage payload containing currently serving tokens across all departments, counter numbers, and recently called history.

### `GET /api/display/stream`
Server-Sent Events / EventSource real-time stream broadcasting queue updates instantly to connected TV screens.

---

## 7. Admin & Analytics Endpoints

### `GET /api/admin/metrics`
Current real-time system KPIs:
- Total active queues
- Total waiting students
- Active operational counters
- Today's completed services
- Current SLA breach rate
- Overall average wait time

### `GET /api/admin/analytics/hourly`
Arrivals and wait times grouped by hour (08:00 - 17:00).

### `GET /api/admin/analytics/heatmap`
Department congestion matrix by day of week and hour.

### `GET /api/admin/recommendations`
Intelligent operational counter recommendations based on M/M/c queue utilization $> 85\%$.
