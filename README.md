# 🏛️ Class Replacement System (CRS)

> This project implements a web-based academic system designed to manage student class replacement appeals. It uses a **Service-Oriented Architecture (SOA)**, relying on a **Dual-Port System** for secure, inter-module communication.

---

## 🎯 Architecture Overview

The CRS is structured as a **Hybrid Monolith**, where complex business logic is strictly separated into four modules coordinated by distinct Design Patterns.

| Module | Team Member | Primary Pattern | Core Responsibility |
| :--- | :--- | :--- | :--- |
| **Timetable** (API Consumer/Provider) | Victor Pow Yie Yuaw (You) | **Mediator** | Orchestrates schedule rendering, filtering clashes, and validating new assignments. |
| **Course-Subject** (API Provider) | Raynold Tai Yan Run | **Facade** | Manages CRUD for Course, Subject, and Course-Subject mappings. |
| **Replacement** (Workflow) | Chew Lai Soon | **Observer** | Handles appeal submission, status tracking, and automated replacement record creation. |
| **User Management** (Auth/Profiles) | Hoe Hong Ming | **Factory** | Handles authentication, authorization, and user record management. |

---

## ⚙️ Installation and Setup Guide

### Prerequisites

*   PHP (8.1 or higher)
*   Composer & Node.js
*   MySQL/MariaDB (Ensure your database credentials are set in `.env`).

### Step 1: Clone and Initialize

```bash
# Clone the repository and navigate to the folder
git clone [your-repo-url]
cd [project-folder]

# Install PHP and JavaScript dependencies
composer install
npm install
php artisan key:generate
```

### Step 2: Database Reset and Seed
The system uses complex String-based Primary Keys and relationships that must be built in a specific order. 

A fresh migration and seed is required.

```bash
# WARNING: This will drop ALL existing data!
php artisan migrate:fresh --seed
```

### Step 3: Launch the Application (Dual-Port System)

You must run **two** independent terminal sessions to facilitate internal API communication.

Launch the API Backend (Port 8001) & Frontend (Port 8000)

```bash
php artisan serve --port=8000
php artisan serve --port=8001
```

## 🔑 Access Credentials

| Role | User ID | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | `U001` | `admin@example.com` | `123456` |
| **Student** | `U011` | `student11@example.com` | `123456` |
| **Lecturer** | `U002` | `lecturer2@example.com` | `123456` |

**Access URL:** `http://127.0.0.1:8000/login`

### 🛡️ Secure Coding Practices

The Timetable Module relies on the **Mediator Pattern** to enforce security logic at the correct layer, complementing Laravel's built-in defenses.

*   **Clash Prevention:** The `Mediator::assignClass()` and `::updateTimeslot()` methods enforce complex database rules (time/venue overlap) before any data is committed.
*   **Secure Stateless Consumption (Defense against Deadlocks):** Services are configured with `withoutCookies()` to ensure inter-module API calls are stateless and do not expose sensitive session data, mitigating the risk of **Session Hijacking** and local development deadlocks.
*   **IDOR Prevention (API Security):** The `TimetableController` (API Provider) ensures that calls to fetch specific user data (e.g., `/api/timetable/future/{userId}`) are authorized, preventing one student from accessing another's schedule data via direct URL manipulation.

---


## 📦 Module Responsibilities and Feature Flow (Timetable)

The **Timetable Mediator** centralizes all scheduling logic, acting as the critical bridge between external APIs and the presentation layer.

### 1. Student Timetable View (READ)

**Controller:** `StudentTimetableController::index()`

| Action | Source/Mediator Function | Detail |
| :--- | :--- | :--- |
| **1. Get User Group** | **User API** (`/api/users/{id}`) | Retrieves the student's `classGroupId` (e.g., CG004) for filtering. |
| **2. Get Schedule Data** | `Mediator::getCurrentWeekSchedule()` | Filters weekly classes based on the student's group, accounting for **joint classes** (`FIND_IN_SET` query). |
| **3. Data Enrichment** | `Mediator::getTransitiveSubjectList()` | Consumes the **Course/Subject API** to map Foreign Key `courseSubjectId` (e.g., CS0013) to human-readable `SubjectName`. |

### 2. Admin Assignment (CREATE - Lecture Only)

**Endpoint:** `POST /admin/assignment/submit`

The core function `Mediator::assignClass($data)` ensures adherence to all constraints before insertion.

| Step | Implementation in Mediator | Validation Point |
| :--- | :--- | :--- |
| **Data Resolution** | Looks up **CourseSubject ID** based on submitted `subjectId` and `courseId`. | Foreign Key Integrity |
| **Clash Check** | `Timetable::where(day/venue)` checks for time overlap (`startTime < newEnd` AND `endTime > newStart`). | Business Rule |
| **Insertion Loop** | Creates **7 independent records** (`Timetable::create()`) for 7 future weeks, ensuring the entire semester's schedule is built. | Database Consistency |

### 3. Timeslot Management (UPDATE)

**Endpoint:** `POST /admin/timeslotManagement/update`

The Update operation is secured by the Mediator's clash check logic.

*   **Function:** `Mediator::updateTimeslot(string $id, array $newData)`
*   **Logic:** Executes the same **Clash Check** logic as above, but explicitly excludes the record currently being modified (`where('timetableId', '!=', $id)`) from the search results, preventing self-clashes.

---


## 5. Software Security

This module employs secure coding practices to mitigate common web application threats, focusing on secure logging and strict access control.

### 5.1 Potential Threat/Attack

