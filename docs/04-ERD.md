# 04 - Entity Relationship Diagram (ERD)

## Overview

This document describes the relationships between all database entities used in the StudentWork MVP.

The ERD is designed around the complete lifecycle of a student project, from task creation to project completion.

---

# Main Workflow

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

# Entity Relationships

## User

A user can:

- Create many tasks.
- Submit many applications.
- Have many skills.
- Receive many notifications.
- Write many reviews.
- Receive many reviews.

Relationship Summary

- User → Tasks (1:N)
- User → Applications (1:N)
- User → UserSkills (1:N)
- User → Notifications (1:N)
- User → Reviews (1:N)

---

## Skill

A skill can belong to many users.

Relationship

- Skill ↔ Users (N:M)

Implemented by:

user_skills

---

## Category

A category contains many tasks.

Relationship

Category (1)

↓

Tasks (N)

---

## Task

A task belongs to exactly one user.

A task belongs to exactly one category.

A task has many applications.

A task has many files.

Relationships

User

↓

Tasks

↓

Applications

↓

Task Files

---

## Task File

Each task file belongs to exactly one task.

Relationship

Task (1)

↓

Task Files (N)

---

## Application

Each application belongs to:

- One Task
- One Applicant

Each application has:

- One Conversation
- One Project
- Many Files

Relationships

Task (1)

↓

Applications (N)

↓

Conversation (1)

↓

Project (1)

---

## Application File

Each application file belongs to one application.

Relationship

Application (1)

↓

Application Files (N)

---

## Conversation

Conversation is created after the task owner initiates communication.

Each conversation belongs to one application.

Relationship

Application (1)

↓

Conversation (1)

↓

Messages (N)

---

## Message

Each message belongs to one conversation.

Each message has one sender.

Relationships

Conversation (1)

↓

Messages (N)

User (1)

↓

Messages (N)

---

## Project

A project exists only after an application has been accepted.

Each project belongs to one application.

Each project contains:

- Deliveries
- Complaints
- Reviews

Relationship

Application (1)

↓

Project (1)

↓

Deliveries

↓

Complaints

↓

Reviews

---

## Delivery

Each project may have multiple deliveries.

Each delivery may contain multiple files.

Relationships

Project (1)

↓

Deliveries (N)

↓

Delivery Files (N)

---

## Complaint

Each complaint belongs to one project.

Each complaint has one creator.

Relationships

Project (1)

↓

Complaints (N)

User (1)

↓

Complaints (N)

---

## Review

Reviews are exchanged after project completion.

Each review belongs to:

- One Project
- One Reviewer
- One Reviewee

Relationships

Project (1)

↓

Reviews (N)

User (Reviewer)

↓

Reviews

User (Reviewee)

↓

Reviews

---

## Notification

Each notification belongs to one user.

Relationship

User (1)

↓

Notifications (N)

---

# Relationship Summary

| Parent | Child | Type |
|----------|---------|------|
| User | Tasks | 1:N |
| User | Applications | 1:N |
| User | Notifications | 1:N |
| User | Reviews | 1:N |
| User | User Skills | 1:N |
| Skill | User Skills | 1:N |
| Category | Tasks | 1:N |
| Task | Task Files | 1:N |
| Task | Applications | 1:N |
| Application | Application Files | 1:N |
| Application | Conversation | 1:1 |
| Application | Project | 1:1 |
| Conversation | Messages | 1:N |
| Project | Deliveries | 1:N |
| Delivery | Delivery Files | 1:N |
| Project | Complaints | 1:N |
| Project | Reviews | 1:N |

---

# Many-to-Many Relationships

Currently, the system contains one many-to-many relationship.

Users

↔

Skills

Implemented through:

user_skills

---

# One-to-One Relationships

Application

↓

Conversation

Application

↓

Project

---

# One-to-Many Relationships

User

↓

Tasks

Task

↓

Applications

Conversation

↓

Messages

Project

↓

Deliveries

Project

↓

Complaints

Project

↓

Reviews

Delivery

↓

Delivery Files

Task

↓

Task Files

Application

↓

Application Files

User

↓

Notifications

---

# Entity Dependency Order

The recommended migration order is:

1. users
2. otp_codes
3. skills
4. user_skills
5. categories
6. tasks
7. task_files
8. applications
9. application_files
10. conversations
11. messages
12. projects
13. deliveries
14. delivery_files
15. complaints
16. reviews
17. notifications

---

# ERD Diagram

The complete visual Entity Relationship Diagram (ERD) should be generated after the database schema is finalized.

Recommended tools:

- dbdiagram.io
- Draw.io
- Mermaid
- MySQL Workbench

---

# Summary

The StudentWork database consists of 17 core entities connected through one-to-one, one-to-many, and many-to-many relationships.

The schema follows the complete lifecycle of a task, ensuring a normalized, scalable, and maintainable database architecture suitable for the MVP and future versions.
