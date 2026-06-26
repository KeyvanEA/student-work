# 03 - Database

## Overview

StudentWork uses a relational database designed around the project lifecycle.

The database follows normalization principles while keeping the structure simple and scalable for future development.

The entire system is centered around the following workflow:

User

↓

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

# Database Engine

- MySQL
- UTF8MB4 Character Set
- InnoDB Storage Engine
- Foreign Key Constraints Enabled

---

# Database Tables

| Table | Description |
|--------|-------------|
| users | Registered users |
| otp_codes | OTP verification codes |
| skills | Available skills |
| user_skills | User ↔ Skill relationship |
| categories | Task categories |
| tasks | Published tasks |
| task_files | Files attached to tasks |
| applications | Task applications |
| application_files | Files attached to applications |
| conversations | Private conversations |
| messages | Conversation messages |
| projects | Accepted projects |
| deliveries | Project submissions |
| delivery_files | Delivery attachments |
| complaints | Project complaints |
| reviews | User reviews |
| notifications | User notifications |

---

# Table Descriptions

## users

Stores all registered users.

Main Responsibilities:

- Authentication
- Profile
- Student Information
- Role Management

Relationships

- Has Many Tasks
- Has Many Applications
- Has Many Skills
- Has Many Notifications
- Has Many Reviews

---

## otp_codes

Stores temporary OTP verification codes.

Used only during authentication.

OTP records expire automatically.

Relationships

- Belongs To User (Optional)

---

## skills

Stores predefined platform skills.

Examples:

- Laravel
- PHP
- Photoshop
- Python
- UI Design

Relationships

- Many To Many Users

---

## user_skills

Pivot table between users and skills.

Relationships

- Belongs To User
- Belongs To Skill

---

## categories

Stores available task categories.

Examples:

- Programming
- Graphic Design
- Translation
- Mathematics

Relationships

- Has Many Tasks

---

## tasks

Represents published student requests.

Relationships

- Belongs To User
- Belongs To Category
- Has Many Applications
- Has Many Files

---

## task_files

Stores uploaded files for tasks.

Relationships

- Belongs To Task

---

## applications

Represents user applications.

Relationships

- Belongs To Task
- Belongs To User
- Has One Conversation
- Has One Project
- Has Many Files

---

## application_files

Stores application attachments.

Relationships

- Belongs To Application

---

## conversations

Private communication between task owner and selected applicant.

Relationships

- Belongs To Application
- Has Many Messages

---

## messages

Stores conversation messages.

Relationships

- Belongs To Conversation
- Belongs To Sender

---

## projects

Represents accepted applications.

Relationships

- Belongs To Application
- Has Many Deliveries
- Has Many Complaints
- Has Many Reviews

---

## deliveries

Stores every project submission.

Every submission is preserved.

Relationships

- Belongs To Project
- Has Many Files

---

## delivery_files

Stores uploaded delivery files.

Relationships

- Belongs To Delivery

---

## complaints

Stores project disputes.

Relationships

- Belongs To Project
- Belongs To User

---

## reviews

Stores mutual project reviews.

Relationships

- Belongs To Project
- Belongs To Reviewer
- Belongs To Reviewee

---

## notifications

Stores user notifications.

Relationships

- Belongs To User

---

# Pivot Tables

| Table | Purpose |
|--------|---------|
| user_skills | Many-to-Many relationship |

---

# Status Fields

## Task Status

- Open
- Assigned
- Completed
- Cancelled
- Expired

---

## Application Status

- Pending
- Contacted
- Accepted
- Rejected

---

## Project Status

- In Progress
- Submitted
- Revision Requested
- Completed
- Cancelled
- Disputed

---

## Payment Status

- Unpaid
- Paid

---

# File Storage

Uploaded files are stored separately according to their type.

Task Files

storage/app/tasks/

Application Files

storage/app/applications/

Delivery Files

storage/app/deliveries/

---

# Foreign Key Strategy

The database uses foreign key constraints to preserve data consistency.

Examples:

- tasks.user_id → users.id
- tasks.category_id → categories.id
- applications.task_id → tasks.id
- applications.user_id → users.id
- conversations.application_id → applications.id
- projects.application_id → applications.id
- deliveries.project_id → projects.id

---

# Cascade Strategy

Delete behavior is carefully controlled.

Examples:

- Delete User → Restricted
- Delete Task → Delete Task Files
- Delete Application → Delete Files
- Delete Project → Delete Deliveries
- Delete Delivery → Delete Delivery Files

Historical records should be preserved whenever possible.

---

# Index Strategy

Frequently queried columns should be indexed.

Examples:

- mobile
- student_number
- category_id
- task_id
- user_id
- project_id
- application_id
- status

---

# Naming Convention

Primary Key

id

Foreign Keys

user_id

task_id

category_id

application_id

project_id

delivery_id

created_by

updated_by

---

# Timestamp Strategy

Every table contains:

- created_at
- updated_at

Optional tables may also include:

- deleted_at

Soft Deletes are used only where appropriate.

---

# Future Database Extensions

The current schema is designed to support future expansion without major structural changes.

Future tables may include:

- wallets
- wallet_transactions
- payments
- universities
- project_changes
- message_files
- ratings
- favorites
- reports

---

# Summary

The StudentWork database consists of 17 core tables designed around a simple, scalable, and normalized architecture.

The schema supports the complete MVP workflow while remaining flexible enough for future versions without requiring major redesign.