| Threat | Description | Attack Type |
| :--- | :--- | :--- |
| **1. Privilege Escalation (Role Manipulation)** | An attacker attempts to bypass the `role:admin` middleware to access sensitive functions (e.g., `assignClass`) or manipulate user roles. | Authorization Bypass / CSRF |
| **2. Information Disclosure** | The system exposes verbose, technical error messages (e.g., SQL query failures, table names, file paths) when exceptions occur. | Error Handling Weakness |

### 5.2 Secure Coding Practice

#### Defense 1: Generic Error Messages and Secure Logging

The system prevents exposure of internal structure by concealing technical details from the user while logging the full exception trace securely server-side.

| Layer | Implementation | Evidence |
| :--- | :--- | :--- |
| **Service/Mediator** | `catch (\Exception $e)` blocks log the full trace (`$e->getTraceAsString()`) but return only a generic status string (`'clash'`, `'error'`) to the controller. | Prevents **Information Disclosure** (Threat 2). |
| **Controller** | `if ($result === 'clash') { return back()->with('error', 'Update Failed: The new Venue/Timeslot is already occupied.'); }` | Provides a user-friendly, non-technical error message. |

#### Defense 2: Access Control and IDOR Prevention (Authorization)

The API endpoints enforce role checks to prevent unauthorized access to sensitive data (Insecure Direct Object Reference).

| Target | Implementation Detail | Goal |
| :--- | :--- | :--- |
| **API Provider (TimetableController)** | All data retrieval APIs (e.g., `/timetable/future/{userId}`) require the caller to either be **Admin** or the **Owner** of the requested `userId`. | Mitigates **IDOR** and **Privilege Escalation** (Threat 1). |
| **Controller Validation** | All `POST` methods use Laravel validation to confirm that foreign keys (`timetableId`, `venueId`) actually exist in the database, preventing attackers from submitting random, invalid IDs. | Mitigates SQL integrity errors. |

---

## 6. Web Services

The Timetable Module is a central piece of the Service-Oriented Architecture (SOA), consuming data from external modules (User, Course-Subject) and providing structured scheduling data to the Replacement Module.

### 6.1 Overview of Web Service Usage

The **Timetable Mediator** orchestrates all consumption via stateless HTTP calls, adhering to the **RESTful JSON** standard.

### 6.2 Service Exposure (Provide)

The Timetable Module exposes several internal endpoints, primarily consumed by the Replacement Module for validation and status checks.

#### IFA 1: Timetable Data Search (Clash Detection & Schedule)

| Field | Description | Format |
| :--- | :--- | :--- |
| **Protocol** | RESTFUL | |
| **Function Description** | Retrieves timetable data filtered by specific criteria (Day, Venue, or Date Range). | |
| **Target Module** | Replacement Module (for clash check) | |
| **URL** | `/api/timetable/schedule` | |
| **Parameters** | `classGroupId`, `startOfWeek`, `endOfWeek` | |

#### IFA 2: Future Classes for Appeals UI

| Field | Description | Format |
| :--- | :--- | :--- |
| **Protocol** | RESTFUL | |
| **Function Description** | Fetches all non-taken classes for a student (by group) after today's date. | |
| **Target Module** | Replacement Module (Appeals UI) | |
| **URL** | `/api/timetable/future/{userId}` | |
| **Parameters** | `userId` | |

### 6.3 Service Consumption (Consume)

The Timetable Module relies on external APIs to perform filtering logic and data enrichment.

#### IFA 3: User Profile Data Retrieval (For Student Filtering)

| Field | Description | Format |
| :--- | :--- | :--- |
| **Protocol** | RESTFUL | |
| **Function Description** | Retrieves `classGroupId` required for timetable filtering. | |
| **Source Module** | User Management Module | |
| **URL** | `/api/users/{userId}` | |
| **Function Name** | `UserService::getUser()` | |

#### IFA 4: Course-Subject Relationship Mapping (For Display)

| Field | Description | Format |
| :--- | :--- | :--- |
| **Protocol** | RESTFUL | |
| **Function Description** | Retrieves all subjects for mapping `courseSubjectId` to human-readable names. | |
| **Source Module** | Course-Subject Module | |
| **URL** | `/api/subjects` | |
| **Function Name** | `CourseService::fetchSubjects()` | |

---

## 7. References

1.  **Gamma, E., Helm, R., Johnson, R., & Vlissides, J. (1994).** Design patterns: Elements of reusable object-oriented software. Addison-Wesley.
    *   *Source for Mediator Pattern definition and usage.*
2.  **Laravel. (2024).** Laravel Documentation (Authentication, Authorization, HTTP Client, Eloquent ORM, Sessions, Middleware, Factories).
    *   *Primary source for all technical implementation details.*
3.  **Carbon.** (2025). Carbon - A simple PHP API extension for DateTime. Carbon. [https://carbon.nesbot.com/docs/](https://carbon.nesbot.com/docs/)
    *   *Used for all date/time manipulation and clash detection logic.*
4.  **OWASP Foundation. (2023).** OWASP Top 10 web application security risks and cheat sheets (Session management).
    *   *Justification for security threats and the implementation of session protection.*
5.  **Refactoring.Guru.** (n.d.). Mediator design pattern. [https://refactoring.guru/design-patterns/mediator](https://refactoring.guru/design-patterns/mediator)
    *   *Source for structural justification of the Mediator pattern.*
6.  **Fielding, R. T. (2000).** Architectural styles and the design of network-based software architectures. (Doctoral dissertation, University of California, Irvine).
    *   *Source for RESTful Web Service architecture concepts.*

---
