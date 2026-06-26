# StudentWork

> A university student work platform built with Laravel.

StudentWork is a platform that connects university students who need help with academic projects, assignments, and technical work with other skilled students who are able to complete those tasks.

The project is designed as a modern service marketplace focused exclusively on university students.

---

# Project Status

Current Version:

> MVP (Minimum Viable Product)

Project State:

> System Design Completed
> Database Design Completed
> Business Rules Completed
> Ready for Development

Current Development Phase:

> Laravel Backend Implementation

---

# Main Features

- Mobile OTP Authentication
- User Profile
- Student Verification
- Skills Management
- Categories
- Task Creation
- Task Attachments
- Apply for Tasks
- Application Attachments
- Applicant Selection
- Private Chat
- Project Management
- Delivery System
- Complaint System
- Review System
- Notification System
- Admin Panel

---

# Target Users

The platform is designed for university students.

Users can simultaneously act as:

- Task Owner
- Task Performer

No separate client/freelancer roles exist.

---

# MVP Scope

Included:

✅ OTP Login

✅ User Profile

✅ Skills

✅ Categories

✅ Tasks

✅ Applications

✅ Chat

✅ Projects

✅ Deliveries

✅ Complaints

✅ Reviews

✅ Notifications

---

Excluded From MVP

The following features are intentionally postponed:

- Wallet
- Online Payment Gateway
- Bidding System
- Multi University Support
- Live Chat (WebSocket)
- Project Change Requests
- Chat File Attachments

These features will be implemented in future versions.

---

# Authentication

Authentication is based only on mobile phone OTP.

No password exists.

Authentication Flow

Send Mobile Number

↓

Receive OTP

↓

Verify OTP

↓

Receive API Token

---

# User Roles

There are only two roles.

| Role | Description |
|-------|-------------|
| user | Student |
| admin | Platform Administrator |

---

# Core Workflow

Task Creation

↓

Applications

↓

Owner Selects One Applicant

↓

Conversation Opens

↓

Project Starts

↓

Performer Submits Delivery

↓

Owner Reviews

↓

Completed

---

# Technologies

Backend

- Laravel
- PHP
- MySQL

Authentication

- Laravel Sanctum
- OTP Login

Storage

- Laravel Storage

API

- REST API

---

# Project Structure

docs/

README.md

01-System-Overview.md

02-Business-Rules.md

03-Database.md

04-ERD.md

05-Development-Plan.md

06-API-Design.md

---

app/

bootstrap/

config/

database/

routes/

storage/

tests/

---

# Database Modules

Users

OTP

Skills

Categories

Tasks

Applications

Conversation

Messages

Projects

Deliveries

Complaints

Reviews

Notifications

---

# Project Lifecycle

Task

↓

Application

↓

Conversation

↓

Project

↓

Delivery

↓

Review

---

# Development Roadmap

Phase 1

Project Setup

Git Repository

Environment

Authentication

---

Phase 2

Database

Migrations

Seeders

Models

Relationships

---

Phase 3

Business Logic

Services

Policies

Validation

Notifications

---

Phase 4

REST API

Authentication

Tasks

Applications

Projects

Chat

Reviews

---

Phase 5

Admin Panel

Dashboard

Management

Reports

---

Phase 6

Testing

Feature Tests

Bug Fixes

Optimization

Deployment

---

# Documentation

Project documentation is located inside the docs directory.

Each document explains one part of the system in detail.

---

# License

This project is created for educational purposes.

StudentWork MVP

Version 1.0
